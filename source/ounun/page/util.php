<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\page;

class util
{
    /**
     * @param string $url_original URL
     * @param array $data_query 数据
     * @param array $replace_ext 要替换的数据
     * @param array $skip 忽略的数据 如:page
     * @return string
     */
    static public function url(array $data_query = [], array $replace_ext = [], array $skip = [],string $url_key = 'p',string $url_original = '')
    {
        $data_query   = $data_query   ? $data_query   : $_GET;
        $replace_ext  = $replace_ext  ? $replace_ext  : ['page' => '{page}'];
        $url_original = $url_original ? $url_original : url_original();
        $url          = url_build_query($url_original, $data_query, $replace_ext, $skip);
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
        $default_url = \ounun::url_page(\ounun::$url_addon_pre.$url);
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
        $default_url = \ounun::url_page(\ounun::$url_addon_pre.$default_url);
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
