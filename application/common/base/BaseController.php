<?php

namespace app\common\base;

use app\common\enum\Redis as RedisEnum;
use app\common\expand\UtilsFactory;
use think\Db;

class BaseController extends \think\Controller
{
    // jwt
    private static string $token;
    // jwt 工具类
    protected object $jwt_utils;
    // redis 工具类
    protected object $redis_utils;
    // 当前登录用户信息
    protected array $user;

    /**
     * @access public
     * @return \think\response\Json|Void
     */
    public final function initialize()
    {
        // 响应 OPTIONS 请求
        if (strtoupper(request()->method()) === "OPTIONS") {
            return clientResponse(null, 'success', true, 200, [
                'Access-Control-Allow-Methods' => request()->header('access-control-request-method', ''),
                'Access-Control-Allow-Headers' => request()->header('access-control-request-headers', '')
            ]);
        }

        $validate_error_message = self::checkActionParam();
        if (!is_bool($validate_error_message)) {
            return clientResponse(null, $validate_error_message, false);
        }

        $this->jwt_utils   = UtilsFactory::jwt();
        $this->redis_utils = UtilsFactory::redis();

        if (isset(self::$token) && self::$token) {
            $this->verifyToken(self::$token);
        }
    }

    /**
     * 加载模块验证器类文件验证提交参数
     * @access private
     * @return Bool|String
     */
    private static function checkActionParam()
    {
        // 校验结果
        $validate_result   = true;
        // 获取所有客户端传过来的数据
        $client_param_data = request()->param(true);
        // 获取用户端在请求头携带的用户 token
        self::$token = (string)request()->header('Authorization', '');
        // url中携带的控制器名
        $controller_name = '';

        if (preg_match("/./", request()->controller())) {
            $controller_array = @explode(".", request()->controller());
            $controller_name  = ucfirst(end($controller_array));
        } else {
            $controller_name = request()->controller();
        }

        // 当前请求url的对应验证器类
        $validate = '\app\\' . request()->module() . '\validate\\' . $controller_name;

        if (class_exists($validate)) {
            $validate = new $validate();
            $action_name = strtolower(request()->action(true));

            // 验证器基类 设置排除场景
            // $excludeActionBase  = $validate->getBaseExcludeActionScene();

            // 验证器子类 设置排除场景
            // $excludeActionChild = $validate->getExcludeActionScene();
            // $excludeAction      = array_merge($excludeActionBase, $excludeActionChild);

            // 排除不需验证场景
            // if (in_array($controller_name . "/" . $request->action(true), $excludeAction)) {
            //     return true;
            // }

            // 判断验证场景是否存在
            if ($validate->hasScene($action_name)) {
                $validate_result = $validate->scene($action_name)->check($client_param_data) === true ? true : $validate->getError();
            }
        }
        return $validate_result;
    }

