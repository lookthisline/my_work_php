<?php

use \think\facade\Route;

Route::miss(function () {
    return clientResponse(null, '请求地址不存在', false);
});

return [
    '/'              => function () {
        return clientResponse(null, 'Hello World');
    },
    // 获取验证码
    'captcha'        => ['index/captcha/getCaptcha', ['method' => 'get']],
    // 注册
    'signUp'         => ['index/accounts/signUp', ['method' => 'put|options']],
    // 登录
    'login'          => ['index/accounts/login', ['method' => 'post|options']],
    // 用户列表
    'list/user'      => ['index/accounts/list', ['method' => 'get|options']],
    // 审核用户
    'audit/user/:id' => ['index/accounts/audit', ['method' => 'put|options'], ['id' => '\d+']],
    '[user]'         => [
        // 用户详情
        ':details_id' => ['index/accounts/details', ['method' => 'get|options'], ['details_id' => '\d+']],
        // 修改用户
        ':modify_id'  => ['index/accounts/modify', ['method' => 'put|options'], ['modify_id' => '\d+']],
        // 删除用户
        ':delete_id'  => ['index/accounts/delete', ['method' => 'delete|options'], ['delete_id' => '\d+']]
    ],
    'test' => 'index/test/index'
];
