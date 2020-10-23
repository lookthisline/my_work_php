<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    'id'             => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // session cookie_domain
    'domain'         => '192.168.20.6',
    // SESSION 前缀
    // 'prefix'         => 'think',
    // 驱动方式 支持redis memcache memcached
    'type'           => '',
    // 是否自动开启 SESSION
    'auto_start'     => true,
    // 是否使用 cookie
    'use_cookies'    => true,
    // 是否启用安全传输
    'secure'         => true,
    // strict 最严谨，只有与当前网页网址一致才能发送（remote.example 与 site.example 无法发送）
    // Lax 默认，使用 GET remote.example 向 site.example 发送，Cookie 将会送向 remote.example (POST 则不会发送 Cookie)
    // None (需有 HTTPS 搭配，否则一样等于 Lax)
    'samesite'       => 'None'
];
