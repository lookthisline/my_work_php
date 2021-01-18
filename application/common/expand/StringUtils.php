<?php

namespace app\common\expand;

/**
 * 字符串相关工具类
 */
class StringUtils
{
    /**
     * 获取一个32字符长度唯一标识
     * @return String
     */
    public static function getUniqueCode(): string
    {
        $time_arr = explode(' ', microtime());
        return substr(md5($time_arr[1] . substr($time_arr[0], 2) . mt_rand(0, 9)), 0, 30) . mt_rand(10, 99);
    }

    /**
     * Url安全的 Base64 编码
     * @param string $string
     * @return string
     */
    public static function url_safe_base64encode(string $string): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    /**
     * Url 安全的 Base64 解码
     * @param string $string
     * @return string
     */
    public static function url_safe_base64decode(string $string): string
    {
        $data      = str_replace(array('-','_'), array('+','/'), $string);
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= substr('====', $remainder);
        }
        return base64_decode($data);
    }

    /**
     * 取汉字的第一个字的首字母
     * @param string $str
     * @return string
     */
    public static function getFirstCharter(string $string): string
    {
        if (!$string) {
            return '';
        }
        $first_char = ord($string[0]);
        if ($first_char >= ord('A') && $first_char <= ord('z')) {
            return strtoupper($string[0]);
        }

        $string_1 = iconv('UTF-8', 'gb2312', $string);
        $string_2 = iconv('gb2312', 'UTF-8', $string_1);
        $s        = $string_2 == $string ? $string_1 : $string;
        $ascii    = ord($s[0]) * 256 + ord($s[1]) - 65536;

        $char = ['A' => [-20319, -20284],'B' => [-20283, -19776],'C' => [-19775, -19219],'D' => [-19218, -18711],'E' => [-18710, -18527],'F' => [-18526, -18240],'G' => [-18239, -17923],'H' => [-17922, -17418],'J' => [-17417, -16475],'K' => [-16474, -16213],'L' => [-16212, -15641],'M' => [-15640, -15166],'N' => [-15165, -14923],'O' => [-14922, -14915],'P' => [-14914, -14631],'Q' => [-14630, -14150],'R' => [-14149, -14091],'S' => [-14090, -13319],'T' => [-13318, -12839],'W' => [-12838, -12557],'X' => [-12556, -11848],'Y' => [-11847, -11056],'Z' => [-11055, -10247]];
        $result = function ($ascii) use ($char) {
            foreach ($char as $k=>$v) {
                if ($ascii >= reset($v) && $ascii <= end($v)) {
                    return $k;
                }
            }
            return '';
        };
        return $result($ascii);
    }
}
