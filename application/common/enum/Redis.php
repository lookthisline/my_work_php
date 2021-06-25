<?php

namespace app\common\enum;

/**
 * Redis 表对应关系枚举类（存放于配置文件也行）
 */
final class Redis
{
    // 用户信息文件夹
    const USER_FOLDER       = 'user:';

    // JWT 信息文件夹
    const JWT_FOLDER        = 'jwt:';
    // jwt 生存周期 一周 单位 秒
    const JWT_LIFECYCLE     = 604800;

    // 验证码信息存放文件夹
    const CAPTCHA_FOLDER    = 'captcha:';
    // 验证码保存期限 单位秒
    const CAPTCHA_LIFECYCLE = 60;

    // 文件流文件夹
    const DOCUMENT_FOLDER   = 'document:';

    private const RESTRICT  = 'restrict:';

    const FEATURE_FOLDER    = self::RESTRICT . 'feature:';

    // 令牌桶文件夹
    const BUCKET_FOLDER     = self::RESTRICT . 'bucket:';
}
