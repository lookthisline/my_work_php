<?php

namespace app\common\expand;

final class RedisUtils
{
    private object $handler;
    private static object $static_handler;
    // 自身实例
    private static $instance;
    private array $config = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'timeout'    => 1.5,
        'persistent' => true,
        'select'     => 0,
        'prefix'     => ''
    ];

    /**
     * 单机连接
     * @access private
     * @param Array $config = []
     */
    private function __construct(array $config = [])
    {
        // 优先从配置文件读取配置
        $config = !empty($config) ? $config : config('cache.redis', $this->config);

        // 合并配置
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }

        // [PhpRedis](https://github.com/phpredis/phpredis)
        if (extension_loaded('redis') && !isset($this->handler)) {
            $this->handler = new \Redis();
            // 长连接；依赖于 php-fpm 进程，php-fpm进程不死，redis connect 就一直存在，直到空闲超时自动断开
            if ($this->config['persistent']) {
                $this->handler->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'], 'persistent_id_' . $this->config['select']);
            } else {
                $this->handler->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }

            !$this->config['password'] ?: $this->handler->auth($this->config['password']);
            // 选择数据表
            (0 == (int)$this->config['select']) ?: $this->handler->select($this->config['select']);

            self::$static_handler = &$this->handler;
            return $this;
        } else {
            throw new \Exception('not support: redis');
        }

        // [predis](https://github.com/predis/predis)
        if (class_exists('\\Predis\\Client') && !isset($this->handler)) {
            self::$static_handler = $this->handler = new \Predis\Client($this->config);
            return $this;
        } else {
            throw new \Exception('not support: redis');
        }
    }

    /**
     * 获取 Redis 实例
     * @param $config = []
     * @return Self
     */
    public static function getInstance($config = []): self
    {
        //判断实例有无创建，没有的话创建实例并返回，有的话直接返回
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * 刷新过期时间
     * @access public
     * @param String $key
     * @param Integer $seconds
     * @return Integer $code
     */
    public function RefreshExpireTime(String $key, int $seconds): int
    {
        // 剩余时间
        $remain_time = $this->handler->ttl($key);
        $code = 1;
        switch ($remain_time) {
            case -1:
                // 存在 key 但没有设置剩余过期时间
                $this->handler->expire($key, $seconds);
                $code = -1;
            case -2:
                // 不存在 key
                $code = -2;
            case $remain_time < ($seconds * (2 / 3)):
                // 剩余时间小于给定时间的 2/3
                $this->handler->expire($key, $seconds);
                $code = 1;
            default:
                $code = 1;
        }
        return $code;
    }

    /**
     * @access public
     * @param String $method_name
     * @param Mixed $arguments
     * @return Mixed
     */
    public static function __callStatic($method_name, $arguments)
    {
        // return self::$static_handler->$method_name($arguments);
        return call_user_func([self::$static_handler, $method_name], $arguments);
    }

    /**
     * @access public
     * @param String $method_name
     * @param Mixed $arguments
     * @return Mixed
     */
    public function __call($method_name, $arguments)
    {
        // return $this->handler->$method_name($arguments);
        return call_user_func([$this->handler, $method_name], $arguments);
    }

    private function __clone()
    {
    }
}
