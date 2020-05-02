<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

class restful extends \v
{
    protected $_class;

    protected $_method;

    protected $_request_gets;

    protected $_request_post;

    protected $_request_inputs;

    protected $_http_accept;

    protected $_http_version = 'HTTP/1.1';

    public function __construct($mod)
    {
        $rs = $this->_construct_before($mod);
        if (error_is($rs)) {
            out($rs);
        }
        //
        $this->_method       = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->_http_accept  = strtolower($_SERVER['HTTP_ACCEPT']);
        $this->_request_gets = $_GET;
        $this->_request_post = $_POST;
        $data                = file_get_contents('php://input');
        if ($data) {
            $this->_request_inputs = json_decode_array($data);
        }
        if ($this->_class) {
            if (!$mod) {
                $mod = [\ounun::def_method];
            }
            $class = "{$this->_class}\\{$mod[0]}";
            if (class_exists($class)) {
                \ounun::$view = $this;
                static::$tpl  = true;  // 不去初始化template
                $this->init_page(\ounun::$url_addon_pre . '/' . ($mod[0] && $mod[0] != \ounun::def_method ? $mod[0] . '.php' : ''), false, true);
                new $class($mod, $this);
            } else {
                parent::__construct($mod);
            }
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
    static public function headers_allow_origin_set(string $methods = 'GET,POST,PUT,DELETE,OPTIONS', string $domain = '*')
    {
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Origin: ' . $domain);
        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: authentication,origin,x-requested-with,content-type,accept,token,appid,unitid');
    }

    /**
     * @param string $content_type
     * @param int $status_code
     * @param string $http_version
     */
    public static function headers_set(string $content_type, int $status_code = 200, string $http_version = 'HTTP/1.1')
    {
        $Http_Status_Message = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => '资源有空表示(No Content)',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => '资源的URI已被更新(Moved Permanently)',
            302 => 'Found',
            303 => '其他（如，负载均衡）(See Other)',
            304 => '资源未更改（缓存）(Not Modified)',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => '指代坏请求(Bad Request)',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => '资源不存在(Not Found)',
            405 => 'Method Not Allowed',
            406 => '服务端不支持所需表示(Not Acceptable)',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => '通用冲突(Conflict)',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => '通用错误响应(Internal Server Error)',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => '服务端当前无法处理请求(Service Unavailable)',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
        $status_message      = $Http_Status_Message[$status_code] ?? $Http_Status_Message[200];

        header($http_version . ' ' . $status_code . ' ' . $status_message);
        header('Content-Type: ' . $content_type . '; charset=utf-8');
    }

    public function gets_get($key = '')
    {
        if ($key) {
            return $this->_request_gets[$key];
        }
        return $this->_request_gets;
    }

    public function post_get($key = '')
    {
        if ($key) {
            return $this->_request_post[$key];
        }
        return $this->_request_post;
    }

    public function input_get($key = '')
    {
        if ($key) {
            return $this->_request_inputs[$key];
        }
        return $this->_request_inputs;
    }

    public function method_get()
    {
        return $this->_method;
    }

    /**
     * @param mixed $raw_data
     * @param int $status_code
     * @param string $request_content_type
     */
    public function out($raw_data, int $status_code = 200, string $request_content_type = '')
    {
        $request_content_type = $request_content_type ?? $this->_http_accept;
        static::headers_set($request_content_type, $status_code, $this->_http_version);

        if (strpos($request_content_type, 'application/json') !== false) {
            $response = $this->encode_json($raw_data);
        } else if (strpos($request_content_type, 'text/html') !== false) {
            $response = $this->encode_html($raw_data);
        } else if (strpos($request_content_type, 'application/xml') !== false) {
            $response = $this->encode_xml($raw_data);
        } else {
            $response = $this->encode_json($raw_data);
        }
        exit($response);
    }


    /**
     * @param $response_data
     * @return string
     */
    public function encode_html($response_data)
    {
        if (is_array($response_data)) {
            $html_response = '<table style="border: darkcyan solid 1px;">';
            foreach ($response_data as $key => $value) {
                $html_response .= "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
            }
            $html_response .= "</table>";
            return $html_response;
        }
        return $response_data;
    }

    /**
     * @param $response_data
     * @return false|string
     */
    public function encode_json($response_data)
    {
        $jsonResponse = json_encode($response_data);
        return $jsonResponse;
    }

    /**
     * @param $response_data
     * @return mixed
     */
    public function encode_xml($response_data)
    {
        // 创建 SimpleXMLElement 对象
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><site></site>');
        foreach ($response_data as $key => $value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }
}