    /**
     * JWT 信息校验
     * @access private
     * @param String $token 在验证器中定义 token 必须传输
     * @return \think\response\Json|Void
     */
    private function verifyToken(String $token)
    {
        // 解析 token
        $token_data = $this->jwt_utils->verifyToken($token);

        // 验证有效性，是否过期
        if (!$token_data) {
            return clientResponse(null, '请求参数不可解析，访问异常！');
        }

        // 查询是否为系统生成的 jwt_token（查询存储jwt文件夹中是否存在 token）
        $is_set_jwt = $this->redis_utils::hexists(RedisEnum::JWT_FOLDER . (string)$token_data['jwt_hash_key'], (string)$token_data['jti']);
        if ((bool)$is_set_jwt) {
            $redis_jwt_token_val = $this->redis_utils::hget(RedisEnum::JWT_FOLDER . (string)$token_data['jwt_hash_key'], (string)$token_data['jti']);

            // 传入jwt信息与保存的jwt信息是否一致
            if ($redis_jwt_token_val !== $token) {
                return clientResponse(null, '非法登录信息', false);
            }

            // 刷新 jwt 过期时间
            $run_result = $this->redis_utils->RefreshExpireTime(RedisEnum::JWT_FOLDER . (string)$token_data['jwt_hash_key'], RedisEnum::JWT_LIFECYCLE);

            if ($run_result === -2) {
                return clientResponse(null, '登录凭证过期，请重新登录', false);
            }
        } else {
            return clientResponse(null, '登录凭证过期，请重新登录', false);
        }

        // auth_hash_key 由公共方法 getUniqueCode() 生成并保存于jwt中，用于存储用户部分信息
        // 提取关键数据，验证关键数据有效性(查询存储用户信息文件夹)
        if (isset($token_data['auth_hash_key']) && $token_data['auth_hash_key']) {
            // 查询jwt对应用户信息
            $isset_user = $this->redis_utils::exists(RedisEnum::USER_FOLDER . (string)$token_data['auth_hash_key']);
            if ((bool)$isset_user) {
                // Redis 中取出缓存的用户数据
                $temp_user_data = $this->redis_utils::hgetall(RedisEnum::USER_FOLDER . (string)$token_data['auth_hash_key']);
                if (empty($temp_user_data) || !isset($temp_user_data['id'])) {
                    return clientResponse(null, '无效凭证信息，请重新登录', false);
                }

                // 校验用户信息
                $this->verifyUser($temp_user_data['id']);
                // 将redis中用户信息拿来使用
                $this->user = $temp_user_data;

                // 刷新 用户信息 过期时间
                $run_result = $this->redis_utils->RefreshExpireTime(RedisEnum::USER_FOLDER . (string)$token_data['auth_hash_key'], RedisEnum::JWT_LIFECYCLE);
                if ($run_result === -2) {
                    return clientResponse(null, '登录凭证过期，请重新登录', false);
                }
            } else {
                return clientResponse(null, '登录凭证过期，请重新登录', false);
            }
        } else {
            return clientResponse(null, '无效请求，请重新登录', false);
        }
    }

    /**
     * 生成并保存 jwt 信息
     * @access protected
     * @param Array $payload
     * @param Array $user_data
     * @return String
     */
    protected final function buildToken(array $payload = [], array $user_data = []): string
    {
        // 生成 token 字符串
        $token_str = $this->jwt_utils->buildToken($payload);

        // 存入 redis；jwt 部分
        $this->redis_utils::hset(RedisEnum::JWT_FOLDER . (string)$this->jwt_utils->jwt_hash_key, (string)$this->jwt_utils->jti, $token_str);

        // 存入 redis；用户信息 部分
        if (!empty($user_data)) {
            foreach ($user_data as $k => $v) {
                $this->redis_utils::hset(RedisEnum::USER_FOLDER . (string)$this->jwt_utils->auth_hash_key, (string)$k, (string)$v);
            }
        } else {
            $this->redis_utils::hset(RedisEnum::USER_FOLDER . (string)$this->jwt_utils->auth_hash_key, '', '');
        }

        // 刷新过期时间（确保在覆盖的是旧数据时能再用）
        $this->redis_utils->RefreshExpireTime(RedisEnum::JWT_FOLDER . (string)$this->jwt_utils->jwt_hash_key, RedisEnum::JWT_LIFECYCLE);
        $this->redis_utils->RefreshExpireTime(RedisEnum::USER_FOLDER . (string)$this->jwt_utils->auth_hash_key, RedisEnum::JWT_LIFECYCLE);

        return $token_str;
    }

    /**
     * 查询用户有效性
     * @access public
     * @param Integer $user_id
     * @return \think\response\Json|Void
     */
    protected final function verifyUser(int $user_id)
    {
        $result = Db::name('user')->where('id', $user_id)->find();
        if (!$result) {
            // 无效登录信息，请重新登录
            return clientResponse(null, '无效凭证信息，请重新登录', false);
        } elseif ($result['account_status'] == -1) {

            // 审核状态(-1 待审核，1 正常用户)
            return clientResponse(null, '账户正在审核中，请等待审核完成', false);
        }
    }

    /**
     * 验证当前用户是否有权限操作
     * @access public
     * @param Integer $lv 用户级别(当前系统中 1 超管, 2 普管, 3 用户)
     * @return Boolean
     */
    protected final function decidePrivilege(int $lv): bool
    {
        return empty($this->user) or (!isset($this->user['user_level']) ? false : (int)$this->user['user_level'] > $lv);
    }
}
