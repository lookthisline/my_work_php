<?php

namespace app\index\controller;

use app\common\base\BaseController;
use think\Controller;
use think\Request;
use think\db\Where;
use app\common\expand\FileUtils;

// class Test extends Controller
class Test extends BaseController
{
    private static array $cb;
    public function testBbc()
    {
        // self::$cb = [];
        // echo phpinfo();
        // $validate = new \app\index\validate\Test;
        // $result   = $validate->scene(__FUNCTION__)->check(request()->param());
        // var_dump($result, $validate->getError());
        var_dump(config('cache.file'));
    }

    public function testBbd(Request $request)
    {
        // var_dump(FileUtils::delete(), __PUBLIC__);
        // echo rtrim('test', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        // var_dump(file_exists('router.php'));
        $a = '/sr/as';
        echo ltrim($a,DIRECTORY_SEPARATOR);
        // echo 23;
    }
}
