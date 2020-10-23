<?php

namespace app\common\expand;

class RedisUtils
{
    private object $handler;

    private static object $static_handler;

    private array $config = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'timeout'    => 0,
        'persistent' => false,
        'select'     => 0,
    ];

    /**
     * @access public
     * @param Array $config
     */
    public function __construct(array $config = [])
    {
        // 优先从配置文件读取配置
        $config = !empty($config) ? $config : config('cache.redis', $this->config);
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        if (extension_loaded('redis')) {
            $this->handler = new \Redis;
            if ($this->config['persistent']) {
                $this->handler->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'], 'persistent_id_' . $this->config['select']);
            } else {
                $this->handler->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }
            if (!empty($this->config['password'])) {
                $this->handler->auth($this->config['password']);
            }
            if (0 != $this->config['select']) {
                $this->handler->select($this->config['select']);
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($this->config as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
                    $params[$key] = $val;
                    unset($this->config[$key]);
                }
            }
            if ('' == $this->config['password']) {
                unset($this->config['password']);
            }
            $this->handler = new \Predis\Client($this->config, $params);
            $this->config['prefix'] = '';
        } else {
            throw new \Exception('not support: redis');
        }
        self::$static_handler = &$this->handler;
    }

    /**
     * 每次执行将得到该次命令结果，不返回自身实例，无法实现链式操作
     * @access public
     * @param String $method
     * @param Mixed $args
     * @return Mixed
     */
    public static function __callStatic($method, $args)
    {
        // return self::$static_handler->$method(...$args);
        return call_user_func_array([self::$static_handler, $method], $args);
    }

    /**
     * 每次执行将得到该次命令结果，不返回自身实例，无法实现链式操作
     * @access public
     * @param String $method
     * @param Mixed $args
     * @return Mixed
     */
    public function __call($method, $args)
    {
        // return $this->handler->$method(...$args);
        return call_user_func_array([$this->handler, $method], $args);
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
}
