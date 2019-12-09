<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\client\proxy;


class reverse
{
    /** @var int 块大小 100k */
    const Chunk_Size = 102400;


    /** @var bool 是否ssl */
    public $is_ssl    = false;
    /** @var bool false: 高速，不转发 true:正常代理 */
    public $is_header = false;
    /** @var bool false: 数据缓存后，内容直接输出  true:数据缓存后，再跳转 */
    public $is_jump   = false;

    /** @var string 缓存数据 */
    public $content   = '';

    /** @var string 服务器版本 */
    protected $_server_version = 'Ounun.org Download Server';

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
    protected $_cache_pathroot      = '';
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
            $this->_server_version = $config['server_version'];
        }
        if($config['port']){
            $this->_port           =  $config['port'];
        }
        if($config['host']){
            $this->_host           =  $config['host'];
        }
        if($config['ip']){
            $this->_ip             =  $config['ip'];
        }

        // cache
        if($config['cache_time']){
            $this->_cache_time  =  $config['cache_time'];
        }
        if($config['cache_life']){
            $this->_cache_life  =  $config['cache_life'];
        }
        if($config['cache_clean_param']){
            $this->_cache_clean_param   =  $config['cache_clean_param'];
        }
        if($config['cache_path']){
            $this->_cache_pathroot      =  $config['cache_path'];
        }
//        if($config['cache_filename']){
//            $this->_cache_filename    =  $config['cache_filename'];
//        }

        $this->_last_modified        = gmdate("D, d M Y H:i:s", time() - $this->_cache_time) . " GMT";
        $this->_http_if_modified_304 = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';
