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

    /** @var string 服务器版本 */
    protected $_server_version = 'Ounun.org Download Server';

    /** @var int 端口 */
    protected $_port = 80;
    /** @var string 主机名 */
    protected $_host = '';
    /** @var string 服务器ip */
    protected $_ip   = '127.0.0.1';

    /** @var string HTTP头部数据 */
    public $headers_outside         = [];
    /** @var string http参数 */
    public $http_x_forwarded_for    = '';
    /** @var string http参数 */
    public $http_x_requested_with   = '';
    /** @var string user agent */
    public $http_user_agent         = '';


    public $content_type   = '';
    public $cookie         = '';
    public $authorization  = '';
    public $request_method = 'GET';
    public $send_post      = [];

    public $path_source    = ''; // 源文件路径
    public $path_forward   = ''; // 路径前缀

    public $content        = '';

    protected $_cache_path  = '';
    protected $_cache_life  = 0;    // 默认已缓存就不更新了
    protected $_cache_param = 'cc'; // 遇到cc的参数就更新缓存;
    protected $_cache_abs   = '';
    protected $_cache_time  = 72000;

    public $is_get;
    public $is_ssl;
    public $is_header = false;





    /** @var string  */
    protected $_http_code;
    /** @var string  */
    protected $_not_modified_304;
    /** @var string  */
    protected $_last_modified;
    /** @var string  */
    protected $_result_header;
    /** @var array 替换数据[0],[1] */
    protected $_replace_data;

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

        $this->content       = '';
        $this->cookie        = '';
        $this->authorization = '';

        $this->content_type          = '';
        $this->http_user_agent       = '';
        $this->http_x_forwarded_for  = '';
        $this->http_x_requested_with = '';
        $this->request_method        = 'GET';


        $this->path_forward  = '';

        if($config['cache_path']){
            $this->_cache_path  =  $config['cache_path'];
        }
        if($config['cache_life']){
            $this->_cache_life  =  $config['cache_life'];
        }
        if($config['cache_param']){
            $this->_cache_param  =  $config['cache_param'];
        }
        if($config['cache_abs']){
            $this->_cache_abs    =  $config['cache_abs'];
        }
        if($config['cache_time']){
            $this->_cache_time  =  $config['cache_time'];
        }
        $this->_last_modified    = gmdate("D, d M Y H:i:s", time() - $this->_cache_time) . " GMT";
        $this->_not_modified_304 = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';


        $this->is_ssl     = false;
        $this->is_get     = $_SERVER['REQUEST_METHOD'] == 'GET';
        $this->is_header  = false;

        $this->_http_code = 0;
    }

    /**
     * @param bool $do_original_headers
     */
    public function output(bool $do_original_headers = true)
    {
        $time_current_string = gmdate("D, d M Y H:i:s", time());
        $time_expired_string = gmdate("D, d M Y H:i:s", (time() + $this->_cache_time));
        if ($this->_not_modified_304) {
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

    public function translate_url($server_name)
    {
        $this->path_source = $this->path_forward . $_SERVER['REQUEST_URI'];

        if (empty($_SERVER['QUERY_STRING'])) {
            return $this->translate_host($server_name) . $this->path_source;
        }
        else{
            return $this->translate_host($server_name) . $this->path_source . "?" . $_SERVER['QUERY_STRING'];
        }
    }

    /**
     * @param string $host
     * @param bool $is_ssl
     * @return string
     */
    public function translate_host(string $host, bool $is_ssl = false)
    {
        $s        = $is_ssl ? 's' : '';
        $protocol = 'http'. $s;
        if (empty($this->_port) || $this->_port == 80) {
            return $protocol . "://" . $host;
        }
        else{
            return $protocol . "://" . $host . ":" . $this->_port;
        }
    }



    public function cache($url)
    {
        $abs_cache = $this->cache_abs_get($url);
        $uri       = parse_url($_SERVER['REQUEST_URI']);
        if ($uri['query']) {
            parse_str($uri['query'], $req);
            if ($this->_cache_param && isset($req[$this->_cache_param])) {//含有cc参数
                if (strpos($abs_cache, '?' . $this->_cache_param)) {
                    $dot = '?' . $this->_cache_param;
                } else {
                    $dot = '&' . $this->_cache_param;
                }
                $ccfile = strstr($abs_cache, $dot, true);
                if (is_file($ccfile)) {
                    unlink($ccfile);
                }
                $abs_cache = $this->_cache_abs = $ccfile;
            }
        }
        if (!is_file($abs_cache)) {
            return false;
        } else {
            $lastSavetime = filemtime($abs_cache);
            if ($this->_cache_life && $lastSavetime + $this->_cache_life < time()) { //超时
                return false;
            }
            header("FILE_CACHE: 200");
            chmod($abs_cache, 0777);
            $this->content_type = mime_content_type($abs_cache);
            $this->_last_modified = gmdate("D, d M Y H:i:s", $lastSavetime);
            $content = static::wget($abs_cache);
            return $content;
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function cache_abs_get($url)
    {
        if ($this->_cache_abs) {
            return $this->_cache_abs;
        }
        $pathinfo  = parse_url($url);
        $cachefile = strrchr($pathinfo['path'], "/") == "/" ? $pathinfo['path'] . "index.html" : $pathinfo['path'];
        if ($pathinfo['query']) {
            $cachefile .= "?" . $pathinfo['query'];
        }
        $this->_cache_abs = rtrim($this->_cache_path, '/') . $cachefile;
        return $this->_cache_abs;
    }

    /**
     * @param $url
     * @param $content
     */
    public function cache_write($url, $content)
    {
        $abs_cache = $this->cache_abs_get($url);
        mkdir(dirname($abs_cache), 0777, true);
        if ($this->_replace_data) {
            $content = str_replace($this->_replace_data[0], $this->_replace_data[1], $content);
        }
        file_put_contents($abs_cache, $content);
        chmod($abs_cache, 0777);
        header("FILE_CACHE: 404");
        $this->content_type   = mime_content_type($abs_cache);
        $this->_last_modified = gmdate("D, d M Y H:i:s", time());
    }


    public function connect()
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $this->connect_pre();
            $ch = curl_init();
            if ($this->request_method == "POST") {
                curl_setopt($ch, CURLOPT_POST, 1);

                $postData = [];
                $filePost = false;
                $uploadPath = 'uploads/';

                if (count($_FILES) > 0) {
                    if (!is_writable($uploadPath)) {
                        die('You cannot upload to the specified directory, please CHMOD it to 777.');
                    }
                    foreach ($_FILES as $key => $fileArray) {
                        copy($fileArray["tmp_name"], $uploadPath . $fileArray["name"]);
                        $proxyLocation = "@" . $uploadPath . $fileArray["name"] . ";type=" . $fileArray["type"];
                        $postData = array($key => $proxyLocation);
                        $filePost = true;
                    }
                }

                foreach ($_POST as $key => $value) {
                    if (!is_array($value)) {
                        $postData[$key] = $value;
                    } else {
                        $postData[$key] = serialize($value);
                    }
                }

                if (!$filePost) {
                    //$postData = http_build_query($postData);
                    $postString = "";
                    $firstLoop = true;
                    foreach ($postData as $key => $value) {
                        $parameterItem = urlencode($key) . "=" . urlencode($value);
                        if ($firstLoop) {
                            $postString .= $parameterItem;
                        } else {
                            $postString .= "&" . $parameterItem;
                        }
                        $firstLoop = false;
                    }
                    $postData = $postString;
                }
                $this->send_post = $postData;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }

            //gets rid of mulitple ? in URL
            $translateURL = $this->translate_url(($this->_ip) ? $this->_ip : $this->_host);
            if (substr_count($translateURL, "?") > 1) {
                $firstPos = strpos($translateURL, "?", 0);
                $secondPos = strpos($translateURL, "?", $firstPos + 1);
                $translateURL = substr($translateURL, 0, $secondPos);
            }

            $this->translate_url = $translateURL;
            if ($this->is_get && $this->_cache_path) {
                $this->content = $this->cache($this->translate_url);
            }
            if (!$this->content) {
                curl_setopt($ch, CURLOPT_URL, $this->translate_url);
                $proxyHeaders = array(
                    "X-Forwarded-For: " . $this->http_x_forwarded_for,
                    "User-Agent: " . $this->http_user_agent,
                    "Host: " . $this->_host
                );

                if (strlen($this->http_x_requested_with) > 1) {
                    $proxyHeaders[] = "X-Requested-With: " . $this->http_x_requested_with;
                    //echo print_r($proxyHeaders);
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $proxyHeaders);

                if ($this->cookie != "") {
                    curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
                }
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $abs_cache = $this->cache_abs_get($this->translate_url);
                if ($this->is_get && $this->_cache_path) {
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
                    if ($this->_replace_data) {
                        $this->content = str_replace($this->_replace_data[0], $this->_replace_data[1], $this->content);
                        file_put_contents($abs_cache, $this->content);
                    }
                    if($this->_http_code == '200' && $this->content){
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
            $this->_not_modified_304 = true;
        }
    }

    public function connect_pre()
    {
        $this->http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $this->request_method  = $_SERVER['REQUEST_METHOD'];
        $temp_cookie = "";
        foreach ($_COOKIE as $i => $value) {
            $temp_cookie = $temp_cookie . " {$i}={$_COOKIE[$i]};";
        }
        $this->cookie = $temp_cookie;
        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->http_x_forwarded_for = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->http_x_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] . ", " . $_SERVER['REMOTE_ADDR'];
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
     * @return array
     */
    public function headers_outside_get()
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
            } elseif (stristr($name, "X-Requested-With")) {
                $headers["X-Requested-With"] = $value;
                $this->http_x_requested_with = $value;
            }
        }

        $this->headers_outside = $headers;
        return $headers;
    }

    /**
     * @param $file
     */
    public function sendfile($file)
    {
        $time_current_string = gmdate("D, d M Y H:i:s", time());
        $time_expired_string = gmdate("D, d M Y H:i:s", (time() + $this->_cache_time));
        header("HTTP/1.1 200 OK");
        header("Date: Wed, {$time_current_string} GMT");
        if (empty($this->content_type)) {
            $this->content_type = 'application/octet-stream';
        }
        header("Content-Type: " . $this->content_type);
        if ($this->_last_modified) {
            header("Last-Modified: $this->_last_modified");
        }
        header("Cache-Control: max-age=$this->_cache_time");
        header("Expires: $time_expired_string GMT");
        header("Server: $this->_server_version");
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
     * @param string $filename_url
     * @return false|string
     */
    public static function wget(string $filename_url)
    {
        ob_start();
        readfile($filename_url);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }


    /**
     * @param $var
     * @param null $label
     * @param bool $strict
     * @param bool $echo
     * @return false|string|string[]|true|null
     */
    public  static function dump($var, $label = null, $strict = true, $echo = true)
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


