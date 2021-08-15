<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\image;


class imagick
{
    /** @var \Imagick|null */
    protected ?\Imagick $_image;
    protected string $_format;

    /** @var string magick所在目录 */
    protected string $magick_path = '';

    public $debug = false;
    public $filepath;
    public $saveas;
    public $log = [];
    public $error = '';
    public $imageQuality;

    function __construct($filepath = '')
    {
        $this->filepath     = $filepath;
        $this->saveas       = $filepath;
        $this->imageQuality = 85;
    }

    // 析构函数
    public function __destruct()
    {
        if ($this->_image !== null) {
            $this->_image->destroy();
        }
    }

    /**
     * 载入图像
     * @param $path
     * @return \Imagick
     * @throws \ImagickException
     */
    public function open($path)
    {
        $this->_image = new \Imagick($path);
        if ($this->_image) {
            $this->_format = strtolower($this->_image->getImageFormat());
        }
        return $this->_image;
    }

    /**
     * @param int $x
     * @param int $y
     * @param null $width
     * @param null $height
     * @throws \ImagickException
     */
    public function crop($x = 0, $y = 0, $width = null, $height = null)
    {
        if ($width == null) $width = $this->_image->getImageWidth() - $x;
        if ($height == null) $height = $this->_image->getImageHeight() - $y;
        if ($width <= 0 || $height <= 0) return;
        if ($this->_format == 'gif') {
            $image  = $this->_image;
            $canvas = new Imagick();
            $images = $image->coalesceImages();
            foreach ($images as $frame) {
                $img = new \Imagick();
                $img->readImageBlob($frame);
                $img->cropImage($width, $height, $x, $y);
                $canvas->addImage($img);
                $canvas->setImageDelay($img->getImageDelay());
                $canvas->setImagePage($width, $height, 0, 0);
            }
            $image->destroy();
            $this->_image = $canvas;
        } else {
            $this->_image->cropImage($width, $height, $x, $y);
        }
    }

