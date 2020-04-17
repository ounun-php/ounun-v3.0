<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;

class restful  extends \v
{
    /** @var string  */
    protected $_class;

    /** @var string  */
    protected $_method;

    /** @var array  */
    protected $_request_gets;

    /** @var array  */
    protected $_request_post;

    /** @var array  */
    protected $_request_inputs;

    /** @var string  */
    protected $_http_accept;

    /** @var string  */
    protected $_http_version = 'HTTP/1.1';

    /**
     * restful constructor.
     * @param $url_mods
     * @param string $addon_tag  设定的$addon_tag
     */
    public function __construct($url_mods, string $addon_tag = '')
    {
        if(empty($addon_tag)){
            error404("<strong>REQUEST_METHOD</strong> -->   {$_SERVER['REQUEST_METHOD']} <br />\n
                           <strong>HTTP_ACCEPT</strong> -->   {$_SERVER['HTTP_ACCEPT']} <br />\n
                           <strong>get</strong> ------> " . json_encode($_GET,JSON_UNESCAPED_UNICODE) . " <br />\n
                           <strong>post</strong> ------> " . json_encode($_POST,JSON_UNESCAPED_UNICODE) . " <br />\n
                           <strong>input</strong> ------> " . file_get_contents('php://input') . " <br />\n
                           <strong>Mods</strong> ------> " . json_encode($url_mods,JSON_UNESCAPED_UNICODE) . " <br />");
        }
        $this->_class        = "addons\\".$addon_tag."\\api";
        $this->_method       = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->_http_accept  = strtolower($_SERVER['HTTP_ACCEPT']);
        $this->_request_gets = $_GET;
        $this->_request_post = $_POST;
        $data = file_get_contents('php://input');
        if($data){
            $this->_request_inputs = json_decode_array($data);
        }
        if($this->_class){
            if (!$url_mods) {
                $url_mods     =  [\ounun::def_method];
            }
            $class            = "{$this->_class}\\{$url_mods[0]}";
            if(class_exists($class)){
                \ounun::$view = $this;
                static::$tpl  = true;  // 不去初始化template
                // 控制器初始化
                $rs = $this->_initialize($url_mods[0]);
                if(error_is($rs)){
                    out($rs);
                }
//              print_r(['$url_mods'=>$url_mods,'$addon_tag'=>$addon_tag,'\ounun::$url_addon_pre'=>\ounun::$url_addon_pre,'$class'=>$class]);
//              exit();
                $this->init_page('/'.($url_mods[0] && $url_mods[0] != \ounun::def_method ? $url_mods[0].'.php':''), false, true);
                new $class($url_mods,$this);
            }else{
//              print_r(['$url_mods'=>$url_mods,'$addon_tag'=>$addon_tag,'$class'=>$class]);
                parent::__construct($url_mods,$addon_tag);
            }
        }
    }

    /**
     * @param string $age
     * @param string $domain
     * @param string $methods
     * @param string $headers
     */
    static public function headers_allow_origin_set(string $domain = '*',string $age = '1728000',string $methods = 'GET,POST,PATCH,PUT,DELETE,OPTIONS', string $headers = 'Authori-zation,Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With')
    {
        header('Access-Control-Allow-Origin: '.$domain);
        header('Access-Control-Allow-Headers: '.$headers);
        header('Access-Control-Allow-Methods: '.$methods);
        header('Access-Control-Max-Age: '.$age);
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
        $status_message = $Http_Status_Message[$status_code]??$Http_Status_Message[200];

        header($http_version. ' ' . $status_code  . ' ' . $status_message);
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function gets_get($key = ''){
        if($key){
            return $this->_request_gets[$key];
        }
        return $this->_request_gets;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function post_get($key = ''){
        if($key){
            return $this->_request_post[$key];
        }
        return $this->_request_post;
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function input_get($key = ''){
        if($key){
            return $this->_request_inputs[$key];
        }
        return $this->_request_inputs;
    }

    /**
     * @return string
     */
    public function method_get(){
        return $this->_method;
    }

    /**
     * @param mixed $raw_data
     * @param int $status_code
     * @param string $request_content_type
     */
    public function out($raw_data, int $status_code = 200, string $request_content_type='') {

        $request_content_type = $request_content_type??$this->_http_accept;
        static::headers_set($request_content_type, $status_code, $this->_http_version);

        if(strpos($request_content_type,'application/json') !== false){
            $type = \ounun\c::Format_Json;
        } else if(strpos($request_content_type,'text/html') !== false){
            $type = \ounun\c::Format_Html_Table;
        } else if(strpos($request_content_type,'application/xml') !== false){
            $type = \ounun\c::Format_Xml_Simple;
        } else {
            $type = \ounun\c::Format_Json;
        }
        out($raw_data,$type);
    }
}
