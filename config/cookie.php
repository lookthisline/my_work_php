<?php
// cookie 配置
return [
    // cookie 名称前缀
    'prefix'    => '',
    // cookie 保存时间
    'expire'    => 0,
    // cookie 保存路径
    'path'      => '/',
    // cookie 有效域名
    'domain'    => request()->rootDomain(),
    // cookie 启用安全传输
    'secure'    => true,
    // httponly设置
    'httponly'  => '',
    // 是否使用 setcookie
    'setcookie' => true
];
