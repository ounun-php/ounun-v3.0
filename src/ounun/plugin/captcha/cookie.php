<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\plugin\captcha;

/**
 * 认证码类
 * @package module
 */
class cookie
{
    /**
     * 输出图片
     *
     * @param string $cookie
     * @param int $img_width
     * @param int $img_height
     * @param int $img_length
     */
    public static function output(string $cookie = 'captcha',int $img_width = 75,int $img_height = 24,int $img_length = 4)
    {
        $base = new base();
        $base->make($img_width, $img_height, $img_length);
        setcookie($cookie, md5($base->code), time() + 3600, '/');
        // setcookie($cookie,md5($base->code),time()+3600);
        $base->output();
    }

    /**
     * 确认认证码
     *
     * @param string $code
     * @param string $cookie
     * @return boolean
     */
    public static function check(string $code, string $cookie = 'captcha'): bool
    {
        $rs = $code && $_COOKIE[$cookie] && md5($code) == $_COOKIE[$cookie];
        setcookie($cookie, '', -3600);
        return $rs;
    }
}
