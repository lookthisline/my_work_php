<?php

namespace app\common\expand;

use app\common\expand\JwtUtils;
use app\common\expand\RedisUtils;
use app\common\expand\Captcha\Main as CaptchaUtils;

// Utils 简单工厂模式
class UtilsFactory
{
    private static object $redis;
    private static object $jwt;
    private static object $captcha;

    public static function redis(array $config = []): RedisUtils
    {
        if (!isset(self::$redis)) {
            self::$redis = new RedisUtils($config);
        }
        return self::$redis;
    }

    public static function jwt(): JwtUtils
    {
        if (!isset(self::$jwt)) {
            self::$jwt = new JwtUtils();
        }
        return self::$jwt;
    }

    public static function captcha(array $config = []): CaptchaUtils
    {
        if (!isset(self::$captcha)) {
            self::$captcha = new CaptchaUtils($config);
        }
        return self::$captcha;
    }
}
