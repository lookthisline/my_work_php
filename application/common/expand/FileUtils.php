<?php

namespace app\common\expand;

use think\File;
use app\common\enum\Redis;

final class FileUtils
{
    private static $block_size  = 4096;              // byte
    private static $max_size    = 5 * 1024 * 1024;   // mb
    private static $hash_mode   = 'sha256';          // 获取文件的hash模式
    private static $expire_time = 7 * 24 * 60 * 60;  // 文件过期时间

    /**
     * 上传文件（移动文件，返回文件存储地址）
     * @param FILE $file
     * @return String|Boolean
     */
    public function upload(File $file)
    {
        $save_dir    = (string)config('upload.upload_path') . DIRECTORY_SEPARATOR . date('YMd');
        $move_result = $file->move($save_dir);
        if ($move_result) {
            return json_encode([
                'dir'  => $save_dir,
                'file' => $move_result->getSaveName()
            ], JSON_UNESCAPED_UNICODE);
        }
        return false;
    }

    /**
     * 删除文件（取得文件路径，删除）
     * @param String $file_path
     * @return Boolean
     */
    public static function delete(string $file_path): bool
    {
        if (!$file_path) {
            return true;
        }
        // 去除地址开头目录符，防止定位到系统根目录
        $file_path = ltrim($file_path, DIRECTORY_SEPARATOR);
        try {
            file_exists($file_path) && unlink($file_path);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * 删除文件夹
     * @param string $path
     * @param integer $level default null
     * @return boolean
     */
    public static function remove_folders(string $path, int &$_level = null): bool
    {
        $_path = realpath(rtrim($path, DIRECTORY_SEPARATOR)); // 获取真实地址
        if (is_dir($_path)) {
            $_path_list = scandir($_path); // 获取路径下文件夹（文件）列表
            if (count($_path_list) <= 2) {
                // 该路径列表仅包含当前路径（.）与上级路径（..）
                return rmdir($_path);
            }
            foreach ($_path_list as $_val) {
                if (in_array($_val, ['.', '..'])) {
                    // 当前路劲仅包含当前路径（.）与上级路径（..）
                    continue;
                }
                // 目标路径
                $_this_path = $_path . DIRECTORY_SEPARATOR . $_val;
                if (is_dir($_this_path)) {
                    $_level++;
                    self::remove_folders($_this_path, $_level); // 继续遍历下级路劲
                } else {
                    unlink($_this_path); // 删除文件
                }
            }
        } elseif (!is_dir($_path) && !$_level) {
            return false; // 输入参数不是合法路径参数
        }
        return rmdir($_path); // 删除指定路径
    }

    /**
     * 作为热文件加速时使用，大文件需修改文件块大小，或是多种服务端间数据互通的方式
     * @param string $path
     * @param string $sum
     */
    public function blob(string $path = '', string $sum = '')
    {
        if (!$path && !$sum) {
            return false;
        }
        $file_stream = null;
        if ($path && !$sum) {
            // ini_set('memory_limit', '1G'); // test
            if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
                // 判断是否是本地文件
                return false;
            }
            $path = realpath($path); // 获取文件在服务器的绝对路径
            if (!file_exists($path) && is_readable($path)) {
                // 判断文件是否存在，是否可读
                return false;
            }
            $file_size = filesize($path);
            if ($file_size > self::$max_size) {
                // 判断文件大小，过大不读
                return false;
            }
            // 读取文件流
            $file_stream = file_get_contents($path);
            $sum         = hash(self::$hash_mode, $file_stream);
        }
        $i           = 0;
        $redis_key   = Redis::DOCUMENT_FOLDER . $sum;
        $redis_utils = UtilsFactory::redis();
        $redis_len   = $redis_utils->llen($redis_key);
        // 通过比较在指定块大小时文件块数量判断重复性，判断错误几率较小
        while ($redis_len && ceil($file_size / self::$block_size) !== $redis_len) {
            // BUG: 为hash值增加序号（数据表设置文件hash值字段应设置冗余长度）
            $redis_len = $redis_utils->llen($redis_key . "_" . ++$i);
        }
        !$i ?: $redis_key .= "_" . $i;
        if ($redis_len) {
            // 从redis尝试读取信息
            $file_stream = $redis_utils->lrange($redis_key, 0, -1);
            $redis_utils->RefreshExpireTime($redis_key, self::$expire_time);
            if ($file_stream && is_array($file_stream)) {
                $file_stream = implode('', $file_stream);
            }
        } else {
            // redis不存在该文件
            $file_stream = file_get_contents($path);
            // NOTE: 存放二进制字符串切片的大型数组（过大的数据存入一个变量可能导致内存溢出，小文件时执行应该快）
            // $package = str_split($file_stream, self::$block_size);
            // // 存入redis
            // $redis_utils->pipeline()
            //     ->rpush($redis_key, ...$package)
            //     ->expire($redis_key, self::$expire_time)
            //     ->exec();
            // NOTE: 不使用数组，只循环写入（应该能避免大数组内存溢出，大文件时执行比数组快）
            $i        = 0;
            $pipeline = $redis_utils->pipeline();
            while ($file_size >= $i) {
                $pipeline->rpush($redis_key, substr($file_stream, $i, self::$block_size));
                $i += self::$block_size;
            }
            $pipeline->expire($redis_key, self::$expire_time)
                ->exec();
        }
        // header('Content-type:image/png');
        // Header("Content-type: application/octet-stream");
        // Header("Accept-Ranges: bytes");
        // Header("Accept-Length: " . $file_size);
        // 输出文件流
        return $file_stream;
        // exit(0);
    }

    /**
     * 下载文件
     */
    public static function download()
    {
        $_source = input('url/s', '', 'trim') ?: input('post.url/s', '', 'trim');
        if (!$_source) {
            exit(0);
        }
        $_is_local_file = true;
        // 判断是否是本地文件
        if (filter_var($_source, FILTER_VALIDATE_URL) !== false) {
            $_is_local_file = false;
        }
        $_file_info = pathinfo($_source);
        $_stream    = @fopen($_source, "rb");
        if (!$_stream || !key_exists('basename', $_file_info) || !key_exists('extension', $_file_info)) {
            exit(0);
        }
        $_content   = $_is_local_file ? $_stream : stream_get_contents($_stream);
        $_size      = $_is_local_file ? filesize($_source) : strlen($_content);
        $_ext       = '.' . strtolower($_file_info['extension']);
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $_size);
        Header("Content-Disposition: attachment; filename=" . date('YmdHis') . $_ext);
        echo $_is_local_file ? fread($_content, $_size) : $_content;
        fclose($_stream);
        exit(0);
    }
}