//      $this->_http_code            = 200;

        if($config['is_ssl']){
            $this->is_ssl     =  $config['is_ssl'];
        }
        if($config['is_header']){
            $this->is_header  =  $config['is_header'];
        }
        if($config['is_jump']){
            $this->is_jump  =  $config['is_jump'];
        }
    }

    public function check(bool $is_jump = false)
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if( 'GET' == $_SERVER['REQUEST_METHOD'] ){
                return $this->connect_get();
            }elseif('POST' == $_SERVER['REQUEST_METHOD']) {
                return $this->connect_post();
            }else{
                return error('',1,404);
            }
        }else{
            return succeed(304);
        }
    }

    public function jump(){

    }



    /**
     * @param string $path
     * @return string
     */
    protected function cache_filename(string $path)
    {
        if ($this->_cache_filename) {
            return $this->_cache_filename;
        }
        $pathinfo  = parse_url($path);
        $cachefile = strrchr($pathinfo['path'], "/") == "/" ? $pathinfo['path'] . "index.html" : $pathinfo['path'];
        if ($pathinfo['query']) {
            $cachefile .= "?" . $pathinfo['query'];
        }
        $this->_cache_filename = rtrim($this->_cache_pathroot, '/') . $cachefile;
        return $this->_cache_filename;
    }

    /**
     * @param string $filename
     * @param mixed  $content
     */
    protected function cache_write(string $filename, $content)
    {
        $abs_cache = $this->cache_filename($filename);
        mkdir(dirname($abs_cache), 0777, true);
        if ($this->_data_replace) {
            $content = str_replace($this->_data_replace[0], $this->_data_replace[1], $content);
        }
        file_put_contents($abs_cache, $content);
        chmod($abs_cache, 0777);
        header("FILE_CACHE: 404");
        $this->content_type   = mime_content_type($abs_cache);
        $this->_last_modified = gmdate("D, d M Y H:i:s", time());
    }

    public function connect_get()
    {
        $url_remote  = $this->url();
        $headers     = static::headers();
        $ch          = curl_init();
        if ($this->_cache_pathroot) {
            $this->content = $this->cache($this->_cache_pathroot);
        }
        return succeed(304);
    }

    public function connect_post()
    {
        $ch          = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        $post_data   = [];
        $post_file   = false;
        $upload_path = $this->_cache_pathroot.'upload';

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


    //gets rid of mulitple ? in URL
    $translateURL = $this->url();
    $headers      = static::headers();

    if ($this->is_get && $this->_cache_pathroot) {
        $this->content = $this->cache($this->translate_url);
    }
    if (!$this->content) {
        curl_setopt($ch, CURLOPT_URL, $this->translate_url);
        $proxyHeaders = [
            "X-Forwarded-For: {$this->_}",
            "User-Agent: {$this->_http_user_agent}",
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
        if ($this->is_get && $this->_cache_pathroot) {
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

    }

    public function connect()
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                //gets rid of mulitple ? in URL
                $url_remote  = $this->url();
                $headers     = static::headers();
                $ch          = curl_init();

                if ($this->_cache_pathroot) {
                    $this->content = $this->cache($this->_cache_pathroot);
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
                    if ($this->is_get && $this->_cache_pathroot) {
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
                $upload_path = $this->_cache_pathroot.'upload';

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

            if ($this->is_get && $this->_cache_pathroot) {
                $this->content = $this->cache($this->translate_url);
            }
            if (!$this->content) {
                curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                $proxyHeaders = [
                    "X-Forwarded-For: {$this->}",
                    "User-Agent: {$this->_http_user_agent}",
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
                if ($this->is_get && $this->_cache_pathroot) {
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
     * @param array $data
     */
    public function data_replace_set(array $data = []){
        $this->_data_replace  =  $data;
    }

    public function path_prefix_set(string $path_prefix = ''){
        $this->_path_prefix   =  $path_prefix;
    }

    /**
     * @param bool $do_original_headers
     */
    public function output(bool $do_original_headers = true)
    {
        $time_current_string = gmdate("D, d M Y H:i:s", time());
        $time_expired_string = gmdate("D, d M Y H:i:s", (time() + $this->_cache_time));
        if ($this->_http_if_modified_304) {
            header("HTTP/1.1 304 Not Modified");
            header("Date: Wed, {$time_current_string} GMT");
            header("Last-Modified: {$this->_last_modified}");
            header("Server: {$this->_server_version}");
        } else {
            header("HTTP/1.1 200 OK");
            header("Date: Wed, {$time_current_string} GMT");
            if (!$this->is_header) {
                if (!$this->content_type) {
                    $this->content_type = 'application/octet-stream';
                }
                header("Content-Type: {$this->content_type}");
            }
            if ($this->_last_modified) {
                header("Last-Modified: {$this->_last_modified}");
            }
            header("Cache-Control: max-age={$this->_cache_time}");
            header("Expires: {$time_expired_string} GMT");
            header("Server: $this->_server_version");
            preg_match("/Set-Cookie:[^\n]*/i", $this->_result_header, $result);
            foreach ($result as $i => $value) {
                header($result[$i]);
            }
            exit($this->content);
        }
    }

    /**
     * 远程获取资源的url
     * @return string
     */
    public function url()
    {
        if (empty($_SERVER['QUERY_STRING'])) {
            return static::host($this->_host,$this->_port,$this->is_ssl) . $this->_path_prefix . $_SERVER['REQUEST_URI'];
        } else {
            return static::host($this->_host,$this->_port,$this->is_ssl) . $this->_path_prefix . $_SERVER['REQUEST_URI'] . "?" . $_SERVER['QUERY_STRING'];
        }
    }

    /**
     * @param $url
     * @return bool|false|string
     */
    public function cache($url)
    {
        $filename = $this->cache_filename($url);
        $uri      = parse_url($_SERVER['REQUEST_URI']);
        if ($uri['query']) {
            parse_str($uri['query'], $req);
            if ($this->_cache_clean_param && isset($req[$this->_cache_clean_param])) {//含有cc参数
                if (strpos($filename, '?' . $this->_cache_clean_param)) {
                    $dot = '?' . $this->_cache_clean_param;
                } else {
                    $dot = '&' . $this->_cache_clean_param;
                }
                $ccfile = strstr($filename, $dot, true);
                if (is_file($ccfile)) {
                    unlink($ccfile);
                }
                $filename = $this->_cache_filename = $ccfile;
            }
        }
        if (!is_file($filename)) {
            return false;
        } else {
            $mtime = filemtime($filename);
            if ($this->_cache_life && $mtime + $this->_cache_life < time()) { //超时
                return false;
            }
            header("FILE_CACHE: 200");
            chmod($filename, 0777);
            $this->content_type   = mime_content_type($filename);
            $this->_last_modified = gmdate("D, d M Y H:i:s", $mtime);
            $content = static::wget($filename);
            return $content;
        }
    }





    public function connect2()
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                //gets rid of mulitple ? in URL
                $url_remote  = $this->url();
                $headers     = static::headers();
                $ch          = curl_init();

                if ($this->_cache_pathroot) {
                    $this->content = $this->cache($this->_cache_pathroot);
                }
                if (!$this->content) {
                    curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                    $proxyHeaders = [
                        "X-Forwarded-For: {$this->}",
                        "User-Agent: {$this->_http_user_agent}",
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
                    if ($this->is_get && $this->_cache_pathroot) {
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
                $upload_path = $this->_cache_pathroot.'upload';

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

            if ($this->is_get && $this->_cache_pathroot) {
                $this->content = $this->cache($this->translate_url);
            }
            if (!$this->content) {
                curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                $proxyHeaders = [
                    "X-Forwarded-For: {$this->}",
                    "User-Agent: {$this->_http_user_agent}",
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
                if ($this->is_get && $this->_cache_pathroot) {
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
     * @param $info
     * @param $output
     */
    public function connect_post($info, $output)
    {
        $this->content_type = $info["content_type"];
        $this->_http_code   = $info['http_code'];
        if (!empty($info['last_modified'])) {
            $this->_last_modified = $info['last_modified'];
        }
        $this->_result_header = substr($output, 0, $info['header_size']);
        $content = substr($output, $info['header_size']); //没有头部问题
        if ($this->_http_code == 200) {
            if(!$this->content) {
                $this->content = $content;
            }
        } elseif (($this->_http_code == 302 || $this->_http_code == 301) && isset($info['redirect_url'])) {
            $redirect_url = str_replace($this->_host, $_SERVER['HTTP_HOST'], $info['redirect_url']);
            header("Location: {$redirect_url}");
            exit;
        } elseif ($this->_http_code == 404) {
            header("HTTP/1.1 404 Not Found");
            exit("HTTP/1.1 404 Not Found");
        } elseif ($this->_http_code == 500) {
            header('HTTP/1.1 500 Internal Server Error');
            exit("HTTP/1.1 500 Internal Server Error");
        } else {
            exit("HTTP/1.1 " . $this->_http_code . " Internal Server Error");
        }
    }


    /**
     * @param string $file
     * @param int $time_cache
     * @param string $last_modified
     * @param string $server_version
     * @param string $content_type
     * @param int $time_expired
     */
    static public function sendfile(string $file,int $time_cache,string $last_modified,string $server_version,string $content_type = 'application/octet-stream',int $time_expired = 0)
    {
        $time_expired        = $time_expired == 0 ? time() : $time_expired;
        $time_current_string = gmdate("D, d M Y H:i:s",  $time_expired);
        $time_expired_string = gmdate("D, d M Y H:i:s", ($time_expired + $time_cache));

        header("HTTP/1.1 200 OK");
        header("Date: Wed, {$time_current_string} GMT");

        header("Content-Type: {$content_type}");
        if ($last_modified) {
            header("Last-Modified: {$last_modified}");
        }
        header("Cache-Control: max-age={$time_cache}");
        header("Expires: {$time_expired_string} GMT");
        header("Server: {$server_version}");
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') === 0) {
            if ($_SERVER['SERVER_SOFTWARE'] < 'lighttpd/1.5') {
                header("X-LIGHTTPD-send-file: $file");
            } else {
                header("X-Sendfile: $file");
            }
        } else {
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') === 0) {
                header('X-Accel-Redirect: ' . $file);
            }
        }
        exit;
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
     * @param string $filename
     * @return false|string
     */
    static public function wget(string $filename)
    {
        ob_start();
        readfile($filename);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
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

    /**
     * @param $var
     * @param null $label
     * @param bool $strict
     * @param bool $echo
     * @return false|string|string[]|true|null
     */
    public static function dump($var, $label = null, $strict = true, $echo = true)
    {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES, 'utf-8') . "</pre>";
            } else {
                $output = $label . " : " . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES, 'utf-8') . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        } else {
            return $output;
        }
    }
}


