<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\client;

class http
{
    public $method;
    public $cookie;
    public $post;
    public $header;
    public $ContentType;
    public $data;
    /** @var int 错误代码 */
    public $error_code;
    /** @var string 错误信息 */
    public $error_msg;

    /** @var $this */
    protected static $_instances = [];

    /**
     * Returns a reference to the global Browser object, only creating it
     * if it doesn't already exist.
     *
     * This method must be invoked as:
     *      <pre>  $browser = &JBrowser::i([$userAgent[, $accept]]);</pre>
     *
     * @param string $userAgent  The browser string to parse.
     * @param string $accept     The HTTP_ACCEPT settings to use.
     * @return $this  The Browser object.
     */
    public static function i($userAgent = null, $accept = null)
    {
        $signature = serialize(array($userAgent, $accept));

        if (empty(static::$_instances[$signature])) {
            static::$_instances[$signature] = new static($userAgent, $accept);
        }

        return static::$_instances[$signature];
    }

    /**
     * @return $this
     */
    static public function i2(string $uri = '')
    {
        if(empty(static::$_instances[$uri])){
            if(empty($uri)) {
                $https = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 's://' : '://';
                if (!empty($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI'])) {
                    $uri = 'http'.$https.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                } else {
                    $uri = 'http'.$https.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
                    if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) $uri .= '?'.$_SERVER['QUERY_STRING'];
                }
                $uri = urldecode($uri);
                $uri = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $uri);
                $uri = preg_replace(array('/eval\((.*)\)/', '/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/'), array('', '""'), $uri);
            }
            static::$_instances[$uri] = new static();
        }
        return static::$_instances[$uri];
    }

    /**
     * Create a browser instance (Constructor).
     *
     * @param string $userAgent The browser string to parse.
     * @param string $accept The HTTP_ACCEPT settings to use.
     * @param string $uri
     */
    public function __construct($userAgent = '', $accept = '', $uri = '')
    {

        $this->method     = 'GET';
        $this->cookie     = '';
        $this->post       = '';
        $this->header     = '';
        $this->error_code = 0;
        $this->error_msg  = '';

        $this->match($userAgent, $accept);

        if ($uri !== null) {
            $this->parse($uri);
        }
    }


    public function post($url, $data = [], $referer = '', $limit = 0, $timeout = 30, $block = true)
    {
        $this->method      = 'POST';
        $this->ContentType = "Content-Type: application/x-www-form-urlencoded\r\n";
        if ($data) {
            $post = '';
            foreach ($data as $k => $v) {
                $post .= $k . '=' . rawurlencode($v) . '&';
            }
            $this->post .= substr($post, 0, -1);
        }
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    public function get($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $this->method = 'GET';
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    public function upload($url, $data = [], $files = [], $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $this->method      = 'POST';
        $boundary          = "AaB03x";
        $this->ContentType = "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
        if ($data) {
            foreach ($data as $k => $v) {
                $this->post .= "--{$boundary}\r\n";
                $this->post .= "Content-Disposition: form-data; name=\"" . $k . "\"\r\n";
                $this->post .= "\r\n" . $v . "\r\n";
                $this->post .= "--{$boundary}\r\n";
            }
        }
        foreach ($files as $k => $v) {
            $this->post .= "--{$boundary}\r\n";
            $this->post .= "Content-Disposition: file; name=\"{$k}\"; filename=\"" . basename($v) . "\"\r\n";
            $this->post .= "Content-Type: " . $this->get_mime($v) . "\r\n";
            $this->post .= "\r\n" . file_get_contents($v) . "\r\n";
            $this->post .= "--{$boundary}\r\n";
        }
        $this->post .= "--{$boundary}--\r\n";
        return $this->request($url, $referer, $limit, $timeout, $block);
    }

    private function request($url, $referer = '', $limit = 0, $timeout = 30, $block = TRUE)
    {
        $matches = parse_url($url);
        $host    = $matches['host'];
        $path    = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port    = $matches['port'] ? $matches['port'] : 80;
        if ($referer == '') $referer = URL;
        $out = "$this->method $path HTTP/1.1\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Referer: $referer\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $out .= "Host: $host\r\n";
        if ($this->cookie) $out .= "Cookie: $this->cookie\r\n";
        if ($this->method == 'POST') {
            $out .= $this->ContentType;
            $out .= "Content-Length: " . strlen($this->post) . "\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $this->post;
        } else {
            $out .= "Connection: Close\r\n\r\n";
        }
        if ($timeout > ini_get('max_execution_time')) @set_time_limit($timeout);
        $fp = @fsockopen($host, $port, $errno, $error, $timeout);
        if (!$fp) {
            $this->error_code = $errno;
            $this->error_msg  = $error;
            return false;
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            fwrite($fp, $out);
            $this->data = '';
            $status     = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                $maxsize = min($limit, 1024000);
                if ($maxsize == 0) $maxsize = 1024000;
                $start = false;
                while (!feof($fp)) {
                    if ($start) {
                        $line = fread($fp, $maxsize);
                        if (strlen($this->data) > $maxsize) break;
                        $this->data .= $line;
                    } else {
                        $line         = fgets($fp);
                        $this->header .= $line;
                        if ($line == "\r\n" || $line == "\n") $start = true;
                    }
                }
            }
            fclose($fp);
            return $this->is_ok();
        }
    }

    public function save($file)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            import('helper.folder');
            folder::create($dir);
        }
        return file_put_contents($file, $this->data);
    }

    public function set_cookie($name, $value)
    {
        $this->cookie .= "$name=$value;";
    }

    public function get_cookie()
    {
        $cookies = [];
        if (preg_match_all("|Set-Cookie: ([^;]*);|", $this->header, $m)) {
            foreach ($m[1] as $c) {
                list($k, $v) = explode('=', $c);
                $cookies[$k] = $v;
            }
        }
        return $cookies;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function get_header()
    {
        return $this->header;
    }

    public function get_status()
    {
        preg_match("|^HTTP/1.1 ([0-9]{3}) (.*)|", $this->header, $m);
        return array($m[1], $m[2]);
    }

    public function get_mime($file)
    {
        $ext = fileext($file);
        if ($ext == '') return '';
        $mime_types = config('mime');
        return isset($mime_types[$ext]) ? $mime_types[$ext] : '';
    }

    public function is_ok()
    {
        $status = $this->get_status();
        if (intval($status[0]) != 200) {
            $this->error_code = $status[0];
            $this->error_msg  = $status[1];
            return false;
        }
        return true;
    }


    /**
     * post数据
     * @param string $url
     * @param array|string $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function stream_post(string $url, $data, array $cookie = [], int $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query'] ? '?' . $info['query'] : '');
        $port = $info['port'] ? $info['port'] : 80;
        return static::_stream('POST', $host, $page, $port, $data, $cookie, $timeout);
    }

    /**
     * get数据
     * @param string $url
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function stream_get($url, $cookie, $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query'] ? '?' . $info['query'] : '');
        $port = $info['port'] ? $info['port'] : 80;
        return static::_stream('GET', $host, $page, $port, null, $cookie, $timeout);
    }

    /**
     * 异步连接
     * @param string $type
     * @param string $host
     * @param string $page
     * @param int $port
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static protected function _stream($type, $host, $page, $port = 80, $data = [], $cookie = [], $timeout = 3)
    {
        $type      = $type == 'POST' ? 'POST' : 'GET';
        $error_no  = null;
        $error_str = null;
        $content   = [];
        if ($type == 'POST' && $data) {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $content[] = $k . "=" . rawurlencode($v);
                }
                $content = implode("&", $content);
            } else {
                $content = $data;
            }
        }
        // echo "\$host:$host, \$port:$port, \$errno:$errno, \$errstr:$errstr, \$timeout:$timeout";
        $fp = fsockopen($host, $port, $error_no, $errstr, $timeout);
        if (!$fp) {
            return error('提示:无法连接!');
        }
        $stream   = [];
        $stream[] = "{$type} {$page} HTTP/1.0";
        $stream[] = "Host: {$host}";

        if ($cookie && is_array($cookie)) {
            $tmp = [];
            foreach ($cookie as $k => $v) {
                $tmp[] = "{$k}={$v}";
            }
            $stream[] = 'Cookie:' . implode('; ', $tmp);
        }

        if ($content && $type == 'POST') {
            $stream[] = "Content-Type: application/x-www-form-urlencoded";
            $stream[] = "Content-Length: " . strlen($content);

            $stream = implode("\r\n", $stream) . "\r\n\r\n" . $content;
        } else {
            $stream = implode("\r\n", $stream) . "\r\n\r\n";
        }

        fwrite($fp, $stream);
        stream_set_timeout($fp, $timeout);

        $res  = stream_get_contents($fp);
        $info = stream_get_meta_data($fp);

        fclose($fp);
        if ($info['timed_out']) {
            return error('提示:连接超时');
        } else {
            return succeed(substr(strstr($res, "\r\n\r\n"), 4));
        }
    }

    /**
     * @param $url
     * @param $referer
     * @return bool|string
     */
    public static function file_get_contents(string $url, string $referer = '', bool $is_unzip = false, string $header = '')
    {
        $referer = $referer ? $referer : $url;
        $opts    = [
            'http' => [
                'method' => "GET",
                'header' => "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n" .
                    "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n" .
                    "Referer: {$referer}\r\n" .
                    $header,
            ],
            "ssl"  => [
                // "allow_self_signed" => false,
                "verify_peer_name"  => false,
                // "verify_peer"       => false,
                "allow_self_signed" => false,
                "verify_peer"       => false,
            ],
        ];
        $context = stream_context_create($opts);
        $cc      = @file_get_contents($url, false, $context);
        if ($cc) {
            if ($is_unzip) {
                if (strpos($cc, 'html') === false) {
                    return gzdecode($cc);
                }
            }
            return $cc;
        }
        return $cc;
    }


    /**
     * @param string $url
     * @param int $loop_max
     * @param int $file_mini_size
     * @return bool|string
     */
    public static function file_get_contents_loop(string $url, string $referer = '', int $loop_max = 5, int $sleep_time_seconds = 1, int $filesize_min = 64)
    {
        $referer = $referer ? $referer : $url;
        do {
            $loop_max--;
            $c = static::file_get_contents($url, $referer);
            if ($c && strlen($c) > $filesize_min) {
                return $c;
            }
            if ($sleep_time_seconds && $loop_max > 0 && $c == false) {
                sleep($sleep_time_seconds);
            }
        } while ($loop_max > 0 && $c == false);
        // echo "url:{$url}\n";
        return false;
    }


    /**
     * 获取网络文件，并保存
     * @param string $url
     * @param string $filename_save
     * @param string $referer
     * @param int $loop_max
     * @param int $sleep_time_seconds
     * @param int $filesize_min
     * @return bool|int
     */
    public static function file_get_put(string $url, string $filename_save, string $referer = '', int $loop_max = 5, int $sleep_time_seconds = 1, int $filesize_min = 64)
    {
        if ('http' != substr($url, 0, 4)) {
            return false;
        }
        $c = static::file_get_contents_loop($url, $referer, $loop_max, $sleep_time_seconds, $filesize_min);
        if ($c) {
            return file_put_contents($filename_save, $c);
        }
        return false;
    }

    /**
     * @param $url
     * @param $referer
     * @param $data
     * @return bool|string
     */
    public static function file_post_contents(string $url, string $referer = '', array $data = [])
    {
        $content = http_build_query($data);
        $opts    = [
            'http' => [
                'method'  => "POST",
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($content) . "\r\n" .
                    "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n" .
                    "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n" .
                    "Referer: {$referer}\r\n",
                'content' => $content
            ],
            "ssl"  => ["allow_self_signed" => true, "verify_peer" => false,],
        ];
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }

    /**
     * URL请求
     * @param string $url
     * @param string $referer
     * @param int $timeout_second
     * @return bool|mixed|string
     */
    static public function curl_https_get_compatible(string $url, string $referer = '', int $timeout_second = 10)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout_second);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/536.35'); // 模拟用户使用的浏览器
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        } elseif (version_compare(PHP_VERSION, '5.0.0') >= 0) {
            $opts   = ['http' => ['header' => "Referer:{$referer}"]];
            $result = file_get_contents($url, false, stream_context_create($opts));
        } else {
            $result = file_get_contents($url);
        }
        return $result;
    }

    /**
     * 以get方式提交请求
     * @param $url
     * @return bool|mixed
     */
    static public function curl_https_get(string $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 以post方式提交请求
     * 使用证书，以post方式提交xml到对应的接口url
     * @param string $url POST提交的内容
     * @param array $data 请求的地址
     * @param string $ssl_cer 证书Cer路径 | 证书内容
     * @param string $ssl_key 证书Key路径 | 证书内容
     * @param int $timeout_second 设置请求超时时间
     * @return bool|mixed
     */
    static public function curl_https_post(string $url, array $data = [], string $ssl_cer = '', string $ssl_key = '', int $timeout_second = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout_second);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($ssl_cer && is_string($ssl_cer) && is_file($ssl_cer)) {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $ssl_cer);
        }
        if ($ssl_cer && is_string($ssl_key) && is_file($ssl_key)) {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $ssl_key);
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, self::_curl_https_post_build($data));
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }


    /**
     * HTTP请求（支持HTTP/HTTPS，支持GET/POST）
     * @param $url
     * @param null $data
     * @return bool|string
     */
    static public function curl_https_post_json($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        // file_put_contents('/tmp/heka_weixin.' . date("Ymd") . '.log', date('Y-m-d H:i:s') . "\t" . $output . "\n", FILE_APPEND);
        return $output;
    }

    /**
     * POST数据过滤处理
     * @param array $data
     * @return array
     */
    static private function _curl_https_post_build(array $data = [])
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_string($value) && $value[0] === '@' && class_exists('CURLFile', false)) {
                    $filename = realpath(trim($value, '@'));
                    file_exists($filename) && $value = new \CURLFile($filename);
                }
            }
        }
        return $data;
    }


    /**
     * Major version number.
     *
     * @var integer
     */
    var $_majorVersion = 0;

    /**
     * Minor version number.
     *
     * @var integer
     */
    var $_minorVersion = 0;

    /**
     * Browser name.
     *
     * @var string
     */
    var $_browser = '';

    /**
     * Full user agent string.
     *
     * @var string
     */
    var $_agent = '';

    /**
     * Lower-case user agent string.
     *
     * @var string
     */
    var $_lowerAgent = '';

    /**
     * HTTP_ACCEPT string
     *
     * @var string
     */
    var $_accept = '';

    /**
     * Platform the browser is running on.
     *
     * @var string
     */
    var $_platform = '';

    /**
     * Known robots.
     *
     * @var array
     */
    var $_robots = array(
        /* The most common ones. */
        'Googlebot',
        'msnbot',
        'Slurp',
        'Yahoo',
        /* The rest alphabetically. */
        'Arachnoidea',
        'ArchitextSpider',
        'Ask Jeeves',
        'B-l-i-t-z-Bot',
        'Baiduspider',
        'BecomeBot',
        'cfetch',
        'ConveraCrawler',
        'ExtractorPro',
        'FAST-WebCrawler',
        'FDSE robot',
        'fido',
        'geckobot',
        'Gigabot',
        'Girafabot',
        'grub-client',
        'Gulliver',
        'HTTrack',
        'ia_archiver',
        'InfoSeek',
        'kinjabot',
        'KIT-Fireball',
        'larbin',
        'LEIA',
        'lmspider',
        'Lycos_Spider',
        'Mediapartners-Google',
        'MuscatFerret',
        'NaverBot',
        'OmniExplorer_Bot',
        'polybot',
        'Pompos',
        'Scooter',
        'Teoma',
        'TheSuBot',
        'TurnitinBot',
        'Ultraseek',
        'ViolaBot',
        'webbandit',
        'www.almaden.ibm.com/cs/crawler',
        'ZyBorg',
    );

    /**
     * Is this a mobile browser?
     *
     * @var boolean
     */
    var $_mobile = false;

    /**
     * Features.
     *
     * @var array
     */
    var $_features = array(
        'html'          => true,
        'hdml'          => false,
        'wml'           => false,
        'images'        => true,
        'iframes'       => false,
        'frames'        => true,
        'tables'        => true,
        'java'          => true,
        'javascript'    => true,
        'dom'           => false,
        'utf'           => false,
        'rte'           => false,
        'homepage'      => false,
        'accesskey'     => false,
        'optgroup'      => false,
        'xmlhttpreq'    => false,
        'cite'          => false,
        'xhtml+xml'     => false,
        'mathml'        => false,
        'svg'           => false
    );

    /**
     * Quirks
     *
     * @var array
     */
    var $_quirks = [
        'avoid_popup_windows'           => false,
        'break_disposition_header'      => false,
        'break_disposition_filename'    => false,
        'broken_multipart_form'         => false,
        'cache_same_url'                => false,
        'cache_ssl_downloads'           => false,
        'double_linebreak_textarea'     => false,
        'empty_file_input_value'        => false,
        'must_cache_forms'              => false,
        'no_filename_spaces'            => false,
        'no_hidden_overflow_tables'     => false,
        'ow_gui_1.3'                    => false,
        'png_transparency'              => false,
        'scrollbar_in_way'              => false,
        'scroll_tds'                    => false,
        'windowed_controls'             => false,
    ];

    /**
     * List of viewable image MIME subtypes.
     * This list of viewable images works for IE and Netscape/Mozilla.
     *
     * @var array
     */
    var $_images = array('jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp');




    /**
     * Parses the user agent string and inititializes the object with
     * all the known features and quirks for the given browser.
     *
     * @param string $userAgent  The browser string to parse.
     * @param string $accept     The HTTP_ACCEPT settings to use.
     */
    public function match($userAgent = null, $accept = null)
    {
        // Set our agent string.
        if (is_null($userAgent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->_agent = trim($_SERVER['HTTP_USER_AGENT']);
            }
        } else {
            $this->_agent = $userAgent;
        }
        $this->_lowerAgent = strtolower($this->_agent);

        // Set our accept string.
        if (is_null($accept)) {
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->_accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
            }
        } else {
            $this->_accept = strtolower($accept);
        }


        // Check if browser excepts content type xhtml+xml
        if (strpos($this->_accept, 'application/xhtml+xml')) {
            $this->setFeature('xhtml+xml');
        }

        // Check for a mathplayer plugin is installed, so we can use MathML on several browsers
        if (strpos($this->_lowerAgent, 'mathplayer') !== false) {
            $this->setFeature('mathml');
        }

        // Check for UTF support.
        if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
            $this->setFeature('utf', strpos(strtolower($_SERVER['HTTP_ACCEPT_CHARSET']), 'utf') !== false);
        }

        if (!empty($this->_agent)) {
            $this->_setPlatform();

            if (strpos($this->_lowerAgent, 'mobileexplorer') !== false ||
                strpos($this->_lowerAgent, 'openwave') !== false ||
                strpos($this->_lowerAgent, 'opera mini') !== false ||
                strpos($this->_lowerAgent, 'operamini') !== false) {
                $this->setFeature('frames', false);
                $this->setFeature('javascript', false);
                $this->setQuirk('avoid_popup_windows');
                $this->_mobile = true;
            } elseif (preg_match('|Opera[/ ]([0-9.]+)|', $this->_agent, $version)) {
                $this->setBrowser('opera');
                list($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
                $this->setFeature('javascript', true);
                $this->setQuirk('no_filename_spaces');

                if ($this->_majorVersion >= 7) {
                    $this->setFeature('dom');
                    $this->setFeature('iframes');
                    $this->setFeature('accesskey');
                    $this->setFeature('optgroup');
                    $this->setQuirk('double_linebreak_textarea');
                }
            } elseif (strpos($this->_lowerAgent, 'elaine/') !== false ||
                strpos($this->_lowerAgent, 'palmsource') !== false ||
                strpos($this->_lowerAgent, 'digital paths') !== false) {
                $this->setBrowser('palm');
                $this->setFeature('images', false);
                $this->setFeature('frames', false);
                $this->setFeature('javascript', false);
                $this->setQuirk('avoid_popup_windows');
                $this->_mobile = true;
            } elseif ((preg_match('|MSIE ([0-9.]+)|', $this->_agent, $version)) ||
                (preg_match('|Internet Explorer/([0-9.]+)|', $this->_agent, $version))) {

                $this->setBrowser('msie');
                $this->setQuirk('cache_ssl_downloads');
                $this->setQuirk('cache_same_url');
                $this->setQuirk('break_disposition_filename');

                if (strpos($version[1], '.') !== false) {
                    list($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
                } else {
                    $this->_majorVersion = $version[1];
                    $this->_minorVersion = 0;
                }

                /* IE (< 7) on Windows does not support alpha transparency in
                 * PNG images. */
                if (($this->_majorVersion < 7) &&
                    preg_match('/windows/i', $this->_agent)) {
                    $this->setQuirk('png_transparency');
                }

                /* Some Handhelds have their screen resolution in the
                 * user agent string, which we can use to look for
                 * mobile agents. */
                if (preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->_agent)) {
                    $this->_mobile = true;
                }

                switch ($this->_majorVersion) {
                    case 7:
                        $this->setFeature('javascript', 1.4);
                        $this->setFeature('dom');
                        $this->setFeature('iframes');
                        $this->setFeature('utf');
                        $this->setFeature('rte');
                        $this->setFeature('homepage');
                        $this->setFeature('accesskey');
                        $this->setFeature('optgroup');
                        $this->setFeature('xmlhttpreq');
                        $this->setQuirk('scrollbar_in_way');
                        break;

                    case 6:
                        $this->setFeature('javascript', 1.4);
                        $this->setFeature('dom');
                        $this->setFeature('iframes');
                        $this->setFeature('utf');
                        $this->setFeature('rte');
                        $this->setFeature('homepage');
                        $this->setFeature('accesskey');
                        $this->setFeature('optgroup');
                        $this->setFeature('xmlhttpreq');
                        $this->setQuirk('scrollbar_in_way');
                        $this->setQuirk('broken_multipart_form');
                        $this->setQuirk('windowed_controls');
                        break;

                    case 5:
                        if ($this->getPlatform() == 'mac') {
                            $this->setFeature('javascript', 1.2);
                            $this->setFeature('optgroup');
                        } else {
                            // MSIE 5 for Windows.
                            $this->setFeature('javascript', 1.4);
                            $this->setFeature('dom');
                            $this->setFeature('xmlhttpreq');
                            if ($this->_minorVersion >= 5) {
                                $this->setFeature('rte');
                                $this->setQuirk('windowed_controls');
                            }
                        }
                        $this->setFeature('iframes');
                        $this->setFeature('utf');
                        $this->setFeature('homepage');
                        $this->setFeature('accesskey');
                        if ($this->_minorVersion == 5) {
                            $this->setQuirk('break_disposition_header');
                            $this->setQuirk('broken_multipart_form');
                        }
                        break;

                    case 4:
                        $this->setFeature('javascript', 1.2);
                        $this->setFeature('accesskey');
                        if ($this->_minorVersion > 0) {
                            $this->setFeature('utf');
                        }
                        break;

                    case 3:
                        $this->setFeature('javascript', 1.5);
                        $this->setQuirk('avoid_popup_windows');
                        break;
                }
            } elseif (preg_match('|amaya/([0-9.]+)|', $this->_agent, $version)) {
                $this->setBrowser('amaya');
                $this->_majorVersion = $version[1];
                if (isset($version[2])) {
                    $this->_minorVersion = $version[2];
                }
                if ($this->_majorVersion > 1) {
                    $this->setFeature('mathml');
                    $this->setFeature('svg');
                }
                $this->setFeature('xhtml+xml');
            } elseif (preg_match('|W3C_Validator/([0-9.]+)|', $this->_agent, $version)) {
                $this->setFeature('mathml');
                $this->setFeature('svg');
                $this->setFeature('xhtml+xml');
            } elseif (preg_match('|ANTFresco/([0-9]+)|', $this->_agent, $version)) {
                $this->setBrowser('fresco');
                $this->setFeature('javascript', 1.5);
                $this->setQuirk('avoid_popup_windows');
            } elseif (strpos($this->_lowerAgent, 'avantgo') !== false) {
                $this->setBrowser('avantgo');
                $this->_mobile = true;
            } elseif (preg_match('|Konqueror/([0-9]+)|', $this->_agent, $version) ||
                preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->_agent, $version)) {
                // Konqueror and Apple's Safari both use the KHTML
                // rendering engine.
                $this->setBrowser('konqueror');
                $this->setQuirk('empty_file_input_value');
                $this->setQuirk('no_hidden_overflow_tables');
                $this->_majorVersion = $version[1];
                if (isset($version[2])) {
                    $this->_minorVersion = $version[2];
                }

                if (strpos($this->_agent, 'Safari') !== false &&
                    $this->_majorVersion >= 60) {
                    // Safari.
                    $this->setFeature('utf');
                    $this->setFeature('javascript', 1.4);
                    $this->setFeature('dom');
                    $this->setFeature('iframes');
                    if ($this->_majorVersion > 125 ||
                        ($this->_majorVersion == 125 &&
                            $this->_minorVersion >= 1)) {
                        $this->setFeature('accesskey');
                        $this->setFeature('xmlhttpreq');
                    }
                    if ($this->_majorVersion > 522) {
                        $this->setFeature('svg');
                        $this->setFeature('xhtml+xml');
                    }
                } else {
                    // Konqueror.
                    $this->setFeature('javascript', 1.5);
                    switch ($this->_majorVersion) {
                        case 3:
                            $this->setFeature('dom');
                            $this->setFeature('iframes');
                            $this->setFeature('xhtml+xml');
                            break;
                    }
                }
            } elseif (preg_match('|Mozilla/([0-9.]+)|', $this->_agent, $version)) {
                $this->setBrowser('mozilla');
                $this->setQuirk('must_cache_forms');

                list($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
                switch ($this->_majorVersion) {
                    case 5:
                        if ($this->getPlatform() == 'win') {
                            $this->setQuirk('break_disposition_filename');
                        }
                        $this->setFeature('javascript', 1.4);
                        $this->setFeature('dom');
                        $this->setFeature('accesskey');
                        $this->setFeature('optgroup');
                        $this->setFeature('xmlhttpreq');
                        $this->setFeature('cite');
                        if (preg_match('|rv:(.*)\)|', $this->_agent, $revision)) {
                            if ($revision[1] >= 1) {
                                $this->setFeature('iframes');
                            }
                            if ($revision[1] >= 1.3) {
                                $this->setFeature('rte');
                            }
                            if ($revision[1] >= 1.5) {
                                $this->setFeature('svg');
                                $this->setFeature('mathml');
                                $this->setFeature('xhtml+xml');
                            }
                        }
                        break;

                    case 4:
                        $this->setFeature('javascript', 1.3);
                        $this->setQuirk('buggy_compression');
                        break;

                    case 3:
                    default:
                        $this->setFeature('javascript', 1);
                        $this->setQuirk('buggy_compression');
                        break;
                }
            } elseif (preg_match('|Lynx/([0-9]+)|', $this->_agent, $version)) {
                $this->setBrowser('lynx');
                $this->setFeature('images', false);
                $this->setFeature('frames', false);
                $this->setFeature('javascript', false);
                $this->setQuirk('avoid_popup_windows');
            } elseif (preg_match('|Links \(([0-9]+)|', $this->_agent, $version)) {
                $this->setBrowser('links');
                $this->setFeature('images', false);
                $this->setFeature('frames', false);
                $this->setFeature('javascript', false);
                $this->setQuirk('avoid_popup_windows');
            } elseif (preg_match('|HotJava/([0-9]+)|', $this->_agent, $version)) {
                $this->setBrowser('hotjava');
                $this->setFeature('javascript', false);
            } elseif (strpos($this->_agent, 'UP/') !== false ||
                strpos($this->_agent, 'UP.B') !== false ||
                strpos($this->_agent, 'UP.L') !== false) {
                $this->setBrowser('up');
                $this->setFeature('html', false);
                $this->setFeature('javascript', false);
                $this->setFeature('hdml');
                $this->setFeature('wml');

                if (strpos($this->_agent, 'GUI') !== false &&
                    strpos($this->_agent, 'UP.Link') !== false) {
                    /* The device accepts Openwave GUI extensions for
                     * WML 1.3. Non-UP.Link gateways sometimes have
                     * problems, so exclude them. */
                    $this->setQuirk('ow_gui_1.3');
                }
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Xiino/') !== false) {
                $this->setBrowser('xiino');
                $this->setFeature('hdml');
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Palmscape/') !== false) {
                $this->setBrowser('palmscape');
                $this->setFeature('javascript', false);
                $this->setFeature('hdml');
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Nokia') !== false) {
                $this->setBrowser('nokia');
                $this->setFeature('html', false);
                $this->setFeature('wml');
                $this->setFeature('xhtml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'Ericsson') !== false) {
                $this->setBrowser('ericsson');
                $this->setFeature('html', false);
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lowerAgent, 'wap') !== false) {
                $this->setBrowser('wap');
                $this->setFeature('html', false);
                $this->setFeature('javascript', false);
                $this->setFeature('hdml');
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lowerAgent, 'docomo') !== false ||
                strpos($this->_lowerAgent, 'portalmmm') !== false) {
                $this->setBrowser('imode');
                $this->setFeature('images', false);
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'BlackBerry') !== false) {
                $this->setBrowser('blackberry');
                $this->setFeature('html', false);
                $this->setFeature('javascript', false);
                $this->setFeature('hdml');
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_agent, 'MOT-') !== false) {
                $this->setBrowser('motorola');
                $this->setFeature('html', false);
                $this->setFeature('javascript', false);
                $this->setFeature('hdml');
                $this->setFeature('wml');
                $this->_mobile = true;
            } elseif (strpos($this->_lowerAgent, 'j-') !== false) {
                $this->setBrowser('mml');
                $this->_mobile = true;
            }
        }
    }

    /**
     * Match the platform of the browser.
     *
     * This is a pretty simplistic implementation, but it's intended
     * to let us tell what line breaks to send, so it's good enough
     * for its purpose.
     */
    public function _setPlatform()
    {
        if (strpos($this->_lowerAgent, 'wind') !== false) {
            $this->_platform = 'win';
        } elseif (strpos($this->_lowerAgent, 'mac') !== false) {
            $this->_platform = 'mac';
        } else {
            $this->_platform = 'unix';
        }
    }

    /**
     * Return the currently matched platform.
     *
     * @return string  The user's platform.
     */
    public function getPlatform()   {
        return $this->_platform;
    }

    /**
     * Sets the current browser.
     *
     * @param string $browser  The browser to set as current.
     */
    public function setBrowser($browser) {
        $this->_browser = $browser;
    }

    /**
     * Retrieve the current browser.
     *
     * @return string  The current browser.
     */
    public function getBrowser()    {
        return $this->_browser;
    }

    /**
     * Retrieve the current browser's major version.
     *
     * @return integer  The current browser's major version.
     */
    function getMajor()  {
        return $this->_majorVersion;
    }

    /**
     * Retrieve the current browser's minor version.
     * @return integer  The current browser's minor version.
     */
    function getMinor()  {
        return $this->_minorVersion;
    }

    /**
     * Retrieve the current browser's version.
     * @return string  The current browser's version.
     */
    function getVersion()    {
        return $this->_majorVersion . '.' . $this->_minorVersion;
    }

    /**
     * Return the full browser agent string.
     *
     * @return string  The browser agent string.
     */
    function getAgentString()    {
        return $this->_agent;
    }

    /**
     * Returns the server protocol in use on the current server.
     *
     * @return string  The HTTP server protocol version.
     */
    function getHTTPProtocol()
    {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            if (($pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/'))) {
                return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);
            }
        }
        return null;
    }

    /**
     * Set unique behavior for the current browser.
     *
     * @param string $quirk  The behavior to set.
     * @param string $value  Special behavior parameter.
     */
    function setQuirk($quirk, $value = true) {
        $this->_quirks[$quirk] = $value;
    }

    /**
     * Check unique behavior for the current browser.
     *
     * @param string $quirk  The behavior to check.
     * @return boolean  Does the browser have the behavior set?
     */
    function hasQuirk($quirk) {
        return !empty($this->_quirks[$quirk]);
    }

    /**
     * Retrieve unique behavior for the current browser.
     *
     * @param string $quirk  The behavior to retrieve.
     * @return string  The value for the requested behavior.
     */
    function getQuirk($quirk)
    {
        return isset($this->_quirks[$quirk])
            ? $this->_quirks[$quirk]
            : null;
    }

    /**
     * Set capabilities for the current browser.
     *
     * @param string $feature  The capability to set.
     * @param string $value Special capability parameter.
     */
    function setFeature($feature, $value = true) {
        $this->_features[$feature] = $value;
    }


    /**
     * Check the current browser capabilities.
     * @param string $feature  The capability to check.
     * @return boolean  Does the browser have the capability set?
     */
    function hasFeature($feature)    {
        return !empty($this->_features[$feature]);
    }

    /**
     * Retrieve the current browser capability.
     *
     * @param string $feature  The capability to retrieve.
     * @return string  The value of the requested capability.
     */
    function getFeature($feature)    {
        return isset($this->_features[$feature])
            ? $this->_features[$feature]
            : null;
    }

    /**
     * Determines if a browser can display a given MIME type.
     *
     * @param string $mimetype  The MIME type to check.
     * @return boolean  True if the browser can display the MIME type.
     */
    function isViewable($mimetype)
    {
        $mimetype = strtolower($mimetype);
        list($type, $subtype) = explode('/', $mimetype);

        if (!empty($this->_accept)) {
            $wildcard_match = false;

            if (strpos($this->_accept, $mimetype) !== false) {
                return true;
            }

            if (strpos($this->_accept, '*/*') !== false) {
                $wildcard_match = true;
                if ($type != 'image') {
                    return true;
                }
            }

            /* image/jpeg and image/pjpeg *appear* to be the same
            * entity, but Mozilla doesn't seem to want to accept the
            * latter.  For our purposes, we will treat them the
            * same.
            */
            if ($this->isBrowser('mozilla') &&
                ($mimetype == 'image/pjpeg') &&
                (strpos($this->_accept, 'image/jpeg') !== false)) {
                return true;
            }

            if (!$wildcard_match) {
                return false;
            }
        }

        if (!$this->hasFeature('images') || ($type != 'image')) {
            return false;
        }

        return (in_array($subtype, $this->_images));
    }

    /**
     * Determine if the given browser is the same as the current.
     *
     * @param string $browser  The browser to check.
     * @return boolean  Is the given browser the same as the current?
     */
    function isBrowser($browser)
    {
        return ($this->_browser === $browser);
    }

    /**
     * Determines if the browser is a robot or not.
     *
     * @return boolean  True if browser is a known robot.
     */
    function isRobot()
    {
        foreach ($this->_robots as $robot) {
            if (strpos($this->_agent, $robot) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if we are using a secure (SSL) connection.
     *
     * @return boolean  True if using SSL, false if not.
     */
    function isSSLConnection()
    {
        return ((isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on')) ||
            getenv('SSL_PROTOCOL_VERSION'));
    }


    public static $_request_url;

    public static $_request_uri;

    public static $_request_base;

    public static $_pathinfo;




    public static function method_get()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function base_get()
    {
        if (!is_null(self::$_request_base)) {
            return self::$_request_base;
        }
        $base = self::is_ssl() ? 'https://' : 'http://';
        $base .= self::get_host();
        self::$_request_base = $base;
        return $base;
    }

    public static function url_get()
    {
        if (!is_null(self::$_request_url)) return self::$_request_url;
        $url  = self::is_ssl() ? 'https://' : 'http://';
        $url .= self::get_host();
        $url .= self::uri_get();
        self::$_request_url = $url;
        return $url;
    }

    public static function uri_get()
    {
        if (!is_null(self::$_request_uri)) return self::$_request_uri;

        if (isset($_SERVER['HTTP_X_REWRITE_URL']))
        {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        elseif (isset($_SERVER['REQUEST_URI']))
        {
            $uri = $_SERVER['REQUEST_URI'];
        }
        elseif (isset($_SERVER['ORIG_PATH_INFO']))
        {
            $uri = $_SERVER['ORIG_PATH_INFO'];
            if (! empty($_SERVER['QUERY_STRING']))
            {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        else
        {
            $uri = '';
        }
        self::$_request_uri = $uri;
        return $uri;
    }

    public static function get_querystring()
    {
        return $_SERVER['QUERY_STRING'];
    }

    public static function get_pathinfo()
    {
        if (!is_null(self::$_pathinfo)) return self::$_pathinfo;

        if (!empty($_SERVER['PATH_INFO']))
        {
            self::$_pathinfo = $_SERVER['PATH_INFO'];
            return $_SERVER['PATH_INFO'];
        }
        $pathinfo = substr(self::uri_get(), strlen(self::get_scriptname()));
        if(substr($pathinfo, 0, 1) == '/')
        {
            if ($_SERVER['QUERY_STRING']) $pathinfo = substr($pathinfo, 0, strpos($pathinfo, '?'));
            self::$_pathinfo = $pathinfo;
        }
        return self::$_pathinfo;
    }

    public static function get_scriptname()
    {
        $script = self::get_env('SCRIPT_NAME');
        return $script ? $script : self::get_env('ORIG_SCRIPT_NAME');
    }

    public static function get_referer()
    {
        return self::get_env('HTTP_REFERER');
    }

    public static function get_host()
    {
        $host = self::get_env('HTTP_X_FORWARDED_HOST');
        return $host ? $host : self::get_env('HTTP_HOST');
    }

    public static function get_language()
    {
        return self::get_env('HTTP_ACCEPT_LANGUAGE');
    }

    public static function get_charset()
    {
        return $_SERVER['HTTP_ACCEPT_CHARSET'];
    }

    public static function get_clientip()
    {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
        {
            $ip = getenv('REMOTE_ADDR');
        }
        elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
    }

    public static function get_env($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : (isset($_ENV[$key]) ? $_ENV[$key] : FALSE);
    }

    public static function clean()
    {

    }

    public static function is_ssl()
    {
        return (strtolower(self::get_env('HTTPS')) === 'on' || strtolower(self::get_env('HTTP_SSL_HTTPS')) === 'on' || self::get_env('HTTP_X_FORWARDED_PROTO') == 'https');
    }

    public static function is_XmlHttpRequest()
    {
        return (self::get_env('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    public static function is_post()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function is_get()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function is_ie()
    {
        return strpos(self::get_env('HTTP_USER_AGENT'), 'MSIE') ? TRUE : FALSE;
    }

    public static function is_spider()
    {
        static $is_spider;
        if(!is_null($is_spider)) return $is_spider;
        $browsers = 'msie|netscape|opera|konqueror|mozilla';
        $spiders = 'bot|spider|google|isaac|surveybot|baiduspider|yahoo|sohu-search|yisou|3721|qihoo|daqi|ia_archiver|p.arthur|fast-webcrawler|java|microsoft-atl-native|turnitinbot|webgather|sleipnir|msn';
        if(preg_match("/($browsers)/i", $_SERVER['HTTP_USER_AGENT']))
        {
            $is_spider = FALSE;
        }
        elseif(preg_match("/($spiders)/i", $_SERVER['HTTP_USER_AGENT']))
        {
            $is_spider = TRUE;
        }
        return $is_spider;
    }

    protected $_uri = '';
    protected $_scheme = '';
    protected $_host = '';
    protected $_port = 80;
    protected $_user = '';
    protected $_pass = '';
    protected $_path = null;
    protected $_query = null;
    protected $_fragment = null;
    protected $_vars = [];



    static public function base($is_full = true)
    {
        static $base;
        if (!isset($base)) {
            $uri	        = static::i();
            $base['prefix'] = $uri->toString( array('scheme', 'host', 'port'));
            if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
                $base['path'] =  rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            } else {
                $base['path'] =  rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            }
        }
        return $is_full ? $base['prefix'].$base['path'].'/' : $base['path'];
    }

    static public function root($pathonly = false, $path = null)
    {
        static $root;
        if(!isset($root)) {
            $uri	        = static::i(uri::base());
            $root['prefix'] = $uri->toString( array('scheme', 'host', 'port') );
            $root['path']   = rtrim($uri->toString( array('path') ), '/\\');
        }

        if(isset($path)) {
            $root['path']    = $path;
        }
        return $pathonly === false ? $root['prefix'].$root['path'].'/' : $root['path'];
    }

    static public function current()
    {
        static $current;
        if (is_null($current)) {
            $uri	 = static::i();
            $current = $uri->toString(array('scheme', 'host', 'port', 'path'));
        }
        return $current;
    }

    static  public function is_internal($url)
    {
        $uri  = static::i($url);
        $base = $uri->toString(['scheme', 'host', 'port', 'path']);
        $host = $uri->toString(['scheme', 'host', 'port']);
        if(stripos($base, uri::base()) !== 0 && !empty($host)) {
            return false;
        }
        return true;
    }



    public function parse($uri)
    {
        $retval = false;
        $this->_uri = $uri;
        if($_parts = parse_url($uri)) {
            $retval = true;
        }

        if(isset($_parts['query']) && strpos($_parts['query'], '&amp;')) {
            $_parts['query'] = str_replace('&amp;', '&', $_parts['query']);
        }
        $this->_scheme = isset ($_parts['scheme']) ? $_parts['scheme'] : null;
        $this->_user = isset ($_parts['user']) ? $_parts['user'] : null;
        $this->_pass = isset ($_parts['pass']) ? $_parts['pass'] : null;
        $this->_host = isset ($_parts['host']) ? $_parts['host'] : null;
        $this->_port = isset ($_parts['port']) ? $_parts['port'] : null;
        $this->_path = isset ($_parts['path']) ? $_parts['path'] : null;
        $this->_query = isset ($_parts['query'])? $_parts['query'] : null;
        $this->_fragment = isset ($_parts['fragment']) ? $_parts['fragment'] : null;
        if(isset($_parts['query'])) {
            parse_str($_parts['query'], $this->_vars);
        }
        return $retval;
    }

    public function toString($parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'])
    {
        $query = $this->get_query();
        $uri   = '';
        $uri .= in_array('scheme', $parts)  ? (!empty($this->_scheme) ? $this->_scheme.'://' : '') : '';
        $uri .= in_array('user', $parts)	? $this->_user : '';
        $uri .= in_array('pass', $parts)	? (!empty($this->_pass) ? ':' : '') .$this->_pass. (!empty($this->_user) ? '@' : '') : '';
        $uri .= in_array('host', $parts)	? $this->_host : '';
        $uri .= in_array('port', $parts)	? (!empty($this->_port) ? ':' : '').$this->_port : '';
        $uri .= in_array('path', $parts)	? $this->_path : '';
        $uri .= in_array('query', $parts)	? (!empty($query) ? '?'.$query : '') : '';
        $uri .= in_array('fragment', $parts)? (!empty($this->_fragment) ? '#'.$this->_fragment : '') : '';
        return $uri;
    }

    public function set_var($name, $value)
    {
        $this->_vars[$name] = $value;
        $this->_query = null;
    }

    public function get_var($name, $default = null)
    {
        return isset($this->_vars[$name]) ? $this->_vars[$name] : $default;
    }

    public function del_var($name)
    {
        if (isset($this->_vars[$name]))
        {
            unset($this->_vars[$name]);
            $this->_query = null;
        }
    }

    public function set_query($query)
    {
        if(!is_array($query))
        {
            if(strpos($query, '&amp;') !== false)
            {
                $query = str_replace('&amp;', '&', $query);
            }
            parse_str($query, $this->_vars);
        }
        else
        {
            $this->_vars = $query;
        }
        $this->_query = null;
    }

    public function get_query()
    {
        if(is_null($this->_query))
        {
            $this->_query = http_build_query($this->_vars);
        }
        return $this->_query;
    }

    public function get_scheme()
    {
        return $this->_scheme;
    }

    public function set_scheme($scheme)
    {
        $this->_scheme = $scheme;
    }

    public function get_user()
    {
        return $this->_user;
    }

    public function set_user($user)
    {
        $this->_user = $user;
    }

    public function get_pass()
    {
        return $this->_pass;
    }

    public function set_pass($pass)
    {
        $this->_pass = $pass;
    }

    public function get_host2()
    {
        return $this->_host;
    }

    public function set_host2($host)
    {
        $this->_host = $host;
    }

    public function get_port()
    {
        return isset($this->_port) ? $this->_port : null;
    }

    public function set_port($port)
    {
        $this->_port = $port;
    }

    public function get_path()
    {
        return $this->_path;
    }

    public function set_path($path)
    {
        $this->_path = $this->_clean_path($path);
    }

    public function get_fragment()
    {
        return $this->_fragment;
    }

    public function set_fragment($anchor)
    {
        $this->_fragment = $anchor;
    }

    public function ssl_is()
    {
        return $this->get_scheme() == 'https' ? true : false;
    }




    function _clean_path($path)
    {
        $path = preg_replace('#(/+)#', '/', $path);
        if(strpos($path, '.') === false) return $path;
        $path = explode('/', $path);
        for ($i = 0; $i < count($path); $i++) {
            if ($path[$i] == '.') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;
            } elseif ($path[$i] == '..') {
                if ($i == 1 AND $path[0] == '') {
                    unset ($path[$i]);
                    $path = array_values($path);
                    $i--;
                } elseif ($i > 1 OR ($i == 1 AND $path[0] != '')) {
                    unset($path[$i]);
                    unset($path[$i-1]);
                    $path = array_values($path);
                    $i -= 2;
                }
            } else {
                continue;
            }
        }
        return implode('/', $path);
    }
}
