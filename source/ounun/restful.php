<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

class restful
{
    /** @var string method */
    protected $_method;

    /** @var array gets */
    protected $_request_gets = [];

    /** @var array post */
    protected $_request_post = [];

    /** @var array inputs */
    protected $_request_inputs = [];

    /** @var string accept */
    protected $_http_accept;

    /** @var string 插件标识 */
    protected $_addon_tag = '';

    /**
     * ounun_view constructor.
     * @param array $url_mods
     * @param string $addon_tag
     */
    public function __construct(array $url_mods, string $addon_tag = '')
    {
        $rs = $this->_construct_before($url_mods);
        if (error_is($rs)) {
            out($rs);
        }
        //
        $this->_method       = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->_http_accept  = strtolower($_SERVER['HTTP_ACCEPT']);
        $this->_addon_tag    = $addon_tag;
        $this->_request_gets = $_GET;
        $this->_request_post = $_POST;
        $data                = file_get_contents('php://input');
        if ($data) {
            $this->_request_inputs = json_decode_array($data);
        }
        if (!$url_mods) {
            $url_mods = [\ounun::def_method];
        }
        $class = "\\addons\\{$addon_tag}\\api\\{$url_mods[0]}";
        if ($addon_tag && class_exists($class)) {
            new $class($url_mods, $this);
        } else {
            error404('$class:' . $class);
        }
    }

    /**
     * @param $mod
     * @return bool|array
     */
    public function _construct_before($mod)
    {
        return true;
    }

    /**
     * @param string $methods
     * @param string $domain
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
        $status_message = \ounun\restful\error_code::Maps[$status_code] ?? \ounun\restful\error_code::Maps[200];

        header($http_version . ' ' . $status_code . ' ' . $status_message);
        header('Content-Type: ' . $content_type . '; charset=utf-8');
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function gets_get($key = '')
    {
        if ($key) {
            return $this->_request_gets[$key];
        }
        return $this->_request_gets;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function post_get($key = '')
    {
        if ($key) {
            return $this->_request_post[$key];
        }
        return $this->_request_post;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function input_get($key = '')
    {
        if ($key) {
            return $this->_request_inputs[$key];
        }
        return $this->_request_inputs;
    }

    /**
     * @return string
     */
    public function method_get()
    {
        return $this->_method;
    }
}
