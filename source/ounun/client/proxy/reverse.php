<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\client\proxy;


use ounun\utils\curl\http;

class reverse
{
    /** @var int 块大小 100k */
    const Chunk_Size = 102400;

    /** @var bool 是否ssl */
    public $is_ssl     = false;
    /** @var bool false: 高速，不转发 true:正常代理 */
    public $is_header  = false;
    /** @var bool false: 数据缓存后，内容直接输出  true:数据缓存后，再跳转 */
    // public $is_jump = false;

    /** @var string 缓存数据 */
    public $content    = '';

    /** @var string 服务器版本 */
    protected $_server_version  = 'Ounun.org Download Server';
    /** @var string 服务器源网址root */
    protected $_server_url_root = '';
    /** @var string 服务器源path */
    protected $_server_path     = '';
    /** @var string 服务器源file */
    protected $_server_file     = '';

    /** @var int 端口 */
    protected $_port = 80;
    /** @var string 主机名 */
    protected $_host = '';
    /** @var string 服务器ip */
    protected $_ip   = '127.0.0.1';

    /** @var int 在服务器端 缓存时间 0:默认已缓存就不更新了 */
    protected $_cache_life          = 0;
    /** @var int 在客户端   缓存时间 */
    protected $_cache_time          = 72000;
    /** @var string 遇到cc的参数就更新缓存; */
    protected $_cache_clean_param   = 'clean';
    /** @var string 根目录 */
    protected $_cache_path_root     = '';
    /** @var string 本地目录名称 */
    protected $_cache_filename      = '';

    /** @var int  */
    protected $_http_code = 200;
    /** @var int  */
    protected $_last_modified = 0;
    /** @var string  */
    protected $_http_if_modified_304 = '';

    /** @var string 路径前缀   $path_forward  */
    protected $_path_prefix         = '';

    /** @var array 替换数据[0],[1] */
    protected $_data_replace   = [];

