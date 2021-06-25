<?php

namespace app\index\validate;

use app\common\base\BaseValidate;
use app\common\expand\Captcha\Main as CaptchaUtils;

class Accounts extends BaseValidate
{
    protected $rule = [
        'id'           => 'require|number',
        'data'         => 'array',
        'page'         => 'number',
        'avatar'       => 'require|file|fileExt:png,jpg,jpeg,pjpeg,bmp|fileSize:30720',
        'nickname'     => 'chsDash|length:2,16|unique:user',
        'phone'        => 'mobile|virtualNumber',
        'passwd'       => 'confirm:repasswd|alphaDash|length:6,16',
        'repasswd'     => 'require|confirm:passwd',
        'name'         => 'chsDash|length:2,16',
        'position'     => 'chsDash|length:2,20',
        'email'        => 'email|max:254',
        'captcha_code' => 'require|length:5|alphaNum|checkCode',
        'captcha_id'   => 'require|max:32|alphaNum'
    ];

    protected $message = [
        'avatar.require'         => '请上传头像',
        'id.require'             => '参数缺少',
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
        'captcha_id.require'     => '验证码参数缺失',
        'captcha_id.max'         => '请填写正确的验证码校验参数',
        'captcha_id.alphaNum'    => '请填写正确的验证码校验参数',
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
        // 审核账户
        'audit'   => ['id'],
        // 用户详情
        'details' => ['id'],
        // 删除用户
        'delete'  => ['id'],
        // 修改用户
        'modify'  => ['nickname', 'name', 'phone', 'position', 'email'],
        // 用户列表
        'list'    => ['page'],
    ];

    /**
     * 登录(login) 验证场景重写
     * @access public
     * @return self
     */
    public function sceneLogin(): self
    {
        return $this->only(['nickname', 'passwd', 'captcha_id', 'captcha_code'])
            ->remove('nickname', 'unique')
            ->remove('passwd', 'confirm');
    }

    /**
     * 注册(signUp) 验证场景重写
     * @access public
     * @return self
     */
    public function sceneSignUp(): self
    {
        return $this->only(['avatar', 'nickname', 'passwd', 'name', 'phone', 'position', 'email'])
            ->append('nickname', 'require')
            ->append('name', 'require')
            ->append('passwd', 'require')
            ->append('phone', 'require')
            ->append('position', 'require')
            ->append('email', 'require');
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
     * @param string $value
     * @return boolean
     */
    protected function virtualNumber(string $value): bool
    {
        return (bool)preg_match('/^1(6[257]|7[01])\d{8}$/', $value) ? false : true;
    }

    /**
     * 验证码校验
     * @access protected
     * @param string $value
     * @param string $rule
     * @param array  $data
     * @return boolean
     */
    protected function checkCode(string $value, string $rule, array $data): bool
    {
        return (new CaptchaUtils)->check($value, array_key_exists('captcha_id', $data) ? $data['captcha_id'] : '');
    }
}
