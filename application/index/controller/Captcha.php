<?php

namespace app\index\controller;

use app\common\base\BaseController;
use app\common\expand\Captcha\Main as CaptchaUtils;

class Captcha extends BaseController
{
    /**
     * 获取验证码
     * @access public
     * @link /index/Captcha/getCaptcha
     */
    public function getCaptcha()
    {
        $id = getUniqueCode();
        return clientResponse([
            'picture'    => (new CaptchaUtils())->build($id),
            'captcha_id' => $id
        ]);
    }
}
