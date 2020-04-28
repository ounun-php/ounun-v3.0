<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
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
     * @param int $img_lenght
     */
    public static function output($cookie = 'captcha', $img_width = 75, $img_height = 24, $img_lenght = 4)
    {
        $base = new base();
        $base->make($img_width, $img_height, $img_lenght);
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
    public static function check($code, $cookie = 'captcha')
    {
        $rs = ($code && $_COOKIE[$cookie] && md5($code) == $_COOKIE[$cookie]) ? true : false;
        setcookie($cookie, '', -3600);
        return $rs;
    }
}
