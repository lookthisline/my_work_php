<?php

namespace app\common\expand;

final class RedisUtils
{
    private static $_instance;
    private static $k;
    private $redis;
    private $expire_time = 60;
    private $host        = 'localhost';
    private $port        = '6379';
    // 当前数据库ID号
    private static $db_id = 0;
    // 当前权限认证码
    private $auth = '';

    private function __clone()
    {
        // disable clone
    }

    public function __destruct()
    {
        $this->redis->close();
    }

    private function __construct(array $config)
    {
        (!isset($config['host']) || !$config['host'] || $config['host'] == $this->host) ?: $this->host = $config['host'];
        (!isset($config['port']) || !$config['port'] || $config['port'] == $this->port) ?: $this->port = $config['port'];
        (!isset($config['auth']) || !$config['auth']) ?: $this->auth = $config['auth'];
        (!isset($config['timeout']) || !$config['timeout']) ?: $this->expire_time = time() + $config['timeout'];

        if (extension_loaded('redis') && !isset($this->redis)) {
            // [PhpRedis](https://github.com/phpredis/phpredis)
            $this->redis = new \Redis();
            // 长连接；依赖于 php-fpm 进程，php-fpm进程不死，redis connect 就一直存在，直到空闲超时自动断开
            if (isset($config['persistent']) && $config['persistent']) {
                $this->redis->pconnect($this->host, $this->port, $this->expire_time, 'persistent_id_' . self::$db_id);
            } else {
                $this->redis->connect($this->host, $this->port, $this->expire_time);
            }
        } elseif (class_exists('\\Predis\\Client') && !isset($this->redis)) {
            // [predis](https://github.com/predis/predis)
            $this->redis = new \Predis\Client($this->config);
        } else {
            throw new \Exception('not support: redis');
        }
        !$this->auth ?: $this->redis->auth($this->auth);
        !self::$db_id ?: $this->select(self::$db_id);
    }

    /**
     * 得到实例化的对象.
     * 为每个数据库建立一个连接
     * 如果连接超时，将会重新建立一个连接
     * @param array $config
     * @param int $db_id
     * @return \iphp\db\Redis
     */
    public static function getInstance(array $config = [], int $db_id = 0): self
    {
        !$db_id ?: self::$db_id = $config['select'] = $db_id;

        self::$k = $k = md5(implode('', $config));

        $instance = &static::$_instance[$k];

        if (!($instance instanceof self)) {
            $instance         = new self($config);
            $instance::$db_id = $db_id;
        } elseif (time() > $instance->expire_time) {
            $instance->close();
            $instance         = new self($config);
            $instance::$db_id = $db_id;
        }
        return $instance;
    }

    /**
     * @access public
     * @param string $method_name
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($method_name, $arguments)
    {
        return call_user_func([static::$_instance[self::$k]->redis, $method_name], ...$arguments);
    }

    /**
     * @access public
     * @param string $method_name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        return call_user_func([$this->redis, $method_name], ...$arguments);
    }

    /**
     * 刷新过期时间
     * @access public
     * @param string $key
     * @param integer $seconds
     * @return integer $code
     */
    public function RefreshExpireTime(string $key, int $seconds): int
    {
        // 剩余时间
        $remain_time = $this->ttl($key);
        $code        = 1;
        switch ($remain_time) {
            case -1:
                // 存在 key 但没有设置剩余过期时间
                $this->expire($key, $seconds);
                $code = $remain_time;
                break;
            case -2:
                // 不存在 key
                $code = -2;
                break;
            case $remain_time <= ($seconds * (2 / 3)):
                // 剩余时间小于等于给定时间的 2/3
                $this->expire($key, $seconds);
                break;
            default:
                // don't need set default
                break;
        }
        return $code;
    }

    /**
     * 删除hash表中指定字段 ,支持批量删除
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return int
     */
    public function hdel(string $key, string $field)
    {
        $field_arr = explode(',', $field);
        $del_num   = 0;

        foreach ($field_arr as $row) {
            $row      = trim($row);
            $del_num += $this->redis->hdel($key, $row);
        }
        return $del_num;
    }

    /**
     * 选择数据库
     * @param int $db_id 数据库ID号
     */
    public function select($db_id): bool
    {
        self::$db_id = $db_id;
        return $this->redis->select($db_id);
    }

    /**
     * 关闭所有连接
     */
    public static function closeAll(): void
    {
        foreach (static::$_instance as $o) {
            if ($o instanceof self) {
                $o->close();
            }
        }
    }

    /**
     * 得到当前数据库ID
     * @return int
     */
    public function getDbId()
    {
        return self::$db_id;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getConfig(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'auth' => $this->auth
        ];
    }

    /**
     * 得到一组的 hash 数据的值
     * @param string $prefix
     * @param string|array $ids
     */
    public function hashAll(string $prefix, $ids)
    {
        if (!$ids) {
            return [];
        }
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $arr = [];
        foreach ($ids as $id) {
            $key = $prefix . '.' . $id;
            $res = $this->redis->hgetall($key);
            if ($res != false) {
                $arr[$key] = $res;
            }
        }
        return $arr;
    }

    /**
     * 得到条批量删除key的命令
     * @param string $keys
     * @param integer|string $db_id
     */
    public function delKeys(string $keys, $db_id = 0): string
    {
        $redis_info = $this->getConfig();

        $cmd_arr = [
            'redis-cli', '-a',
            $redis_info['auth'], '-h',
            $redis_info['host'], '-p',
            $redis_info['port'], '-n',
            $db_id,
        ];

        $redis_str = implode(' ', $cmd_arr);
        return "{$redis_str} KEYS \"{$keys}\" | xargs {$redis_str} del";
    }
}
