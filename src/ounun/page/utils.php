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
     * 获取当前页面 并保存到cookie(可定义参数)
     *
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

        return self::url_set($path2, $addon_tag, $addon_view, $url_key);
    }

    /**
     * 获取当前页面 并保存到cookie(不可定义参数，没有?后面的参数)
     *
     * @param string $path
     * @param string $addon_tag
     * @param string $addon_view
     * @param string $url_key
     * @return string
     */
    static public function url_set(string $path, string $addon_tag, string $addon_view = '', string $url_key = 'u'): string
    {
        $url = \ounun::url_addon_get($addon_tag, $addon_view, $path);
        setcookie("pu_{$url_key}", $url, time() + 31100000, '/');
        return $url;
    }

    /**
     * 获取当前页面，优先读cookie里的
     *
     * @param string $path
     * @param string $addon_tag
     * @param string $addon_view
     * @param string $url_key
     * @return mixed
     */
    static public function url_get(string $path, string $addon_tag, string $addon_view = '', string $url_key = 'u'): mixed
    {
        return $_COOKIE["pu_{$url_key}"] ?? \ounun::url_addon_get($addon_tag, $addon_view, $path);
    }
}
