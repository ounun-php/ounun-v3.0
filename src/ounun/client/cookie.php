<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\client;


class cookie
{
    /** @var string cookie前缀 */
    protected static string $_prefix = '';

    /** @var string 目录 */
    protected static string $_path = '/';

    /** @var string 域名 */
    protected static string $_domain = '';

    /**
     * 配制
     *
     * @param string $prefix cookie前缀
     * @param string $path 目录
     * @param string $domain 域名
     */
    public static function config(string $prefix = '', string $path = '/', string $domain = '')
    {
        empty($prefix) || static::$_prefix = $prefix;
        empty($path) || static::$_path = $path;
        empty($domain) || static::$_domain = $domain;
    }

    /**
     * cookie设定
     *
     * @param string $key
     * @param mixed $value
     * @param int $time
     */
    public static function set(string $key, $value = null, int $time = 0)
    {
        if (is_null($value)) {
            $time = time() - 3600;
        } elseif ($time > 0 && $time < 31536000) {
            $time += time();
        }
        $s             = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
        $key           = static::$_prefix . $key;
        $_COOKIE[$key] = $value;
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                setcookie($key . '[' . $k . ']', $v, $time, static::$_path, static::$_domain, $s);
            }
        } else {
            setcookie($key, $value, $time, static::$_path, static::$_domain, $s);
        }
    }

    /**
     * 取得cookie
     *
     * @param string $key
     * @param mixed $default
     * @return string
     */
    public static function get(string $key,$default = null)
    {
        $key = static::$_prefix . $key;
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * 删除cookie
     *
     * @param string $key
     */
    public static function del(string $key)
    {
        static::set($key, null, 0);
    }
}
