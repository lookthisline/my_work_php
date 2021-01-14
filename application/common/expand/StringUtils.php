<?php

namespace app\common\expand;

/**
 * 字符串相关工具类
 */
class StringUtils
{
    /**
     * Url安全的Base64编码
     * @param $string
     * @return mixed|string
     */
    public static function url_safe_b64decode($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

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
     * base64_url 编码
     * @param String $input
     * @return String
     */
    public function base64UrlEncode(String $input): String
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64_url 解码
     * @param String $input
     * @return String
     */
    public function base64UrlDecode(String $input): String
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $add_len = 4 - $remainder;
            $input   .= str_repeat('=', $add_len);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * 取汉字的第一个字的首字母
     * @param string $str
     * @return string
     */
    public static function getFirstCharter(string $string):string
    {
        if (!$string) {
            return '';
        }
        $result     = '';
        $first_char = ord($string[0]);

        if ($first_char >= ord('A') && $first_char <= ord('z')) {
            return strtoupper($string[0]);
        }

        $string_1 = iconv('UTF-8', 'gb2312', $string);
        $string_2 = iconv('gb2312', 'UTF-8', $string_1);
        $s        = $string_2 == $string ? $string_1 : $string;
        $ascii    = ord($s[0]) * 256 + ord($s[1]) - 65536;

        !($ascii >= -20319 && $ascii <= -20284) ?: $result = 'A';
        !($ascii >= -20283 && $ascii <= -19776) ?: $result = 'B';
        !($ascii >= -19775 && $ascii <= -19219) ?: $result = 'C';
        !($ascii >= -19218 && $ascii <= -18711) ?: $result = 'D';
        !($ascii >= -18710 && $ascii <= -18527) ?: $result = 'E';
        !($ascii >= -18526 && $ascii <= -18240) ?: $result = 'F';
        !($ascii >= -18239 && $ascii <= -17923) ?: $result = 'G';
        !($ascii >= -17922 && $ascii <= -17418) ?: $result = 'H';
        !($ascii >= -17417 && $ascii <= -16475) ?: $result = 'J';
        !($ascii >= -16474 && $ascii <= -16213) ?: $result = 'K';
        !($ascii >= -16212 && $ascii <= -15641) ?: $result = 'L';
        !($ascii >= -15640 && $ascii <= -15166) ?: $result = 'M';
        !($ascii >= -15165 && $ascii <= -14923) ?: $result = 'N';
        !($ascii >= -14922 && $ascii <= -14915) ?: $result = 'O';
        !($ascii >= -14914 && $ascii <= -14631) ?: $result = 'P';
        !($ascii >= -14630 && $ascii <= -14150) ?: $result = 'Q';
        !($ascii >= -14149 && $ascii <= -14091) ?: $result = 'R';
        !($ascii >= -14090 && $ascii <= -13319) ?: $result = 'S';
        !($ascii >= -13318 && $ascii <= -12839) ?: $result = 'T';
        !($ascii >= -12838 && $ascii <= -12557) ?: $result = 'W';
        !($ascii >= -12556 && $ascii <= -11848) ?: $result = 'X';
        !($ascii >= -11847 && $ascii <= -11056) ?: $result = 'Y';
        !($ascii >= -11055 && $ascii <= -10247) ?: $result = 'Z';
        return $result;
    }
}
