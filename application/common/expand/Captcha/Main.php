<?php

namespace app\common\expand\Captcha;

use app\common\enum\Redis as RedisEnum;
use think\facade\Session;
use app\common\expand\RedisUtils;

/**
 * 重新封装的验证码类（修改tp的验证码类重新封装，去除中文验证码，修改生成图片方法）
 * 原验证码类：https://github.com/top-think/think-captcha
 */
class Main
{
    protected $config = [
        // 验证码过期时间(s)
        'expire'    => 60,
        // 验证码加密秘钥
        'secretKey' => 'MyWork',
        // 验证码字符集合
        'codeSet'   => '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        // 验证码字体大小(px)
        'fontSize'  => 25,
        // 字体文件路径
        'fontFile'  => '',
        // 字体文件存放目录
        'fontsDir'  => __DIR__ . '/fonts/',
        // 背景图文件存放目录
        'picsDir'   => __DIR__ . '/pics/',
        // 是否使用 Redis 存储验证码值
        'useRedis'  => true,
        // 是否使用背景图片
        'useImgBg'  => false,
        // 是否画混淆曲线
        'useCurve'  => true,
        // 是否添加杂点
        'useNoise'  => true,
        // 背景颜色(RGB)
        'bg'        => [243, 251, 254],
        // 验证码图片高度
        'picH'      => 0,
        // 验证码图片宽度
        'picW'      => 0,
        // 输出图片类型
        'picType'   => 'png',
        // 验证码位数
        'length'    => 5,
        // 验证成功后是否重置
        'reset'     => true
    ];

    private $im    = null; // 验证码图片实例
    private $color = null; // 验证码字体颜色

    /**
     * @access public
     * @param  Array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        if ($this->useRedis) {
            $this->config['redis_utils'] = new RedisUtils();
        }
    }

    /**
     * 验证验证码是否正确
     * @access public
     * @param String $code 用户验证码
     * @param String $id   验证码标识
     * @return bool 用户验证码是否正确
     */
    public function check(String $code, String $id = ''): Bool
    {
        $key    = $this->authCode($this->secretKey) . $id;
        $secode = $this->useRedis ? $this->redis_utils::hgetall(RedisEnum::CAPTCHA_FOLDER . $key) : Session::get($key, '');

        // 验证码为空
        if (empty($code) || empty($secode)) {
            return false;
        }

        // 验证码过期
        if (time() - $secode['verify_time'] > $this->expire) {
            $this->useRedis ? $this->redis_utils::expire($key, -2) : Session::delete($key, '');
            return false;
        }

        // 验证成功
        if ($this->authCode(strtoupper($code)) == $secode['verify_code']) {
            if ($this->useRedis) {
                // 验证之后是否重置
                $this->reset && $this->redis_utils::expire($key, -2);
            } else {
                // 验证之后是否重置
                $this->reset && Session::delete($key, '');
            }
            return true;
        }
        return false;
    }

    /**
     * 保存验证码的值
     * @access private
     * @param String $id
     * @param Array $code
     * @return Void
     */
    private function saveCode(String $id, array $code): Void
    {
        // 验证码校验key
        $key    = $this->authCode($this->secretKey);
        $secode = [
            // 验证码
            'verify_code' => $this->authCode(strtoupper(implode('', $code))),
            // 验证码创建时间
            'verify_time' => time()
        ];

        // 是否使用 redis
        if ($this->useRedis) {
            // redis hash 类型保存
            foreach ($secode as $k => $v) {
                $this->redis_utils::hset(RedisEnum::CAPTCHA_FOLDER . $key . $id, (string)$k, (string)$v);
            }
            // 刷新过期时间
            $this->redis_utils->RefreshExpireTime(RedisEnum::CAPTCHA_FOLDER . $key . $id, RedisEnum::CAPTCHA_LIFECYCLE);
        } else {
            Session::set($key . $id, $secode, '');
        }
    }

    /**
     * 图片(常用图片格式)转 Base64 字符串
     * @access private
     * @return String
     */
    private function base64PicString(): String
    {
        $base64HeadStr = '';
        // 保存输出至内部缓存区
        ob_start();
        // 输出图像
        switch ($this->picType) {
            case 'png':
                imagepng($this->im);
                $base64HeadStr = 'data:image/png;base64,';
                break;
            case 'jpg':
                imagejpeg($this->im);
                $base64HeadStr = 'data:image/jpeg;base64,';
                break;
            case 'jpeg':
                imagejpeg($this->im);
                $base64HeadStr = 'data:image/jpeg;base64,';
                break;
            case 'bmp':
                imagebmp($this->im);
                $base64HeadStr = 'data:image/bmp;base64,';
                break;
            case 'gif':
                imagegif($this->im);
                $base64HeadStr = 'data:image/gif;base64,';
                break;
            default:
                imagepng($this->im);
                $base64HeadStr = 'data:image/png;base64,';
                break;
        }
        // 获取缓存区数据保存至变量，同时清空当前输出缓存区
        $content = ob_get_clean();
        // 销毁图片
        imagedestroy($this->im);
        return $base64HeadStr . base64_encode($content);
    }

