<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\plugin\captcha;

class session
{
    /** @var int */
    public int $max_angle = 15;

    /** @var int */
    public int $max_offset = 5;

    /** @var string */
    public string $phrase = '';

    /** @var resource */
    protected $_image;

    /** @var null */
    protected $_max_front_lines;

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function build($width, $height): static
    {
        $image = imagecreatetruecolor($width, $height);
        $bg    = imagecolorallocate($image, $this->rand(200, 255), $this->rand(200, 255), $this->rand(200, 255));
        imagefill($image, 0, 0, $bg);

        $square  = $width * $height * 3;
        $effects = $this->rand($square / 2000, $square / 1000);
        for ($e = 0; $e < $effects; $e++) {
            $this->draw_line($image, $width, $height);
        }
        $this->phrase = ""; //  $this->phrase();
        $color        = $this->write_phrase($image, $this->phrase, $this->font(), $width, $height);

        $square  = $width * $height;
        $effects = $this->rand($square / 3000, $square / 2000);
        if ($this->_max_front_lines !== 0) {
            for ($e = 0; $e < $effects; $e++) {
                $this->draw_line($image, $width, $height, $color);
            }
        }

        $image        = $this->distort($image, $width, $height, $bg);
        $this->_image = $image;
        return $this;
    }

    /**
     * @param int $quality
     */
    public function output(int $quality = 90)
    {
        header('content-type: image/png');
        imagepng($this->_image, null, $quality);
        imagedestroy($this->_image);
    }

    /**
     * @param $min
     * @param $max
     * @return int
     */
    protected function rand($min, $max): int
    {
        mt_srand((double)microtime() * 1000000);
        return mt_rand($min, $max);
    }

    /**
     * @param $image
     * @param $width
     * @param $height
     * @param null $tcol
     */
    protected function draw_line($image, $width, $height, $tcol = null)
    {
        if ($tcol === null) {
            $tcol = imagecolorallocate($image, $this->rand(100, 255), $this->rand(100, 255), $this->rand(100, 255));
        }

        if ($this->rand(0, 1)) {
            $Xa = $this->rand(0, $width / 2);
            $Ya = $this->rand(0, $height);
            $Xb = $this->rand($width / 2, $width);
            $Yb = $this->rand(0, $height);
        } else {
            $Xa = $this->rand(0, $width);
            $Ya = $this->rand(0, $height / 2);
            $Xb = $this->rand(0, $width);
            $Yb = $this->rand($height / 2, $height);
        }
        imagesetthickness($image, $this->rand(1, 3));
        imageline($image, $Xa, $Ya, $Xb, $Yb, $tcol);
    }

    protected function write_phrase($image, $phrase, $font, $width, $height)
    {
        $size       = $width / strlen($phrase) - $this->rand(0, 3) - 1;
        $box        = imagettfbbox($size, 0, $font, $phrase);
        $textWidth  = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x          = ($width - $textWidth) / 2;
        $y          = ($height - $textHeight) / 2 + $size;

        $textColor = array($this->rand(0, 150), $this->rand(0, 150), $this->rand(0, 150));
        $col       = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

        $length = strlen($phrase);
        for ($i = 0; $i < $length; $i++) {
            $box    = imagettfbbox($size, 0, $font, $phrase[$i]);
            $w      = $box[2] - $box[0];
            $angle  = $this->rand(-$this->max_angle, $this->max_angle);
            $offset = $this->rand(-$this->max_offset, $this->max_offset);
            imagettftext($image, $size, $angle, $x, $y + $offset, $col, $font, $phrase[$i]);
            $x += $w;
        }
        return $col;
    }

    public function distort($image, $width, $height, $bg)
    {
        $contents = imagecreatetruecolor($width, $height);
        $X        = $this->rand(0, $width);
        $Y        = $this->rand(0, $height);
        $phase    = $this->rand(0, 10);
        $scale    = 1.1 + $this->rand(0, 10000) / 30000;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = sqrt($Vx * $Vx + $Vy * $Vy);

                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * sin($Vn / 30);
                    $nX  = $X + ($Vx * $Vn2 / $Vn);
                    $nY  = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY = $nY + $scale * sin($phase + $nX * 0.2);

                $p = $this->interpolate(
                    $nX - floor($nX),
                    $nY - floor($nY),
                    $this->col_get($image, floor($nX), floor($nY), $bg),
                    $this->col_get($image, ceil($nX), floor($nY), $bg),
                    $this->col_get($image, floor($nX), ceil($nY), $bg),
                    $this->col_get($image, ceil($nX), ceil($nY), $bg)
                );

                if ($p == 0) {
                    $p = $bg;
                }

                imagesetpixel($contents, $x, $y, $p);
            }
        }

        return $contents;
    }

    protected function interpolate($x, $y, $nw, $ne, $sw, $se)
    {
        list($r0, $g0, $b0) = $this->rgb_get($nw);
        list($r1, $g1, $b1) = $this->rgb_get($ne);
        list($r2, $g2, $b2) = $this->rgb_get($sw);
        list($r3, $g3, $b3) = $this->rgb_get($se);

        $cx = 1.0 - $x;
        $cy = 1.0 - $y;

        $m0 = $cx * $r0 + $x * $r1;
        $m1 = $cx * $r2 + $x * $r3;
        $r  = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $g0 + $x * $g1;
        $m1 = $cx * $g2 + $x * $g3;
        $g  = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $b0 + $x * $b1;
        $m1 = $cx * $b2 + $x * $b3;
        $b  = (int)($cy * $m0 + $y * $m1);

        return ($r << 16) | ($g << 8) | $b;
    }

    /**
     * @param $col
     * @return array
     */
    protected function rgb_get($col)
    {
        return array(
            (int)($col >> 16) & 0xff,
            (int)($col >> 8) & 0xff,
            (int)($col) & 0xff,
        );
    }

    /**
     * @param $image
     * @param $x
     * @param $y
     * @param $background
     * @return false|int
     */
    protected function col_get($image, $x, $y, $background)
    {
        $L = imagesx($image);
        $H = imagesy($image);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }

        return imagecolorat($image, $x, $y);
    }

    /**
     * @return string
     */
    protected function font()
    {
        return __DIR__ . '/res/font/a.ttf';
    }
}
