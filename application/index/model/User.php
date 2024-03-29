<?php

namespace app\index\model;

use think\Model;
use think\Exception\PDOException;
use think\facade\Log;

class User extends Model
{
    protected $pk = 'id';

    protected $user = [
        'status'  => -4,
        'data'    => array()
    ];

    /**
     * 账户创建时间(create_time) 获取器
     * @access public
     * @param string $value
     * @return string
     */
    public function getCreateTimeAttr(string $value): string
    {
        return @date('Y-m-d H:i:s', $value) ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 注册
     * @param array $param_data
     * @return boolean|integer
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
     * @param array $param_data
     */
    public function Login(array $param_data)
    {
        try {
            $result = $this->where('nickname', $param_data['nickname'])
                ->field('id,account_status,nickname,user_level')
                ->find();
            if (!$result) {
                $this->user['status'] = -3;
                return;
            }
            switch ($result) {
                case (string)$result->value('passwd') !== (string)$param_data['passwd']:
                    $this->user['status'] = -1;
                    break;
                case $result->account_status < 0:
                    $this->user['status'] = -2;
                    break;
                case $result->account_status == 0:
                    $this->user['status'] = 0;
                    break;
                default:
                    $this->user['status'] = 1;
                    $this->user['data']   = $result->toarray();
                    break;
            }
        } catch (\Exception $e) {
            Log::write($e->getMessage(), 'error');
        } finally {
            return $this->user;
        }
    }

    /**
     * 获取用户列表(普通用户，待审核中)
     * @access public
     * @return array
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
     * @param integer $id
     * @return array
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
     * @param array $param_data
     * @return boolean
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
     * @param integer $id
     * @return boolean
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
     * @param integer $id
     * @return boolean
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
