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

    function __construct()
    {
        $this->method     = 'GET';
        $this->cookie     = '';
        $this->post       = '';
        $this->header     = '';
        $this->error_code = 0;
        $this->error_msg  = '';
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
}
