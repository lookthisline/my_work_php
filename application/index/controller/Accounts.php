<?php

namespace app\index\controller;

use think\Request;
use think\facade\Config;
use app\common\base\BaseController;
use app\index\model\User as UserModel;
use app\common\enum\Model\User as UserEnum;

class Accounts extends BaseController
{
    /**
     * 登录(普通用户与管理员共用登录)
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/login
     */
    public function login(Request $request)
    {
        $param_data = [
            'nickname' => $request->post('nickname/s'),
            'passwd'   => md5($request->post('passwd/s'))
        ];

        $user_model = new UserModel();
        $user       = $user_model->Login($param_data);
        if ($user['status'] > 0) {
            // 生成 jwt
            $token = $this->buildToken([], $user['data']);
            $user['data'] = array_merge($user['data'], ['token' => $token]);
        }
        return clientResponse($user['data'], UserEnum::USER_STATUS[$user['status']], $user['status'] > 0);
    }

    /**
     * 注册
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/signUp
     */
    public function signUp(Request $request)
    {
        $param_data = [
            'nickname'    => $request->put('nickname/s'),
            'passwd'      => md5($request->put('passwd/s')),
            'name'        => $request->put('name/s'),
            'phone'       => $request->put('phone/d'),
            'position'    => $request->put('position/s'),
            'email'       => $request->put('email/s'),
            'create_time' => time()
        ];

        // 保存上传文件到指定路径
        $file = $request->file('avatar');
        $info = $file->move(Config::get('upload.upload_path'));

        if ($info) {
            $param_data['avatar'] = (string)Config::get('upload.upload_path') . DIRECTORY_SEPARATOR . $info->getSaveName() . PHP_EOL;
        } else {
            return clientResponse(null, '文件上传失败：' . $file->getError(), false);
        }

        // 创建用户，保存数据
        $user_model = new UserModel();
        $result     = $user_model->SignUp($param_data);

        if (!$result) {
            return clientResponse(null, '注册失败，请稍后再试', false);
        }

        return clientResponse(null, '注册成功');
    }

    /**
     * 用户列表(分页，用户类型区分)
     * @access public
     * @return \think\response\Json
     * @link /index/accounts/list
     */
    public function List()
    {
        // 判断当前用户权限
        if ($this->decidePrivilege(2)) {
            // 需为管理员
            return clientResponse(null, '权限不足，操作失败', false);
        }

        $user_model = new UserModel();
        $result     = $user_model->getList();

        return clientResponse($result);
    }

    /**
     * 用户详情
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/details
     */
    public function Details(Request $request)
    {
        // 判断当前用户权限
        if ($this->decidePrivilege(2)) {
            // 需为管理员
            return clientResponse(null, '权限不足，操作失败', false);
        }

        $user_model = new UserModel();
        $result     = $user_model->getUserDetails($request->get('id/d', 0));

        return clientResponse($result);
    }

    /**
     * 修改用户
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/modify
     */
    public function Modify(Request $request)
    {
        // 判断当前用户权限
        if ($this->decidePrivilege(1)) {
            // 需为超级管理员
            return clientResponse(null, '权限不足，操作失败', false);
        }

        $data = [
            'id'       => $request->put('id/d', null),
            'name'     => $request->put('name/s', null),
            'nickname' => $request->put('nickname/s', null),
            'phone'    => $request->put('phone/d', null),
            'position' => $request->put('position/s', null),
            'email'    => $request->put('email/s', null),
        ];

        // 过滤空值
        $data = array_filter($data);

        $user_model = new UserModel();
        $result     = $user_model->modifyUser($data);

        if (!$result) {
            return clientResponse(null, '修改用户失败', false);
        }
        return clientResponse();
    }

    /**
     * 审核用户
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/Audit
     */
    public function Audit(Request $request)
    {
        // 判断当前用户权限
        if ($this->decidePrivilege(1)) {
            // 需为超级管理员
            return clientResponse(null, '权限不足，操作失败', false);
        }

        $user_model = new UserModel();
        $result     = $user_model->auditUsers($request->put('id/d', 0));

        return $result ? clientResponse($result) : clientResponse(null, '操作失败，请检查参数稍后重试', false);
    }

    /**
     * 删除用户
     * @access public
     * @param Request $request
     * @return \think\response\Json
     * @link /index/accounts/delete
     */
    public function Delete(Request $request)
    {
        // 判断当前用户权限
        if ($this->decidePrivilege(1)) {
            // 需为超级管理员
            return clientResponse(null, '权限不足，操作失败', false);
        }

        $user_model = new UserModel();
        $result     = $user_model->deleteUser($request->delete('id/d', 0));

        return $result ? clientResponse($result) : clientResponse(null, '操作失败，请稍后再试', false);
    }
}
