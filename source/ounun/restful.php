<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

use ounun;
use ounun\addons\logic;
use ounun\restful\error_code;

abstract class restful
{
    /** @var string method */
    protected string $_method;

    /** @var array gets */
    // protected array $_request_gets = [];

    /** @var array post */
    // protected array $_request_post = [];

    /** @var array cookie */
    // protected array $_request_cookie = [];

    /** @var array|null inputs */
    protected ?array $_request_inputs = [];

    /** @var string accept */
    protected string $_http_accept;

    /** @var string 插件标识 */
    protected string $_addon_tag = '';

    /** @var logic logic */
    public static $logic;

    /**
     * ounun_view constructor.
     * @param array $url_mods
     * @param string $addon_tag
     */
    public function __construct(array $url_mods, string $addon_tag = '')
    {
        // http
        $this->_method      = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->_http_accept = strtolower($_SERVER['HTTP_ACCEPT']);
        $this->_addon_tag   = $addon_tag;
//      $this->_request_gets = $_GET;
//      $this->_request_post = $_POST;
//      $this->_request_cookie = $_COOKIE;

        // input
        if (empty($_POST)) {
            $data = file_get_contents('php://input');
            if ($data) {
                $this->_request_inputs = json_decode_array($data);
            }
        }

        // before
        $rs = $this->_construct_before($url_mods);
        if (error_is($rs)) {
            out($rs);
        }

        // url_mods
        if (empty($url_mods)) {
            $url_mods = [ounun::Def_Method];
        }
        $class = "\\addons\\{$addon_tag}\\api\\{$url_mods[0]}";

        // debug::header(['$class' => $class, '$url_mods' => $url_mods], '', __FILE__, __LINE__);
        if ($addon_tag && class_exists($class)) {
            new $class($url_mods, $this);
        } else {
            error404('$class:' . $class);
        }
    }

    /**
     * 构建前执行
     *
     * @param $url_mods
     * @return bool|array
     */
    public function _construct_before(array $url_mods = [])
    {
        return true;
    }

    /**
     * @param string $methods
     * @param string $domain
     * @param string $headers
     */
    static public function headers_allow_origin_set(string $methods = 'GET,POST,PUT,DELETE', string $domain = '*', string $headers = '*')
    {
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Origin: ' . $domain);
        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: ' . $headers);
    }

    /**
     * @param string $content_type
     * @param int $status_code
     * @param string $http_version
     */
    public static function headers_set(string $content_type, int $status_code = 200, string $http_version = 'HTTP/1.1')
    {
        $status_message = error_code::Maps[$status_code] ?? error_code::Maps[200];

        header($http_version . ' ' . $status_code . ' ' . $status_message);
        header('Content-Type: ' . $content_type . '; charset=utf-8');
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function gets_get(string $key = ''): array
    {
        if ($key) {
            // return $this->_request_gets[$key];
            return $_GET[$key];
        }
        // return $this->_request_gets;
        return $_GET;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function post_get(string $key = ''): array
    {
        if ($key) {
            // return $this->_request_post[$key];
            return $_POST[$key];
        }
        // return $this->_request_post;
        return $_POST;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function cookie_get(string $key = ''): array
    {
        if ($key) {
            // return $this->_request_cookie[$key];
            return $_COOKIE[$key];
        }
        // return $this->_request_cookie;
        return $_COOKIE;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function input_get(string $key = ''): ?array
    {
        if ($key) {
            return $this->_request_inputs[$key];
        }
        return $this->_request_inputs;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function request_get(string $key): ?array
    {
        if ($key) {
            if ($_GET && isset($_GET[$key])) {
                return $_GET[$key];
            } elseif ($_POST && isset($_POST[$key])) {
                return $_POST[$key];
            } elseif ($this->_request_inputs && isset($this->_request_inputs[$key])) {
                return $this->_request_inputs[$key];
            } elseif ($_COOKIE && isset($_COOKIE[$key])) {
                return $_COOKIE[$key];
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function method_get(): string
    {
        return $this->_method;
    }
}
