<?php

namespace app\common\expand;

/**
 * 时间日期工具类
 */
class TimeDateUtils
{
    /**
     * 获取 ISO-8601 时间
     * @param int|string
     * @return string
     */
    public function gmt_iso8601($time): string
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }
}
