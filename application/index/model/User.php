<?php

namespace app\index\model;

use think\Model;
use think\Exception\PDOException;
use think\facade\Log;

class User extends Model
{
    protected $pk = 'id';

    /**
     * 账户创建时间(create_time) 获取器
     * @access public
     * @param String $value
     * @return String
     */
    public function getCreateTimeAttr(String $value): string
    {
        return @date('Y-m-d H:i:s', $value) ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 注册
     * @param Array $param_data
     * @return Boolean|Integer
     */
    public function SignUp(array $param_data)
    {
        $result = false;

        $this->startTrans();
        try {
            $result = $this->allowField(true)
                ->insert($param_data, false, true);
            $this->commit();
        } catch (PDOException $e) {
            $this->rollback();
            Log::write($e->getMessage(), 'error');
        }
        return $result;
    }

    /**
     * 登录
     * @param Array $param_data
     * @return Array|Null|\PDOStatement|String|\think\Model
     */
    public function Login(array $param_data)
    {
        return $this->allowField(true)
            ->field('id,account_status,nickname,user_level')
            ->where($param_data)
            ->find();
    }

    /**
     * 获取用户列表(普通用户，待审核中)
     * @access public
     * @return Array
     */
    public function getList()
    {
        $account_status_arr = [-1 => '待审核', 1 => '已审核'];
        return $this->where([
            // 'account_status' => -1,
            'user_level' => 3
        ])
            ->field('id,nickname,name,phone,position,email,account_status,create_time')
            ->order('create_time', 'desc')
            ->withAttr('account_status', function ($value) use ($account_status_arr) {
                // 审核状态(account_status) 获取器
                return $account_status_arr[in_array($value, [-1, 1]) ? $value : -1];
            })
            ->paginate(15);
    }

    /**
     * 获取用户详情(普通用户)用于展示
     * @access public
     * @param Integer $id
     * @return Array
     */
    public function getUserDetails(int $id)
    {
        return $this->where([
            // 'account_status' => -1,
            'user_level' => 3,
            'id'         => $id
        ])
            ->field('id,nickname,name,phone,position,email')
            ->find();
    }

    /**
     * 修改用户
     * @param Array $param_data
     * @return Boolean
     */
    public function modifyUser(array $param_data): bool
    {
        $result = false;

        // 不存在 id 不做任何改变
        if (!array_key_exists('id', $param_data)) {
            return true;
        }

        $this->startTrans();
        try {
            $result = $this->allowField(['name', 'nickname', 'phone', 'position', 'email'])
                ->data($param_data)
                ->updateData([$this->pk => $param_data['id'], 'user_level' => 3]);
            $this->commit();
        } catch (PDOException $e) {
            $this->rollback();
            Log::write($e->getMessage(), 'error');
        }
        return $result;
    }

    /**
     * 审核用户
     * @param Integer $id
     * @return Boolean
     */
    public function auditUsers(int $id): bool
    {
        $result = $this->get($id);

        if ($result) {
            if ($result->account_status == 1) {
                return true;
            }
            $result->account_status = 1;
            return $result->save();
        }

        return false;
    }

    /**
     * 删除用户
     * @access public
     * @param Integer $id
     * @return Boolean
     */
    public function deleteUser(int $id): bool
    {
        $this->startTrans();
        try {
            return $this->destroy(function ($query) use ($id) {
                $query->where([
                    'id'         => $id,
                    // 普通管理员、普通用户
                    'user_level' => 3
                ]);
            });
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            Log::write($e->getMessage(), 'error');
        }
        return false;
    }
}
