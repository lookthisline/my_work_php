<?php

// namespace app\common\expand;

/**
 * 图片压缩
 */
final class PictureCompress
{
    private $_source;
    private $_image;
    private $_picture_info;
    private $_percent               = 0.5;
    private $_is_local_file         = true;
    private $_require_base64_string = false;
    private static $_allow_ext      = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp', '.gif'];

    /**
     * @param string $source 源文件地址
     * @param float $percent 压缩比例
     * @param bool $is_local_file 是否是本地文件
     */
    public function __construct(string $source, float $percent = 1, bool $is_local_file = true)
    {
        $is_local_file ?: $this->_is_local_file = $is_local_file;

        $this->_source  = $this->_is_local_file ? realpath($source) : $source;
        $this->_percent = $percent;
    }

    /**
     * @param string $save_name 提供图片名（可不带扩展名，用源图扩展名）用于保存。或不提供文件名直接显示
     * @param bool $require_base64_string 是否需要base64字符串
     */
    public function action(string $save_name = '', bool $require_base64_string = false)
    {
        !$require_base64_string ?: $this->_require_base64_string = $require_base64_string;
        $this->_read();
        if ($save_name) {
            $this->_save($save_name);
        } else {
            $this->_show();
        }
    }

    private function _read()
    {
        $_picture = getimagesize($this->_source);
        list($_width, $_height, $_type) = array_slice($_picture, 0, 3);
        $this->_picture_info = [
            'width'  => $_width,
            'height' => $_height,
            'type'   => image_type_to_extension($_type, false),
            'other'  => array_slice($_picture, 3),
            'source' => $_picture
        ];
        // NOTE: imagecreatefrom bmp|gd2|gd2part|gd|gif|jpeg|png|string|wbmp|webp|xbm|xpm
        $_func = "imagecreatefrom" . $this->_picture_info['type'];
        // 从源文件地址或链接新建一个图像
        $this->_image = $_func($this->_source);
        $this->_compress();
    }

    private function _compress()
    {
        $width  = $this->_picture_info['width'] * $this->_percent;
        $height = $this->_picture_info['height'] * $this->_percent;
        // 新建一个真彩色图像
        $image_thump = imagecreatetruecolor($width, $height);
        // 重采样拷贝部分图像并调整大小
        imagecopyresampled($image_thump, $this->_image, 0, 0, 0, 0, $width, $height, $this->_picture_info['width'], $this->_picture_info['height']);
        // 销毁图像
        imagedestroy($this->_image);
        $this->_image = $image_thump;
        imagedestroy($image_thump);
    }

    /**
     * 输出图片:保存图片则用saveImage()
     */
    private function _show()
    {
        $this->_require_base64_string && $this->outputBase64String();

        $_func = "image" . $this->_picture_info['type'];
        header('Content-Type:' . $this->_picture_info['other']['mime']);
        // 将 GD 图像输出到浏览器或文件
        $_func($this->_image);
    }

    /**
     * 保存图片到硬盘
     * 1、可指定字符串不带后缀的名称，使用源图扩展名
     * 2、直接指定目标图片名带扩展名
     * @param string $save_name
     */
    private function _save(string $save_name)
    {
        $this->_require_base64_string && $this->outputBase64String();
        if (!$save_name) {
            return false;
        }
        $_ext        = isset(pathinfo($save_name)['extension']) ? strtolower(pathinfo($save_name)['extension']) : '';
        $_source_ext = isset(pathinfo($this->_source)['extension']) ? strtolower(pathinfo($this->_source)['extension']) : '';
        //有指定目标名扩展名
        if ($_ext && in_array($_ext, self::$_allow_ext)) {
            // continue
        } elseif ($_source_ext && in_array($_source_ext, self::$_allow_ext)) {
            $save_name = $save_name . '.' . $_source_ext;
        } else {
            $save_name = $save_name . '.' . $this->_picture_info['type'];
        }
        $_func = "image" . $this->_picture_info['type'];
        $_func($this->_image, $save_name);
    }

    public function __destruct()
    {
        $this->_image && imagedestroy($this->_image);
    }

    private function outputBase64String(): void
    {
        $_func = "image" . $this->_picture_info['type'];
        ob_start();
        $_func($this->_image);
        $_stream = ob_get_clean();
        echo 'data:' . $this->_picture_info['other']['mime'] . ';base64,' . base64_encode($_stream);
        $this->_image && imagedestroy($this->_image);
        exit(0);
    }
}
$url   = 'http://img.netbian.com/file/2020/0904/dbb00a5646309df5fad6efda1079e756.jpg';
$url_1 = 'https://yaqu-renzheng.oss-cn-beijing.aliyuncs.com/IMG_CROP_20201205_16570151.jpeg';
$pic   = new PictureCompress($url_1, 80, false);
$pic->action('./'.date("YmdHis"), false);
