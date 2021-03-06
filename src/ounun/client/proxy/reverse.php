<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\client\proxy;


use ounun\client\httpc;

class reverse
{
    /** @var int 块大小 100k */
    const Chunk_Size = 102400;

    /** @var bool 是否ssl */
    public bool $is_ssl = false;
    /** @var bool false: 高速，不转发 true:正常代理 */
    public bool $is_header = false;
    /** @var bool false: 数据缓存后，内容直接输出  true:数据缓存后，再跳转 */
    // public $is_jump = false;

    /** @var string 缓存数据 */
    public string $content = '';

    /** @var string 服务器版本 */
    protected string $_server_version = 'Ounun.org Download Server';
    /** @var string 服务器源网址root */
    protected string $_server_url_root = '';
    /** @var string 服务器源path */
    protected string $_server_path = '';
    /** @var string 服务器源file */
    protected string $_server_file = '';

    /** @var int 端口 */
    protected int $_port = 80;
    /** @var string 主机名 */
    protected string $_host = '';
    /** @var string 服务器ip */
    protected string $_ip = '127.0.0.1';

    /** @var int 在服务器端 缓存时间 0:默认已缓存就不更新了 */
    protected int $_cache_life = 0;
    /** @var int 在客户端   缓存时间 */
    protected int $_cache_time = 72000;
    /** @var string 遇到cc的参数就更新缓存; */
    protected string $_cache_clean_param = 'clean';
    /** @var string 根目录 */
    protected string $_cache_path_root = '';
    /** @var string 本地目录名称 */
    protected string $_cache_filename = '';

    /** @var int */
    protected int $_http_code = 200;
    /** @var int */
    protected int $_last_modified = 0;
    /** @var string */
    protected string $_http_if_modified_304 = '';

    /** @var array 替换数据[0],[1] */
    protected array $_data_replace = [];

