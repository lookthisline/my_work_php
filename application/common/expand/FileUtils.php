<?php

namespace app\common\expand;

use think\File;

final class FileUtils
{
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
     */
    public static function remove_folders($path)
    {
        if (is_dir($path)) {
            $p = scandir($path);
            if (count($p) <= 2) {
                return rmdir($path);
            }
            foreach ($p as $val) {
                if (in_array($val, ['.', '..'])) {
                    continue;
                }
                is_dir($path . $val) ? self::remove_folders($path . $val . '/')  : unlink($path . $val);
            }
        }
        return rmdir($path);
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
