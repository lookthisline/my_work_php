<?php

namespace app\common\enum\Model;

/**
 * 模型类 User 的枚举类
 */
class User
{
    const USER_STATUS = [
        1  => '登录成功',
        0  => '未知用户状态，禁止登录',
        -1 => '用户名或密码输入错误，请稍后再试',
        -2 => '用户正在等待审核中',
        -3 => '此用户不存在',
        -4 => '系统错误，请稍后再试'
    ];
}