    /**
     * 生成 Base64 验证码字符串，并把验证码的值保存到 session 中
     * 验证码保存到 session 的格式为：array('verify_code' => '验证码值', 'verify_time' => '验证码创建时间')
     * @access public
     * @param String $id 要生成验证码的标识
     * @return String Base64 字符串
     */
    public function build(String $id = ''): String
    {
        // 图片宽(px)
        $this->picW || $this->picW = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
        // 图片高(px)
        $this->picH || $this->picH = $this->fontSize * 2.5;
        // 创建空白图像
        $this->im = imagecreate($this->picW, $this->picH);
        // 设置背景
        imagecolorallocate($this->im, $this->bg[0], $this->bg[1], $this->bg[2]);

        // 设置验证码字体随机颜色
        $this->color = imagecolorallocate($this->im, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));

        // 读取字体文件夹下文件信息，随机选中字体
        if (empty($this->fontFile)) {
            $dir  = dir($this->fontsDir);
            $files = [];
            while (false !== ($file = $dir->read())) {
                if ('.' != $file[0] && substr($file, -4) === '.ttf') {
                    $files[] = $file;
                }
            }
            $dir->close();
            $this->fontFile = $files[array_rand($files)];
        }

        $this->fontFile = $this->fontsDir . $this->fontFile;

        if ($this->useImgBg) {
            // 绘制背景
            $this->background();
        }
        if ($this->useNoise) {
            // 绘制杂点
            $this->writeNoise();
        }
        if ($this->useCurve) {
            // 绘制干扰线
            $this->writeCurve();
        }

        // 绘制验证码
        $code   = []; // 验证码
        $codeNX = 0; // 每个字符距离最左起点的距离，字符在图片上的 x 轴坐标
        for ($i = 0; $i < $this->length; $i++) {
            // 随机取字符
            $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)];
            $codeNX += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
            // 向图片写入字符
            imagettftext($this->im, $this->fontSize, mt_rand(-40, 40), $codeNX, $this->fontSize * 1.6, $this->color, $this->fontFile, $code[$i]);
        }

        // 保存验证码值
        $this->saveCode($id, $code);

        // Base64 字符串
        return $this->base64PicString();
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线
     * 正弦型函数解析式：y=Asin(ωx+φ)+b
     * 各常数值对函数图像的影响：
     *    A：决定峰值（即纵向拉伸压缩的倍数）
     *    b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *    φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *    ω：决定周期（最小正周期T=2π/∣ω∣）
     * @access private
     * @return Void
     */
    private function writeCurve(): void
    {
        $px = $py = 0;

        // 曲线前部分
        $A = mt_rand(1, $this->picH / 2); // 振幅
        $b = mt_rand(-$this->picH / 4, $this->picH / 4); // Y轴方向偏移量
        $f = mt_rand(-$this->picH / 4, $this->picH / 4); // X轴方向偏移量
        $T = mt_rand($this->picH, $this->picW * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($this->picW / 2, $this->picW * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->picH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    // while 循环画像素点，比 imagettftext、imagestring 这种不用 while 循环，用字体大小一次画出性能要好很多
                    imagesetpixel($this->im, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, $this->picH / 2); // 振幅
        $f   = mt_rand(-$this->picH / 4, $this->picH / 4); // X轴方向偏移量
        $T   = mt_rand($this->picH, $this->picW * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->picH / 2;
        $px1 = $px2;
        $px2 = $this->picW;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->picH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->im, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }
    }

    /**
     * 画杂点，往图片上写不同颜色的字母或数字
     * @access private
     * @return Void
     */
    private function writeNoise(): void
    {
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->im, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                // 绘制杂点
                imagestring($this->im, 5, mt_rand(-10, $this->picW), mt_rand(-10, $this->picH), $this->codeSet[mt_rand(0, (strlen($this->codeSet) - 1))], $noiseColor);
            }
        }
    }

    /**
     * 绘制背景图片
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     * @access private
     * @return Void
     */
    private function background(): void
    {
        $dir = dir($this->picsDir);
        $bgs = [];
        while (false !== ($file = $dir->read())) {
            if ('.' != $file[0] && substr($file, -4) == '.jpg') {
                $bgs[] = $this->picsDir . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        // 读取背景图片文件信息，获得宽度，高度
        list($width, $height) = @getimagesize($gb);
        // 从背景图片文件创建一个新图象
        $bgImage = @imagecreatefromjpeg($gb);
        // 将验证码图片插入背景（合并两图）
        @imagecopyresampled($this->im, $bgImage, 0, 0, 0, 0, $this->picW, $this->picH, $width, $height);
        // 销毁背景
        @imagedestroy($bgImage);
    }

    /**
     * 加密验证码
     * @access private
     * @param String $str
     * @return String
     */
    private function authCode(String $str): string
    {
        $key = substr(md5($this->secretKey), 5, 8);
        $str = substr(md5($str), 8, 10);
        return md5($key . $str);
    }

    /**
     * @access public
     * @param  String $name 配置名称
     * @return Mixed  配置值
     */
    public function __get(String $name)
    {
        return $this->config[$name];
    }

    /**
     * @access public
     * @param  String $name  配置名称
     * @param  Mixed $value 配置值
     * @return Void
     */
    public function __set(String $name, $value): void
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * @access public
     * @param  String $name 配置名称
     * @return Bool
     */
    public function __isset(String $name): Bool
    {
        return isset($this->config[$name]);
    }
}
