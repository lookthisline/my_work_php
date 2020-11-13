<?php

namespace app\index\validate;

use app\common\base\BaseValidate;

class Test extends BaseValidate
{

    protected $rule    = [
        'abc' => 'require|length:5'
    ];
    protected $message = [];
    protected $scene   = [
        // 'test' => 'abc'
    ];
    // public function sceneTestBbc()
    // {
    //     return $this->only(['abc']);
    // }
}
