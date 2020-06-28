<?php


namespace ounun\image;


/**
 * 这里说的imagick 是 ImageMagick 在PHP下的扩展。使用pecl安装起来那叫一个轻松简单 —— 一条命令就搞定：
 * sudo pecl install imagick
 * （扩展装好后还是要在php.ini中加上extension=imagick.so，然后记得重启apache或php-fpm服务。）
 * 最近有个需求是要把多张图片组合起来生成缩略图，刚好用用这个强大的imagick扩展。
 * 这个需求是要这样生成缩略图：
 *      如果有1张图片，就直接生成这张图片的缩略图；
 *      如果有2张图片，则一张在左边一张在右边，各一半；
 *      如果有3张图片，则两张左边平均分配，一张独占右边；
 *      如果有4张图片，则像田字格一样平均分配空间；
 * 更多张图片，则只取前4张，按田字格方式生成缩略图。
 *
 * $thumbnail = \clarence\thumbnail\Thumbnail::createFromImages($srcImages, 240, 320);
 * $thumbnail->writeImage($outputDir."/example.jpg");
 *
 * Class multi_thumb
 * @package ounun\image
 */
class thumb_multi extends \Imagick
{
    /**
     * @param array $images
     * @param int $width
     * @param int $height
     * @param string $background
     * @param string $format
     * @return self
     * @throws \Exception
     */
    public static function create($images, $width, $height, $background = 'white', $format = 'jpg')
    {
        if (empty($images)) {
            throw new \Exception("No images!");
        }

        $thumbnail = new static();
        $thumbnail->newImage($width, $height, $background, $format);
        $thumbnail->composite($images);

        return $thumbnail;
    }

    /**
     * @param $images
     * @throws \ImagickException
     */
    public function composite($images)
    {
        $images_keys      = array_keys($images);
        $composite_config = $this->calc_composite_images_pos_and_size($images);

        foreach ($composite_config as $index => $cfg) {
            $imgKey = $images_keys[$index];
            $img    = new \Imagick($images[$imgKey]);
            $img    = $this->composite_thumb($img, $cfg);
            $this->compositeImage($img, self::COMPOSITE_OVER, $cfg['to']['x'], $cfg['to']['y']);
        }
    }

    /**
     * @param \Imagick $img
     * @param $cfg
     * @return \Imagick
     * @throws \ImagickException
     */
    protected function composite_thumb(\Imagick $img, $cfg)
    {
        $img->cropThumbnailImage($cfg['size']['width'], $cfg['size']['height']);
        return $img;
    }

    /**
     * @param $images
     * @return array
     * @throws \ImagickException
     */
    protected function calc_composite_images_pos_and_size($images)
    {
        $width  = $this->getImageWidth();
        $height = $this->getImageHeight();

        switch (count($images)) {
            case 0:
                throw new \ImagickException("No images!");
            case 1:
                // | 0 |
                return [
                    0 => [
                        'to'   => ['x' => 0, 'y' => 0],
                        'size' => ['width' => $width, 'height' => $height,]
                    ]
                ];
            case 2:
                // | 0 | 1 |
                return [
                    0 => [
                        'to'   => ['x' => 0, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height,]
                    ],
                    1 => [
                        'to'   => ['x' => $width / 2, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height,]
                    ]
                ];
            case 3:
                // | 0 | 1 | 2 |
                return [
                    0 => [
                        'to'   => ['x' => 0, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                    1 => [
                        'to'   => ['x' => $width / 2, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height,]
                    ],
                    2 => [
                        'to'   => ['x' => 0, 'y' => $height / 2],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                ];
            default:   // >= 4:
                // | 0 | 1 |
                // | 2 | 3 |
                return [
                    0 => [
                        'to'   => ['x' => 0, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                    1 => [
                        'to'   => ['x' => $width / 2, 'y' => 0],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                    2 => [
                        'to'   => ['x' => 0, 'y' => $height / 2],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                    3 => [
                        'to'   => ['x' => $width / 2, 'y' => $height / 2],
                        'size' => ['width' => $width / 2, 'height' => $height / 2,]
                    ],
                ];
        }
    }
}
