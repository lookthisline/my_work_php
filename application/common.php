<?php

/**
 * base64_url 编码
 * @param String $input
 * @return String
 */
function base64UrlEncode(String $input): String
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

/**
 * base64_url 解码
 * @param String $input
 * @return String
 */
function base64UrlDecode(String $input): String
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $add_len = 4 - $remainder;
        $input   .= str_repeat('=', $add_len);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

/**
 * 去除返回数据的 key 中的括号
 * @param Array $data
 * @return Void
 */
function tripTag(array &$data): void
{
    foreach ($data as $key => $value) {
        // 提取键名
        $new_key = str_replace(['(', '.', ')'], '_', $key);
        // 替换旧键名
        if ($new_key != $key) {
            unset($data[$key]);
            $data[$new_key] = $value;
        }
        // 处理嵌套数组
        if (is_array($value)) {
            tripTag($data[$key]);
        }
    }
}

/**
 * 响应请求
 * @param Mixed $data
 * @param String $message
 * @param Boolean $status 当前响应为错误类型还是成功类型
 * @param Integer $http_code Http 响应状态码
 * @param Array $header
 */
function clientResponse($data = [], string $message = 'success', bool $status = true, int $http_code = 200, array $header = [])
{
    if (is_array($data) && !empty($data)) {
        tripTag($data);
    }

    $def_header = [
        'X-Powered-By'                     => config('app.app_name'),
        // 为 Vue 项目设置 cookie 跨域，将 cookie 带至指定域
        'Access-Control-Allow-Origin'      => request()->header('origin'),
        // 跨域时允许 cookie 添加到请求中(允许 cookie 跨域)
        'Access-Control-Allow-Credentials' => 'true',
        // 设置客户端访问的资源允许使用的方法
        'Access-Control-Allow-Methods'     => 'GET,POST,PATCH,PUT,HEAD,OPTIONS,DELETE',
        'Access-Control-Allow-Headers'     => '*,Authorization',
        // 'Access-Control-Allow-Headers'     => 'origin,x-requested-with,content-type,accept,Authorization',
        // options 预检信息(Access-Control-Allow-Methods 和 Access-Control-Allow-Headers)的缓存时长 (s)
        'Access-Control-Max-Age'           => 60 * 60 * 1,
        // frame 标签设置
        'X-Frame-Options'                  => 'SAMEORIGIN',
        // xss 攻击防护设置
        'X-XSS-Protection'                 => '1;mode=block',
        // 设置在浏览器收到这个响应后的 365 * 24 * 60 * 60 秒的时间内凡是访问这个域名下的请求都使用HTTPS请求；includeSubDomains 适用于该网站的所有子域名；允许预载
        'Strict-Transport-Security'        => "max-age=" . (string)(365 * 24 * 60 * 60) . ";includeSubDomains;preload"
    ];

    $ret = [
        'status'  => $status,
        'data'    => is_null($data) ? [] : $data,
        'message' => $message,
        'cmd'     => request()->path()
    ];

    return json()->data($ret)->code($http_code)->header(array_merge($def_header, $header))->send();
    // return new HttpResponseException(json()->data($ret)->code($http_code)->header(array_merge($def_header, $header)));
}

/**
 * 简单无限极分类获取分类数
 */
 function getTree($array, $pid = 0, $level = 0)
 {
     //声明静态数组,避免递归调用时,多次声明导致数组覆盖
     static $list = [];
     foreach ($array as $key => $value) {
         //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
         if ($value['pid'] == $pid) {
             //父节点为根节点的节点,级别为0，也就是第一级
             $value['level'] = $level;
             //把数组放到list中
             $list[] = $value;
             //把这个节点从数组中移除,减少后续递归消耗
             unset($array[$key]);
             //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
             getTree($array, $value['id'], $level + 1);
         }
     }
     return $list;
 }
