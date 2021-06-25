<?php

namespace app\common\expand;

use app\common\enum\Redis as RedisEnum;

/**
 * 流量控制工具类
 */
final class NetworkTrafficUtils
{
    /**
     * 简单计数器限流（QPS限流；秒级以上的时间周期，存在临界问题）
     * @param string  $feature 客户端特征字符串
     * @param integer $number  单位时间内最多允许请求的次数
     * @param integer $minute  分钟
     * @return boolean
     */
    public static function speedCounter(string $feature = '', int $number = 0, int $minute = 1): bool
    {
        if (!$feature || !intval($number) || !intval($minute)) {
            return false;
        }
        return self::executeLuaScript('SpeedCounter', [
            RedisEnum::FEATURE_FOLDER . $feature,
            intval($minute) * 60,
            intval($number)
        ]);
    }

    /**
     * 令牌桶限流（令牌桶取令牌）
     * @param integer $consumption 每次执行消耗令牌数
     * @return boolean
     */
    public static function takeToken(int $consumption = 1): bool
    {
        if (!intval($consumption)) {
            return false;
        }
        return self::executeLuaScript('TakeToken', [
            RedisEnum::BUCKET_FOLDER . config('bucket.key'),
            intval($consumption),
            intval(config('bucket.max'))
        ]);
    }

    /**
     * 执行lua脚本
     * @param string  $script_name     脚本名
     * @param array   $param           传入参数数组
     * @param boolean $require_boolean 是否需要布尔型结果
     * @return boolean|mixed
     */
    public static function executeLuaScript(string $script_name = '', array $param = [], bool $require_boolean = true)
    {
        if (!$script_name || !$param) {
            return $require_boolean ? false : 0;
        }
        try {
            $lua_script = file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Script' . DIRECTORY_SEPARATOR . 'Lua' . DIRECTORY_SEPARATOR . $script_name . '.lua'));
        } catch (\Exception $e) {
            trace([
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ], 'error');
            return $require_boolean ? false : 0;
        }
        $lua_script_sha1   = UtilsFactory::redis()->script('load', $lua_script); // 将脚本加入缓存
        $lua_script_exists = current(UtilsFactory::redis()->script('exists', $lua_script_sha1));
        $result            = $lua_script_exists ? UtilsFactory::redis()->evalsha($lua_script_sha1, $param, count($param)) : UtilsFactory::redis()->eval($lua_script, $param, count($param));
        return $require_boolean ? boolval($result) : $result;
    }
}
