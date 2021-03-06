<?php

namespace app\index\controller;

use app\common\base\BaseController;
use app\common\expand\UtilsFactory;
use app\common\expand\StringUtils;

class Captcha extends BaseController
{
    /**
     * 获取验证码
     * @access public
     * @link /index/Captcha/getCaptcha
     */
    public function getCaptcha()
    {
        $id = StringUtils::getUniqueCode();
        return clientResponse([
            'picture'    => UtilsFactory::captcha()->build($id),
            'captcha_id' => $id
        ]);
    }
}
