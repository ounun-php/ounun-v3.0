<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);
namespace ounun\page;

class utils
{
    /**
     * @param string $path
     * @param string $addon_tag
     * @param string $addon_view
     * @param array|null $query 数据
     * @param array|null $replace_ext
     * @param array|null $skip 忽略的数据 如:page
     * @param string $url_key
     * @return string
     */
    static public function query(string $path, string $addon_tag, string $addon_view = '', ?array $query = null, ?array $replace_ext = null, ?array $skip = null, string $url_key = 'u'): string
    {
        $query = $query ?? $_GET;
        $path2 = url_build_query($path, $query, $replace_ext ?? [], $skip ?? []);
        // print_r(['$url_original'=>$url_original,'$url'=>$url,'$paras_gets'=>$paras_gets,'$paras_page'=>$paras_page]);
        // exit();
        self::url_set($path2, $addon_tag, $addon_view, $url_key);
        return $path2;
    }

    /**
     * 设定当前页面
     *
     * @param string $path
     * @param string $addon_tag
     * @param string $addon_view
     * @param string $url_key
     */
    static public function url_set(string $path, string $addon_tag, string $addon_view = '', string $url_key = 'u')
    {
        $url = \ounun::url_addon_get($addon_tag, $addon_view, $path);
        self::value_set($url_key, $url);
    }

    /**
     * 获取URL
     *
     * @param string $path
     * @param string $addon_tag
     * @param string $addon_view
     * @param string $url_key
     * @return mixed
     */
    static public function url_get(string $path, string $addon_tag, string $addon_view = '', string $url_key = 'u')
    {
        $url = \ounun::url_addon_get($addon_tag, $addon_view, $path);
        return self::value_get($url_key, $url);
    }

    /**
     * 设定当前页码
     *
     * @param int $page 页数
     * @param string $page_key
     */
    static public function curr_page_set(int $page = 1, string $page_key = 'page')
    {
        self::value_set($page_key, $page);
    }

    /**
     * 获取当前页码
     *
     * @param string $pre
     * @param string $page_key GET 页数key
     * @param int $default_page 默认忽略 的页数
     * @return string
     */
    static public function curr_page_get(string $pre = '?', string $page_key = 'page', int $default_page = 1): string
    {
        $page = self::value_get($page_key, $default_page);
        if ($page == $default_page) {
            return '';
        }
        return "{$pre}{$page_key}={$page}";
    }

    /**
     * 设定值
     *
     * @param string $key
     * @param mixed $value
     */
    static public function value_set(string $key, $value)
    {
        setcookie("pu_{$key}", $value, time() + 31100000, '/');
    }

    /**
     * 获取值
     *
     * @param string $key 值key
     * @param mixed $default_value 如值为空，返回本值
     * @return mixed
     */
    static public function value_get(string $key, $default_value)
    {
        return $_COOKIE["pu_{$key}"] ?? $default_value;
    }
}