    /**
     * reverse constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if($config['server_version']){
            $this->_server_version  = $config['server_version'];
        }
        if($config['server_url_root']){
            $this->_server_url_root = $config['server_url_root'];
        }
        if($config['server_path']){
            $this->_server_path     = $config['server_path'];
        }
        if($config['server_file']){
            $this->_server_file    = $config['server_file'];
        }

        if($config['host']){
            $this->_host   =  $config['host'];
        }
        if($config['port']){
            $this->_port   =  $config['port'];
        }
        if($config['ip']){
            $this->_ip     =  $config['ip'];
        }

        // cache
        if($config['cache_time']){
            $this->_cache_time          =  $config['cache_time'];
        }
        if($config['cache_life']){
            $this->_cache_life          =  $config['cache_life'];
        }
        if($config['cache_clean_param']){
            $this->_cache_clean_param   =  $config['cache_clean_param'];
        }
        if($config['cache_path_root']){
            $this->_cache_path_root     =  $config['cache_path_root'];
        }
        if($config['cache_filename']){
            $this->_cache_filename      =  $config['cache_filename'];
        }

        $this->_http_if_modified_304 = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';
//      $this->_http_code            = 200;

        if($config['is_ssl']){
            $this->is_ssl     =  $config['is_ssl'];
        }
        if($config['is_header']){
            $this->is_header  =  $config['is_header'];
        }
    }

    /**
     * 查看文件是否存在
     * @return array
     */
    public function cache_check()
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if( 'GET' == $_SERVER['REQUEST_METHOD'] ){
                return $this->cache_get();
            }elseif('POST' == $_SERVER['REQUEST_METHOD']) {
                // return $this->cache_post();
                return $this->cache_get();
            }else{
                return error('404-REQUEST_METHOD',404,404);
            }
        }else{
            return succeed(304);
        }
    }

    /**
     * @param $url
     * @return array
     */
    public function cache_get()
    {
        $local_filename = $this->local_filename();
        if(file_exists($local_filename)){
            $mtime                  = filemtime($local_filename);
            $this->_last_modified   = gmdate("D, d M Y H:i:s", $mtime) . " GMT";
            // $filesize = filesize($local_filename);
            if ($this->_cache_clean_param && isset($req[$this->_cache_clean_param])) {//含有cc参数
                unlink($local_filename);
            }elseif($this->_cache_life <= 0 ){
                return succeed(304);
            }elseif($this->_cache_life && $mtime + $this->_cache_life < time() ){
                return succeed(304);
            }
        }
        if(empty($this->_server_url_root)){
            return error('404-empty-server_url_root',404,404);
        }
        $server_url = $this->server_url();
        $this->content = http::file_get_contents_loop($server_url,'',1);
        if($this->content){
            $this->write($local_filename,$this->content);
            return succeed(200);
        }
        return error('404-error-file_get_contents',404,404);
    }

    /**
     * @param string $local_filename
     * @param string $content
     * @return false|int
     */
    protected function write(string $local_filename, string $content)
    {
        $local_dir      = dirname($local_filename);
        if(!file_exists($local_dir)){
            mkdir($local_dir, 0777, true);
        }
        if ($this->_data_replace) {
            $content = str_replace($this->_data_replace[0], $this->_data_replace[1], $content);
        }
        $rs = file_put_contents($local_filename, $content);
        if($rs){
            chmod($local_filename, 0777);
            $mtime                  = filemtime($local_filename);
            $this->_last_modified   = gmdate("D, d M Y H:i:s", $mtime) . " GMT";
        }
        return $rs;
    }


    /**
     * @param string $local_filename
     * @param bool $http_if_modified_304
     * @param bool $is_sendfile
     * @param int $time_expired
     */
    public function output(string $local_filename, bool $is_sendfile = false,int $time_expired = 0)
    {
        $time_expired            = $time_expired == 0 ? time() : $time_expired;
        if ($this->_http_if_modified_304) {
            $time_current_string = gmdate("D, d M Y H:i:s", $time_expired);
            header("HTTP/1.1 304 Not Modified");
            header("Date: Wed, {$time_current_string} GMT");
            header("Last-Modified: {$this->_last_modified}");
            header("Server: {$this->_server_version}");
        } else {
            if($is_sendfile || empty($this->content)){
                static::output_sendfile($local_filename,$this->_cache_time,$this->_last_modified,$this->_server_version,$this->is_header,$time_expired);
            }else{
                static::output_content($this->content,$local_filename,$this->_cache_time,$this->_last_modified,$this->_server_version,$this->is_header,$time_expired);
            }
        }
    }

    /**
     * @param string $content
     * @param string $local_filename
     * @param int $time_cache
     * @param string $last_modified
     * @param string $server_version
     * @param bool $is_header
     * @param int $time_expired
     */
    static public function output_content(string $content,string $local_filename,int $time_cache, string $last_modified, string $server_version,bool $is_header, int $time_expired = 0)
    {
        $time_current_string = gmdate("D, d M Y H:i:s", $time_expired);
        $time_expired_string = gmdate("D, d M Y H:i:s", ($time_expired + $time_cache));
        header("HTTP/1.1 200 OK");
        header("Date: Wed, {$time_current_string} GMT");
        if ($is_header) {
            $content_type = mime_content_type($local_filename);
            header("Content-Type: {$content_type}");
        }
        if ($last_modified) {
            header("Last-Modified: {$last_modified}");
        }
        header("Cache-Control: max-age={$time_cache}");
        header("Expires: {$time_expired_string} GMT");
        header("Server: {$server_version}");
        exit($content);
    }

    /**
     * @param string $local_filename
     * @param int $time_cache
     * @param string $last_modified
     * @param string $server_version
     * @param bool $is_header
     * @param int $time_expired
     */
    static public function output_sendfile(string $local_filename, int $time_cache, string $last_modified, string $server_version,bool $is_header, int $time_expired = 0)
    {
        // exit(__METHOD__);

        $time_current_string = gmdate("D, d M Y H:i:s",  $time_expired);
        $time_expired_string = gmdate("D, d M Y H:i:s", ($time_expired + $time_cache));

        header("HTTP/1.1 200 OK");
        header("Date: Wed, {$time_current_string} GMT");

        if ($is_header) {
            $content_type = mime_content_type($local_filename);
            header("Content-Type: {$content_type}");
        }
        if ($last_modified) {
            header("Last-Modified: {$last_modified}");
        }
        header("Cache-Control: max-age={$time_cache}");
        header("Expires: {$time_expired_string} GMT");
        header("Server: {$server_version}");
        readfile($local_filename);
        exit;
    }

    /**
     * 远程地址
     * @return string
     */
    public function server_url()
    {
        return  $this->_server_url_root.'/'.($this->_server_path?$this->_server_path.'/':'').$this->_server_file;
    }

    /**
     * 本地文件名
     * @return string
     */
    public function local_filename()
    {
        return $this->_cache_path_root.$this->_cache_filename;
    }

    /**
     * @param array $data
     */
    public function data_replace_set(array $data = [])
    {
        $this->_data_replace  =  $data;
    }

    /**
     * @param string $path_prefix
     */
    public function path_prefix_set(string $path_prefix = '')
    {
        $this->_path_prefix   =  $path_prefix;
    }




    public function connect()
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                //gets rid of mulitple ? in URL
                $url_remote  = $this->url();
                $headers     = static::headers();
                $ch          = curl_init();

                if ($this->_cache_path_root) {
                    $this->content = $this->cache($this->_cache_path_root);
                }
                if (!$this->content) {
                    curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                    $proxyHeaders = [
                        "X-Forwarded-For: {$headers['X-Forwarded-For']}",
                        "User-Agent: {$headers['User-Agent']}",
                        "Host: {$this->_host}"
                    ];

                    if ($headers['X-Forwarded-For'] && strlen($headers['X-Forwarded-For']) > 1) {
                        $proxyHeaders[] = "X-Requested-With: " . $headers['X-Forwarded-For'];
                        //echo print_r($proxyHeaders);
                    }

                    curl_setopt($ch, CURLOPT_HTTPHEADER, $proxyHeaders);

                    $cookie = $this->cookie();
                    if ($cookie) {
                        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                    }
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $abs_cache = $this->cache_filename($this->translate_url);
                    if ($this->is_get && $this->_cache_path_root) {
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        mkdir(dirname($abs_cache), 0777, true);
                        $fp = fopen($abs_cache, 'w+');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_exec($ch);
                        $info = curl_getinfo($ch);
                        curl_close($ch);
                        fclose($fp);
                        chmod($abs_cache, 0777);
                        header("FILE_CACHE: 404");
                        $this->content = file_get_contents($abs_cache);
                        if ($this->_data_replace) {
                            $this->content = str_replace($this->_data_replace[0], $this->_data_replace[1], $this->content);
                            file_put_contents($abs_cache, $this->content);
                        }
                        if ($this->_http_code == '200' && $this->content) {
                            header("HTTP/1.1 200 OK");
                            header("Content-Type: " . $info["content_type"]);
                            echo($this->content);
                            exit;
                        }
                    }
                }
            }elseif($_SERVER['REQUEST_METHOD'] == 'POST') {
                $ch          = curl_init();
                curl_setopt($ch, CURLOPT_POST, 1);
                $post_data   = [];
                $post_file   = false;
                $upload_path = $this->_cache_path_root.'upload';

                if (count($_FILES) > 0) {
                    if (!is_writable($upload_path)) {
                        die('You cannot upload to the specified directory, please CHMOD it to 777.');
                    }
                    foreach ($_FILES as $key => $file) {
                        copy($file["tmp_name"], $upload_path . $file["name"]);
                        $proxy_location = "@" . $upload_path . $file["name"] . ";type=" . $file["type"];
                        $post_data      = [$key => $proxy_location];
                        $post_file      = true;
                    }
                }

                foreach ($_POST as $key => $value) {
                    if (!is_array($value)) {
                        $post_data[$key] = $value;
                    } else {
                        $post_data[$key] = serialize($value);
                    }
                }

                if (!$post_file) {
                    $post_string  = '';
                    $first_loop   = true;
                    foreach ($post_data as $key => $value) {
                        $parameterItem = urlencode($key) . "=" . urlencode($value);
                        if ($first_loop) {
                            $post_string .= $parameterItem;
                        } else {
                            $post_string .= "&" . $parameterItem;
                        }
                        $first_loop = false;
                    }
                    $post_data = $post_string;
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            }

            //gets rid of mulitple ? in URL
            $translateURL = $this->url();
            $headers      = static::headers();

            if ($this->is_get && $this->_cache_path_root) {
                $this->content = $this->cache($this->translate_url);
            }
            if (!$this->content) {
                curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                $proxyHeaders = [
                    "X-Forwarded-For: {$headers['X-Forwarded-For']}",
                    "User-Agent: {$headers['User-Agent']}",
                    "Host: {$this->_host}"
                ];

                if (strlen($this->_http_x_requested_with) > 1) {
                    $proxyHeaders[] = "X-Requested-With: " . $this->_http_x_requested_with;
                    //echo print_r($proxyHeaders);
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $proxyHeaders);

                $cookie = $this->cookie();
                if ($cookie) {
                    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                }
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $abs_cache = $this->cache_filename($this->translate_url);
                if ($this->is_get && $this->_cache_path_root) {
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    mkdir(dirname($abs_cache), 0777, true);
                    $fp = fopen($abs_cache, 'w+');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);
                    fclose($fp);
                    chmod($abs_cache, 0777);
                    header("FILE_CACHE: 404");
                    $this->content = file_get_contents($abs_cache);
                    if ($this->_data_replace) {
                        $this->content = str_replace($this->_data_replace[0], $this->_data_replace[1], $this->content);
                        file_put_contents($abs_cache, $this->content);
                    }
                    if($this->_http_code == 200 && $this->content){
                        header("HTTP/1.1 200 OK");
                        header("Content-Type: " . $info["content_type"]);
                        echo($this->content);
                        exit;
                    }
                }else {
                    $output = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);
                }
                $this->connect_post($info, $output);
            }
        } else {
            $this->_last_modified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            $this->_http_if_modified_304 = true;
        }
    }



    /**
     * @return array
     */
    public static function headers()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } elseif ($name == "CONTENT_TYPE") {
                $headers["Content-Type"]     = $value;
            } elseif ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"]   = $value;
//            } elseif (stristr($name, "X-Requested-With")) {
//                $headers["X-Requested-With"] = $value;
            }
        }

        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $headers["X-Forwarded-For"] = $_SERVER['REMOTE_ADDR'];
        } else {
            $headers["X-Forwarded-For"] = $_SERVER['HTTP_X_FORWARDED_FOR'] . ", " . $_SERVER['REMOTE_ADDR'];
        }

//      $this->_data_headers = $headers;
        return $headers;
    }


    /**
     * @return string
     */
    public static function cookie()
    {
        $cookie = '';
        foreach ($_COOKIE as $i => $value) {
            $cookie = $cookie . " {$i}={$_COOKIE[$i]};";
        }
        return $cookie;
    }

    /**
     * @param string $host
     * @param int $port
     * @param bool $is_ssl
     * @return string
     */
    public static function host(string $host,int $port = 80, bool $is_ssl = false)
    {
        if($is_ssl){
            if (empty($port) || $port == 443) {
                return 'https://' . $host;
            } else{
                return 'https://' . $host . ':' . $port;
            }
        }else{
            if (empty($port) || $port == 80) {
                return 'http://' . $host;
            } else{
                return 'http://' . $host . ':' . $port;
            }
        }
    }
}


