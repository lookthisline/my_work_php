<?php

namespace app\index\validate;

use app\common\base\BaseValidate;
use app\common\expand\Captcha\Main as CaptchaUtils;

class Accounts extends BaseValidate
{
    protected $rule = [
        'id'           => 'number',
        'data'         => 'array',
        'page'         => 'number',
        'avatar'       => 'file|fileExt:png,jpg,jpeg,pjpeg,bmp|fileSize:30720',
        'nickname'     => 'require|chsDash|length:2,16|unique:user',
        'phone'        => 'require|mobile|virtualNumber',
        'passwd'       => 'require|confirm:repasswd|alphaDash|length:6,16',
        'repasswd'     => 'require|confirm:passwd',
        'name'         => 'require|chsDash|length:2,16',
        'position'     => 'require|chsDash|length:2,20',
        'email'        => 'require|email|max:254',
        'captcha_code' => 'require|length:5|alphaNum|checkCode'
    ];

    protected $message = [
        'id.number'              => '参数类型错误',
        'data.array'             => '参数类型错误',
        'page.number'            => '参数类型错误',
        'phone.mobile'           => '手机号格式不正确',
        'phone.virtualNumber'    => '禁止使用虚拟电话号',
        'passwd.require'         => '请填写密码',
        'passwd.length'          => '密码位数只能为 6~16 位',
        'passwd.alphaDash'       => '密码只能由字母、数字、下划线 _ 、破折号 - 组成',
        'passwd.confirm'         => '两次密码输入不一致，请重新输入',
        'nickname.unique'        => '该用户名已被使用',
        'captcha_code.require'   => '请填写验证码',
        'captcha_code.alphaNum'  => '验证码只能为字母和数字',
        'captcha_code.length'    => '请输入正确的验证码',
        'captcha_code.checkCode' => '请输入正确的验证码',
        'position.require'       => '请输入职务名称',
        'position.length'        => '请输入2~16字的职务名称',
        'email.require'          => '请输入电子邮箱',
        'email.email'            => '请输入正确的电子邮箱',
        'email.max'              => '电子邮箱最大可输入254字',
    ];

    protected $scene = [
        'index'   => '',
        // 注册
        'signup'  => ['avatar', 'nickname', 'passwd', 'name', 'phone', 'position', 'email'],
        // 修改用户
        'modify'  => ['nickname', 'name', 'phone', 'position', 'email'],
        // 用户列表
        'list'    => ['page'],
        // 用户详情
        'details' => ['id'],
        // 删除用户
        'delete'  => ['id']
    ];

    /**
     * SignIn 验证场景重写
     * @access public
     * @return Self
     */
    public function sceneSignIn(): self
    {
        return $this->only(['nickname', 'passwd', 'captcha_code'])
            ->remove('nickname', 'unique')
            ->remove('passwd', 'confirm');
    }

    /**
     * 虚拟电话号过滤
     * 移动：
     * 165
     * 1703 1705 1706
     * 电信：
     * 162
     * 1700 1701 1702
     * 联通：
     * 167
     * 1704 1707 1708 1709
     * 1710 1711 1712 1713 1714 1715 1716 1717 1718 1719
     * @access protected
     * @param String $value
     * @return Boolean
     */
    protected function virtualNumber(String $value): Bool
    {
        return (bool)preg_match('/^1(6[257]|7[01])\d{8}$/', $value) ? false : true;
    }

    /**
     * 验证码校验
     * @access protected
     * @param String $value
     * @param String $rule
     * @param Array $data
     * @return Boolean
     */
    protected function checkCode(String $value, String $rule, array $data): Bool
    {
        return (new CaptchaUtils)->check($value, array_key_exists('captcha_id', $data) ? $data['captcha_id'] : '');
    }
}
