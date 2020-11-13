<?php

namespace app\common\expand;

use app\common\expand\JwtUtils;
use app\common\expand\RedisUtils;
use app\common\expand\Captcha\Main as CaptchaUtils;

// Utils 简单工厂模式
final class UtilsFactory
{
    private static object $redis;
    private static object $jwt;
    private static object $captcha;
    // private static object $instance;

    private function __construct()
    {
    }

    // /**
    //  * 单例无法维持，失败
    //  * @param String $name 实例名
    //  * @param Array $config 配置参数
    //  * @return $instance
    //  */
    // public final static function getInstance(String $instance_name, array $config = [])
    // {
    //     // 类名
    //     $instance_full_name = __NAMESPACE__ . '\\' . $instance_name;

    //     if (!isset(self::$instance) && class_exists($instance_full_name)) {
    //         // 初始化指定实例
    //         self::$instance = new $instance_full_name($config);
    //     }

    //     if (self::$instance instanceof $instance_full_name) {
    //         // 重新赋值实例化指定工具类
    //         self::$instance = new $instance_full_name($config);
    //     }
    //     return self::$instance;
    // }

    public final static function redis(array $config = []): RedisUtils
    {
        if (!(self::$redis instanceof RedisUtils)) {
            // self::$redis = new RedisUtils($config);
            self::$redis = RedisUtils::getInstance($config);
        }
        return self::$redis;
    }

    public final static function jwt(): JwtUtils
    {
        if (!isset(self::$jwt)) {
            self::$jwt = new JwtUtils();
        }
        return self::$jwt;
    }

    public final static function captcha(array $config = []): CaptchaUtils
    {
        if (!isset(self::$captcha)) {
            self::$captcha = new CaptchaUtils($config);
        }
        return self::$captcha;
    }

    private function __clone()
    {
    }
}
