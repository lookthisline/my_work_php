<?php

namespace app\common\expand;

use app\common\expand\JwtUtils;
use app\common\expand\RedisUtils;
use app\common\expand\Captcha\Main as CaptchaUtils;

/**
 * Utils 简单工厂
 */
final class UtilsFactory
{
    private static object $_redis;
    private static object $_jwt;
    private static object $_captcha;
    private static object $_file;
    // private static object $instance;

    private function __construct()
    {
        // disable initialization
    }

    // /**
    //  * 单例无法维持，失败
    //  * @param string $name 实例名
    //  * @param array $config 配置参数
    //  * @return $instance
    //  */
    // public final static function getInstance(string $instance_name, array $config = [])
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

    final public static function redis(array $config = []): RedisUtils
    {
        if (!isset(self::$_redis) || !(self::$_redis instanceof RedisUtils)) {
            self::$_redis = RedisUtils::getInstance($config);
        }
        return self::$_redis;
    }

    final public static function jwt(): JwtUtils
    {
        if (!isset(self::$_jwt) || !(self::$_jwt instanceof JwtUtils)) {
            self::$_jwt = new JwtUtils();
        }
        return self::$_jwt;
    }

    final public static function captcha(array $config = []): CaptchaUtils
    {
        if (!isset(self::$_captcha) || !(self::$_captcha instanceof CaptchaUtils)) {
            self::$_captcha = new CaptchaUtils($config);
        }
        return self::$_captcha;
    }

    final public static function file(): FileUtils
    {
        if (!isset(self::$_file) || !(self::$_file instanceof FileUtils)) {
            self::$_file = new FileUtils();
        }
        return self::$_file;
    }

    private function __clone()
    {
        // disable clone
    }
}