    /**
     * reverse constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if ($config['server_version']) {
            $this->_server_version = $config['server_version'];
        }
        if ($config['server_url_root']) {
            $this->_server_url_root = $config['server_url_root'];
        }
        if ($config['server_path']) {
            $this->_server_path = $config['server_path'];
        }
        if ($config['server_file']) {
            $this->_server_file = $config['server_file'];
        }

        if ($config['host']) {
            $this->_host = $config['host'];
        }
        if ($config['port']) {
            $this->_port = $config['port'];
        }
        if ($config['ip']) {
            $this->_ip = $config['ip'];
        }

        // cache
        if ($config['cache_time']) {
            $this->_cache_time = $config['cache_time'];
        }
        if ($config['cache_life']) {
            $this->_cache_life = $config['cache_life'];
        }
        if ($config['cache_clean_param']) {
            $this->_cache_clean_param = $config['cache_clean_param'];
        }
        if ($config['cache_path_root']) {
            $this->_cache_path_root = $config['cache_path_root'];
        }
        if ($config['cache_filename']) {
            $this->_cache_filename = $config['cache_filename'];
        }

        $this->_http_if_modified_304 = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';
//      $this->_http_code            = 200;

        if ($config['is_ssl']) {
            $this->is_ssl = $config['is_ssl'];
        }
        if ($config['is_header']) {
            $this->is_header = $config['is_header'];
        }
    }

    /**
     * 查看文件是否存在
     * @return array
     */
    public function cache_check(): array
    {
        if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if ('GET' == $_SERVER['REQUEST_METHOD']) {
                return $this->cache_get();
            } elseif ('POST' == $_SERVER['REQUEST_METHOD']) {
                // return $this->cache_post();
                return $this->cache_get();
            } else {
                return error('404-REQUEST_METHOD', 404, 404);
            }
        } else {
            return succeed(304);
        }
    }

    /**
     * @return array
     */
    public function cache_get(): array
    {
        $local_filename = $this->local_filename();
        if (file_exists($local_filename)) {
            $mtime                = filemtime($local_filename);
            $this->_last_modified = gmdate("D, d M Y H:i:s", $mtime) . " GMT";
            // $filesize = filesize($local_filename);
            if ($this->_cache_clean_param && isset($req[$this->_cache_clean_param])) {//含有cc参数
                unlink($local_filename);
            } elseif ($this->_cache_life <= 0) {
                return succeed(304);
            } elseif ($this->_cache_life && $mtime + $this->_cache_life < time()) {
                return succeed(304);
            }
        }
        if (empty($this->_server_url_root)) {
            return error('404-empty-server_url_root:' . $this->server_url(), 404, 404);
        }
        $server_url    = $this->server_url();
        $this->content = httpc::file_get_contents_loop($server_url, '', 1);
        if ($this->content) {
            $this->_http_code = 200;
            $this->write($local_filename, $this->content);
            return succeed(200);
        }
        return error('404-error-file_get_contents', 404, 404);
    }

    /**
     * @param string $local_filename
     * @param string $content
     * @return false|int
     */
    protected function write(string $local_filename, string $content)
    {
        $local_dir = dirname($local_filename);
        if (!file_exists($local_dir)) {
            mkdir($local_dir, 0777, true);
        }
        if ($this->_data_replace) {
            $content = str_replace($this->_data_replace[0], $this->_data_replace[1], $content);
        }
        $rs = file_put_contents($local_filename, $content);
        if ($rs) {
            chmod($local_filename, 0777);
            $mtime                = filemtime($local_filename);
            $this->_last_modified = gmdate("D, d M Y H:i:s", $mtime) . " GMT";
        }
        return $rs;
    }


    /**
     * @param string $local_filename
     * @param bool $is_sendfile
     * @param int $time_expired
     */
    public function output(string $local_filename, bool $is_sendfile = false, int $time_expired = 0)
    {
        $time_expired = $time_expired == 0 ? time() : $time_expired;
        if ($this->_http_if_modified_304) {
            $time_current_string = gmdate("D, d M Y H:i:s", $time_expired);
            header("HTTP/1.1 304 Not Modified");
            header("Date: Wed, {$time_current_string} GMT");
            header("Last-Modified: {$this->_last_modified}");
            header("Server: {$this->_server_version}");
        } else {
            if ($is_sendfile || empty($this->content)) {
                static::output_sendfile($local_filename, $this->_cache_time, $this->_last_modified, $this->_server_version, $this->is_header, $time_expired);
            } else {
                static::output_content($this->content, $local_filename, $this->_cache_time, $this->_last_modified, $this->_server_version, $this->is_header, $time_expired);
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
    static public function output_content(string $content, string $local_filename, int $time_cache, string $last_modified, string $server_version, bool $is_header, int $time_expired = 0)
    {
        static::_header($local_filename,$time_cache,$last_modified,$server_version,$is_header,$time_expired);
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
    static public function output_sendfile(string $local_filename, int $time_cache, string $last_modified, string $server_version, bool $is_header, int $time_expired = 0)
    {
        static::_header($local_filename,$time_cache,$last_modified,$server_version,$is_header,$time_expired);
        readfile($local_filename);
        exit;
    }

    static protected function _header(string $local_filename, int $time_cache, string $last_modified, string $server_version, bool $is_header, int $time_expired = 0)
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
    }

    /**
     * 远程地址
     * @return string
     */
    public function server_url(): string
    {
        return $this->_server_url_root . '/' . ($this->_server_path ? $this->_server_path . '/' : '') . $this->_server_file;
    }

    /**
     * 本地文件名
     * @return string
     */
    public function local_filename(): string
    {
        return $this->_cache_path_root . $this->_cache_filename;
    }


    /**
     * @return array
     */
    public static function headers(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } elseif ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } elseif ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
//            } elseif (stristr($name, "X-Requested-With")) {
//                $headers["X-Requested-With"] = $value;
            }
        }

        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $headers["X-Forwarded-For"] = $_SERVER['REMOTE_ADDR'];
        } else {
            $headers["X-Forwarded-For"] = $_SERVER['HTTP_X_FORWARDED_FOR'] . ", " . $_SERVER['REMOTE_ADDR'];
        }
        return $headers;
    }

    /**
     * @return string
     */
    public static function cookie(): string
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
    public static function host(string $host, int $port = 80, bool $is_ssl = false): string
    {
        if ($is_ssl) {
            if (empty($port) || $port == 443) {
                return 'https://' . $host;
            } else {
                return 'https://' . $host . ':' . $port;
            }
        } else {
            if (empty($port) || $port == 80) {
                return 'http://' . $host;
            } else {
                return 'http://' . $host . ':' . $port;
            }
        }
    }
}


