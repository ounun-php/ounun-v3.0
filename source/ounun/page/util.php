<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\page;

class util
{
    /**
     * @param array $paras_gets
     * @param array $paras_page
     * @param string $url_original
     * @return string
     */
    static public function url(array $paras_gets = [], array $paras_page = [], string $url_original = '',string $url_key = 'p')
    {
        $paras_gets   = $paras_gets ? $paras_gets : $_GET;
        $paras_page   = $paras_page ? $paras_page : ['page' => '{page}'];
        $url_original = $url_original ? $url_original : url_original();
        $url          = url_build_query($url_original, $paras_gets, $paras_page);
        // print_r(['$url_original'=>$url_original,'$url'=>$url,'$paras_gets'=>$paras_gets,'$paras_page'=>$paras_page]);
        // exit();
        self::page_set($_SERVER['REQUEST_URI'],$url_key);
        return $url;
    }

    /**
     * 设定当前页面
     * @param string $url
     * @param string $url_key
     */
    static public function page_set(string $url, string $url_key = 'p')
    {
        self::value_set($url_key, $url);
    }

    /**
     * 获取URL
     * @param string $default_url
     * @param string $url_key
     * @return mixed
     */
    static public function page_get(string $default_url, string $url_key = 'p')
    {
        return self::value_get($url_key, $default_url);
    }

    /**
     * 设定当前页
     * @param int $page 页数
     */
    static public function curr_set(int $page = 1, string $page_key = 'page')
    {
        self::value_set($page_key, $page);
    }

    /**
     * 获取当前页
     * @param string $pre
     * @param string $page_key GET 页数key
     * @param int $default_page 默认忽略 的页数
     * @return string
     */
    static public function curr_get(string $pre = '?', string $page_key = 'page', int $default_page = 1)
    {
        $page = self::value_get($page_key, $default_page);
        if ($page == $default_page) {
            return '';
        }
        return "{$pre}{$page_key}={$page}";
    }

    /**
     * 设定值
     * @param string $key
     * @param mixed $value
     */
    static public function value_set(string $key, $value)
    {
        setcookie("pu_{$key}", $value, time() * 1.2, '/');
    }

    /**
     * 获取值
     * @param string $key 值key
     * @param mixed $default_value 如值为空，返回本值
     * @return mixed
     */
    static public function value_get(string $key, $default_value)
    {
        $val = $_COOKIE["pu_{$key}"];
        return $val ? $val : $default_value;
    }
}
