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

    public static function remove_folders($path)
    {
        if (is_dir($path)) {
            $p = scandir($path);
            if (count($p) > 2) {
                foreach ($p as $val) {
                    if ($val != "." && $val != "..") {
                        if (is_dir($path . $val)) {
                            self::remove_folders($path . $val . '/');
                        } else {
                            unlink($path . $val);
                        }
                    }
                }
            }
        }
        return rmdir($path);
    }
}