    /*
    * 更改图像大小
    $fit: 适应大小方式
    'force': 把图片强制变形成 $width X $height 大小
    'scale': 按比例在安全框 $width X $height 内缩放图片, 输出缩放后图像大小 不完全等于 $width X $height
    'scale_fill': 按比例在安全框 $width X $height 内缩放图片，安全框内没有像素的地方填充色, 使用此参数时可设置背景填充色 $bg_color = array(255,255,255)(红,绿,蓝, 透明度) 透明度(0不透明-127完全透明))
    其它: 智能模能 缩放图像并载取图像的中间部分 $width X $height 像素大小
    $fit = 'force','scale','scale_fill' 时： 输出完整图像
    $fit = 图像方位值 时, 输出指定位置部分图像
    字母与图像的对应关系如下:
    north_west north north_east
    west center east
    south_west south south_east
    */
    public function resize_to($width = 100, $height = 100, $fit = 'center', $fill_color = array(255, 255, 255, 0))
    {
        switch ($fit) {
            case 'force':
                if ($this->_format == 'gif') {
                    $image  = $this->_image;
                    $canvas = new \Imagick();
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $img = new Imagick();
                        $img->readImageBlob($frame);
                        $img->thumbnailImage($width, $height, false);
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                    }
                    $image->destroy();
                    $this->_image = $canvas;
                } else {
                    $this->_image->thumbnailImage($width, $height, false);
                }
                break;
            case 'scale':
                if ($this->_format == 'gif') {
                    $image  = $this->_image;
                    $images = $image->coalesceImages();
                    $canvas = new Imagick();
                    foreach ($images as $frame) {
                        $img = new Imagick();
                        $img->resize_to();
                        $img->readImageBlob($frame);
                        $img->thumbnailImage($width, $height, true);
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                    }
                    $image->destroy();
                    $this->_image = $canvas;
                } else {
                    $this->_image->thumbnailImage($width, $height, true);
                }
                break;
            case 'scale_fill':
                $size       = $this->_image->getImagePage();
                $src_width  = $size['width'];
                $src_height = $size['height'];
                $x          = 0;
                $y          = 0;
                $dst_width  = $width;
                $dst_height = $height;
                if ($src_width * $height > $src_height * $width) {
                    $dst_height = intval($width * $src_height / $src_width);
                    $y          = intval(($height - $dst_height) / 2);
                } else {
                    $dst_width = intval($height * $src_width / $src_height);
                    $x         = intval(($width - $dst_width) / 2);
                }
                $image  = $this->_image;
                $canvas = new Imagick();
                $color  = 'rgba(' . $fill_color[0] . ',' . $fill_color[1] . ',' . $fill_color[2] . ',' . $fill_color[3] . ')';
                if ($this->_format == 'gif') {
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $frame->thumbnailImage($width, $height, true);
                        $draw = new \ImagickDraw();
                        $draw->composite($frame->getImageCompose(), $x, $y, $dst_width, $dst_height, $frame);
                        $img = new Imagick();
                        $img->newImage($width, $height, $color, 'gif');
                        $img->drawImage($draw);
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
                } else {
                    $image->thumbnailImage($width, $height, true);
                    $draw = new \ImagickDraw();
                    $draw->composite($image->getImageCompose(), $x, $y, $dst_width, $dst_height, $image);
                    $canvas->newImage($width, $height, $color, $this->type_get());
                    $canvas->drawImage($draw);
                    $canvas->setImagePage($width, $height, 0, 0);
                }
                $image->destroy();
                $this->_image = $canvas;
                break;
            default:
                $size       = $this->_image->getImagePage();
                $src_width  = $size['width'];
                $src_height = $size['height'];
                $crop_x     = 0;
                $crop_y     = 0;
                $crop_w     = $src_width;
                $crop_h     = $src_height;
                if ($src_width * $height > $src_height * $width) {
                    $crop_w = intval($src_height * $width / $height);
                } else {
                    $crop_h = intval($src_width * $height / $width);
                }
                switch ($fit) {
                    case 'north_west':
                        $crop_x = 0;
                        $crop_y = 0;
                        break;
                    case 'north':
                        $crop_x = intval(($src_width - $crop_w) / 2);
                        $crop_y = 0;
                        break;
                    case 'north_east':
                        $crop_x = $src_width - $crop_w;
                        $crop_y = 0;
                        break;
                    case 'west':
                        $crop_x = 0;
                        $crop_y = intval(($src_height - $crop_h) / 2);
                        break;
                    case 'center':
                        $crop_x = intval(($src_width - $crop_w) / 2);
                        $crop_y = intval(($src_height - $crop_h) / 2);
                        break;
                    case 'east':
                        $crop_x = $src_width - $crop_w;
                        $crop_y = intval(($src_height - $crop_h) / 2);
                        break;
                    case 'south_west':
                        $crop_x = 0;
                        $crop_y = $src_height - $crop_h;
                        break;
                    case 'south':
                        $crop_x = intval(($src_width - $crop_w) / 2);
                        $crop_y = $src_height - $crop_h;
                        break;
                    case 'south_east':
                        $crop_x = $src_width - $crop_w;
                        $crop_y = $src_height - $crop_h;
                        break;
                    default:
                        $crop_x = intval(($src_width - $crop_w) / 2);
                        $crop_y = intval(($src_height - $crop_h) / 2);
                }
                $image  = $this->_image;
                $canvas = new Imagick();
                if ($this->_format == 'gif') {
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $img = new Imagick();
                        $img->readImageBlob($frame);
                        $img->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
                        $img->thumbnailImage($width, $height, true);
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
                } else {
                    $image->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
                    $image->thumbnailImage($width, $height, true);
                    $canvas->addImage($image);
                    $canvas->setImagePage($width, $height, 0, 0);
                }
                $image->destroy();
                $this->_image = $canvas;
        }
    }

    // 添加水印图片
    public function add_watermark($path, $x = 0, $y = 0)
    {
        $watermark = new Imagick($path);
        $draw      = new \ImagickDraw();
        $draw->composite($watermark->getImageCompose(), $x, $y, $watermark->getImageWidth(), $watermark->getimageheight(), $watermark);
        if ($this->_format == 'gif') {
            $image  = $this->_image;
            $canvas = new Imagick();
            // $images = $image->coalesceImages();
            $image->coalesceImages();
            foreach ($image as $frame) {
                $img = new Imagick();
                $img->readImageBlob($frame);
                $img->drawImage($draw);
                $canvas->addImage($img);
                $canvas->setImageDelay($img->getImageDelay());
            }
            $image->destroy();
            $this->_image = $canvas;
        } else {
            $this->_image->drawImage($draw);
        }
    }

    // 添加水印文字
    public function add_text($text, $x = 0, $y = 0, $angle = 0, $style = [])
    {
        $draw = new \ImagickDraw();
        if (isset($style['font'])) $draw->setFont($style['font']);
        if (isset($style['font_size'])) $draw->setFontSize($style['font_size']);
        if (isset($style['fill_color'])) $draw->setFillColor($style['fill_color']);
        if (isset($style['under_color'])) $draw->setTextUnderColor($style['under_color']);
        if ($this->_format == 'gif') {
            foreach ($this->_image as $frame) {
                $frame->annotateImage($draw, $x, $y, $angle, $text);
            }
        } else {
            $this->_image->annotateImage($draw, $x, $y, $angle, $text);
        }
    }

    // 保存到指定路径
    public function save_to($path)
    {
        if ($this->_format == 'gif') {
            $this->_image->writeImages($path, true);
        } else {
            $this->_image->writeImage($path);
        }
    }

    // 输出图像
    public function output($header = true)
    {
        if ($header) header('Content-type: ' . $this->_format);
        echo $this->_image->getImagesBlob();
    }

    public function width_get()
    {
        $size = $this->_image->getImagePage();
        return $size['width'];
    }

    public function height_get()
    {
        $size = $this->_image->getImagePage();
        return $size['height'];
    }

    // 设置图像类型， 默认与源类型一致
    public function type_set($type = 'png')
    {
        $this->_format = $type;
        $this->_image->setimageformat($type);
    }

    // 获取源图像类型
    public function type_get()
    {
        return $this->_format;
    }

    // 当前对象是否为图片
    public function is_image()
    {
        if ($this->_image)
            return true;
        else
            return false;
    }

    public function thumbnail($width = 100, $height = 100, $fit = true)
    {
        $this->_image->thumbnailImage($width, $height, $fit);
    } // 生成缩略图 $fit为真时将保持比例并在安全框 $width X $height 内生成缩略图片


    /**
     * 添加一个边框
     * @param int $width 左右边框宽度
     * @param int $height 上下边框宽度
     * @param string $color 颜色: RGB 颜色 'rgb(255,0,0)' 或 16进制颜色 '#FF0000' 或颜色单词 'white'/'red'...
     */
    public function border($width, $height, $color = 'rgb(220, 220, 220)')
    {
        $pix = new \ImagickPixel();
        $pix->setColor($color);
        $this->_image->borderImage($pix, $width, $height);
    }

    public function blur($radius, $sigma)
    {
        $this->_image->blurImage($radius, $sigma);
    } // 模糊

    public function gaussian_blur($radius, $sigma)
    {
        $this->_image->gaussianBlurImage($radius, $sigma);
    } // 高斯模糊

    public function motion_blur($radius, $sigma, $angle)
    {
        $this->_image->motionBlurImage($radius, $sigma, $angle);
    } // 运动模糊

    public function radial_blur($radius)
    {
        $this->_image->radialBlurImage($radius);
    } // 径向模糊

    public function add_noise($type = null)
    {
        $this->_image->addNoiseImage($type == null ? imagick::NOISE_IMPULSE : $type);
    } // 添加噪点

    public function level($black_point, $gamma, $white_point)
    {
        $this->_image->levelImage($black_point, $gamma, $white_point);
    } // 调整色阶

    public function modulate($brightness, $saturation, $hue)
    {
        $this->_image->modulateImage($brightness, $saturation, $hue);
    } // 调整亮度、饱和度、色调

    public function charcoal($radius, $sigma)
    {
        $this->_image->charcoalImage($radius, $sigma);
    } // 素描

    public function oil_paint($radius)
    {
        $this->_image->oilPaintImage($radius);
    } // 油画效果

    public function flop()
    {
        $this->_image->flopImage();
    } // 水平翻转

    public function flip()
    {
        $this->_image->flipImage();
    } // 垂直翻转


    function setSource($path)
    {
        $this->filepath = $path;
        return $this;
    }

    function setDestination($path)
    {
        $this->saveas = $path;
        return $this;
    }

    function setImageQuality($value)
    {
        $this->imageQuality = intval($value);
        return $this;
    }

    function blur2($r = 5)
    {
        $filename = $this->filepath;
        $filename = escapeshellcmd($filename);
        $cmd      = 'convert "' . $filename . '" -channel RGBA -blur 0x' . $r . ' "' . $filename . '"';
        $this->execute($cmd);
        return $this;
    }

    function stroke_outline()
    {
        $filename = $this->filepath;
        $filename = '"' . escapeshellcmd($filename) . '"';
        $cmd      = 'convert -background none -stroke black ' . $filename
            . ' ( +clone   -background navy   -shadow 80x3+3+3 ) +swap '
            . ' -background none -layers merge +repage  ' . $filename;
        $this->execute($cmd);
        return $this;
    }

    public function execute($cmd)
    {
        $ret = null;
        $out = [];
        echo $cmd . '<br />';
        chdir($this->magick_path);
        //exec($cmd .' 2>&1', $out, $ret);

        passthru($cmd . ' 2>&1', $ret);

        if ($ret != 0) {
            $err = 'Error executing "' . $cmd . '" <br>';
            $err .= 'return code: ' . $ret . ' <br>command output :"' . implode("<br>", $out) . '"';
            if ($this->debug)
                echo $err;
            else
                $this->error[] = $err;
        }

        $this->log[] = [
            'cmd'      => $cmd
            , 'return' => $ret
            , 'output' => $out
        ];


        return $ret;
    }
}
