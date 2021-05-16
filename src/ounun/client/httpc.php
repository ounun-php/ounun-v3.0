<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\client;


class httpc
{
    /**
     * 请求
     *
     * @param string $url
     * @param array $headers header格式必须为 “键: 值”
     * @param array $options
     * @param string|null $post_data
     * @return array
     */
    public static function _curl_request(string $url, $headers = [], $options = [], $post_data = null)
    {
        $headers = is_array($headers) ? $headers : [];
        $options = is_array($options) ? $options : [];

        $options['timeout'] = intval($options['timeout']);
        $options['timeout'] = $options['timeout'] > 0 ? $options['timeout'] : 20;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($options['nobody']) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        if ($options['useragent']) {
            curl_setopt($ch, CURLOPT_USERAGENT, $options['useragent']);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($headers) && count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (isset($post_data)) {

            curl_setopt($ch, CURLOPT_POST, 1);
            if (is_array($post_data)) {

                $post_data = http_build_query($post_data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if (!empty($options['proxy']) && !empty($options['proxy']['ip'])) {

            $proxyType = null;
            switch ($options['proxy']['type']) {
                case 'socks4':
                    $proxyType = CURLPROXY_SOCKS4;
                    break;
                case 'socks5':
                    $proxyType = CURLPROXY_SOCKS5;
                    break;
                default:
                    $proxyType = CURLPROXY_HTTP;
                    break;
            }

            curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType);

            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $options['proxy']['port']);
            if (!empty($options['proxy']['user'])) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $options['proxy']['user'] . ':' . $options['proxy']['pwd']);
            }
        }

        $body = curl_exec($ch);

        $header_pos = strpos($body, "\r\n\r\n");
        if ($header_pos !== false) {
            $header_pos = intval($header_pos) + strlen("\r\n\r\n");
        }
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header_size = intval($header_size);
        if ($header_size < $header_pos) {
            $header_size = $header_pos;
        }

        $rs_header = substr($body, 0, $header_size);
        $rs_body   = substr($body, $header_size);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $code = intval($code);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return succeed(['code'=>$code,'body'=>$rs_body,'header'=>$rs_header]);
        } else {
            return error('http error',1,['code'=>$code,'body'=>$rs_body,'header'=>$rs_header]);
        }
    }

    public static function head($url, $headers = [], $options = [])
    {
        $options           = is_array($options) ? $options : [];
        $options['nobody'] = 1;
        return self::_curl_request($url, $headers, $options);
    }

    public static function get($url, $headers = [], $options = [])
    {
        return self::_curl_request($url, $headers, $options);
    }

    public static function post($url, $headers = [], $options = [], $data = null)
    {
        return self::_curl_request($url, $headers, $options, $data ? $data : '');
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
    protected static function _stream(string $type, string $host, string $page, $port = 80, $data = [], $cookie = [], $timeout = 3)
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
    static public function stream_get( string $url,array $cookie,int $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query'] ? '?' . $info['query'] : '');
        $port = $info['port'] ? $info['port'] : 80;
        return static::_stream('GET', $host, $page, $port, null, $cookie, $timeout);
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
     * @param string $referer
     * @param int $loop_max
     * @param int $sleep_time_seconds
     * @param int $filesize_min
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
     * 获取网络文件，并保存
     *
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
     * URL请求
     *
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
     *
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
     *
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
     *
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
     *
     * @param array $data
     * @return array
     */
    protected static function _curl_https_post_build(array $data = []): array
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
}
