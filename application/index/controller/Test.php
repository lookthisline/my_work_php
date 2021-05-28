<?php

namespace app\index\controller;

use app\common\base\BaseController;
use think\Controller;
use think\Request;
use think\db\Where;
use app\common\expand\FileUtils;
use app\common\expand\UtilsFactory;

// class Test extends Controller
class Test extends BaseController
{
    public function index()
    {
        $file_utils = UtilsFactory::file();
        $file_utils->blob('static/images/avatar.png');
    }
}
