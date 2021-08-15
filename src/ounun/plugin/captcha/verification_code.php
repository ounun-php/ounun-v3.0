<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\plugin\captcha;

class verification_code
{
    const Key = '_captcha_verification_code_';
    
    public string $const = 'bcdfhjkmnpqrstwxyz';
    public string $vowels = 'aei23456789';
    public int $height = 24;
    public int $length = 4;
    public int $angle = 10;        //倾斜度
    public int $contort = 2;       //扭曲度
    public string $fonts;

    public function __construct()
    {
        $session = new session();
        $session->start();
        $this->fonts = __DIR__ . '/res/fonts/a.ttf';
    }

    public function image()
    {
        $string = $this->_string();
        $this->_image($string);
    }

    public function valid($destroy = false): bool
    {
        $result = isset($_SESSION[static::Key]) && $_REQUEST[static::Key] === $_SESSION[static::Key];
        if ($destroy) unset($_SESSION[static::Key]);
        return $result;
    }

    public function _string(): string
    {
        $const_len = strlen($this->const) - 1;
        $vowels_len = strlen($this->vowels) - 1;
        $string    = '';
        for ($x = 0; $x < $this->length; $x++) {
            $string .= $x % 2 == 0 ? substr($this->const, mt_rand(0, $const_len), 1) : substr($this->vowels, mt_rand(0, $vowels_len), 1);
        }
        $_SESSION[static::Key] = $string;
        return $_SESSION[static::Key];
    }

    public function _image($string)
    {
        ob_clean();
        $imageX = strlen($string) * 13;    //the image width
        $imageY = $this->height;                        //the image height
        $im     = imagecreatetruecolor($imageX, $imageY);

        // 背景
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));

        // 角度旋转写入
        $fontColor = imagecolorallocate($im, 0, 0, 192);
        for ($i = 0; $i < strlen($string); $i++) {
            $angle    = mt_rand(-$this->angle, $this->angle);    //角度随机
            $fontsize = mt_rand(12, 16);    //字体大小随机
            imagefttext($im, $fontsize, $angle, 2 + $i * 11, 18, $fontColor, $this->fonts, $string[$i]);
        }

        // 扭曲
        $distorted = imagecreatetruecolor($imageX, $imageY);
        imagefill($distorted, 0, 0, imagecolorallocate($distorted, 255, 255, 255));

        $this->contort = mt_rand(1, $this->contort);
        $func          = ['sin', 'cos'];
        $func          = $func[mt_rand(0, 1)];
        for ($j = 0; $j < $imageY; $j++) {
            $amend = round($func($j / $imageY * 2 * M_PI - M_PI * 0.5) * $this->contort);
            for ($i = 0; $i < $imageX; $i++) {
                $rgb = imagecolorat($im, $i, $j);
                imagesetpixel($distorted, $i + $amend, $j, $rgb);
            }
        }

        // 边框
        $border = imagecolorallocate($distorted, 133, 153, 193);
        imagerectangle($distorted, 0, 0, $imageX - 1, $imageY - 1, $border);

        header("content-type:image/png\r\n");
        imagepng($distorted);
        imagedestroy($im);
        imagedestroy($distorted);
    }
}
