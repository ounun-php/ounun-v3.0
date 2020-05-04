<?php
/** Ounun版本号 */
define('Ounun_Version', '3.2.1');
/** 是否Cli - 环境常量 */
define('Is_Cli', PHP_SAPI == 'cli' ? true : false);
/** 是否Win - 环境常量 */
define('Is_Win', strpos(PHP_OS, 'WIN') !== false);
/** root根目录 **/
defined('Dir_Root') || define('Dir_Root', realpath(__DIR__ . '/../') . '/');
/** libs库文件目录 **/
defined('Dir_Ounun') || define('Dir_Ounun', __DIR__ . '/');
/** libs目录 **/
defined('Dir_Vendor') || define('Dir_Vendor', Dir_Root . 'vendor/');
/** data目录 **/
defined('Dir_Extend') || define('Dir_Extend', Dir_Root . 'extend/');
/** template目录 **/
//defined('Dir_Template') || define('Dir_Template', Dir_Root . 'template/');
/** data目录 **/
defined('Dir_Data') || define('Dir_Data', Dir_Root . 'data/');
/** cache目录 **/
defined('Dir_Cache') || define('Dir_Cache', Dir_Data . 'cache/');
/** app目录 **/
defined('Dir_App') || define('Dir_App', Dir_Root . 'app/');
/** Environment目录 **/
defined('Environment') || define('Environment', environment());
if (Environment) {
    /** 开始时间戳 **/
    define('Ounun_Start_Time', microtime(true));
    /** 开始内存量 **/
    define('Ounun_Start_Memory', memory_get_usage());
}

/**
 * 得到访客的IP
 * @return string IP
 */
function ip(): string
{
    static $hdr_ip;
    if (empty($hdr_ip)) {
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $hdr_ip = stripslashes($_SERVER['HTTP_CDN_SRC_IP']);
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $hdr_ip = stripslashes($_SERVER['HTTP_CLIENT_IP']);
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $hdr_ip = stripslashes($_SERVER['HTTP_X_FORWARDED_FOR']);
                } else {
                    $hdr_ip = stripslashes($_SERVER['REMOTE_ADDR']);
                    if (empty($hdr_ip)) {
                        $hdr_ip = '127.0.0.1';
                    }
                }
            }
        }
    }
    return $hdr_ip;
}


/**
 * 输出带参数的URL
 * @param string $url URL
 * @param array $data_query 数据
 * @param array $replace_ext 要替换的数据
 * @param array $skip 忽略的数据 如:page
 * @return string
 */
function url_build_query(string $url, array $data_query, array $replace_ext = [], array $skip = []): string
{
    $rs = [];
    if (is_array($data_query)) {
        if ($replace_ext && is_array($replace_ext)) {
            foreach ($replace_ext as $key => $value) {
                $data_query[$key] = $value;
            }
        }
        if ($skip && is_array($skip)) {
            foreach ($skip as $key => $value) {
                if ($value) {
                    if (is_array($value) && in_array($data_query[$key], $value, true)) {
                        unset($data_query[$key]);
                    } elseif ($value == $data_query[$key]) {
                        unset($data_query[$key]);
                    }
                } else {
                    unset($data_query[$key]);
                }
            }
        }
        $rs     = [];
        $rs_str = '';
        foreach ($data_query as $key => $value) {
            if ('{page}' === $value) {
                $rs_str = $key . '={page}';
            } elseif (is_array($value)) {
                foreach ($value as $k2 => $v2) {
                    $rs[] = $key . '[' . $k2 . ']=' . urlencode($v2);
                }
            } elseif ($value || 0 === $value || '0' === $value) {
                $rs[] = $key . '=' . urlencode($value);
            }
        }
        // 已保正page 是最后项
        if ($rs_str) {
            $rs[] = $rs_str;
        }
    }
    $url = trim($url);
    if ($rs) {
        $len = strlen($url);
        if ($url && $len > 0) {
            if (strpos($url, '?') === false) {
                return $url . '?' . implode('&', $rs);
            } elseif ('?' === $url[$len - 1]) {
                return $url . implode('&', $rs);
            }
            return $url . '&' . implode('&', $rs);
        }
        return implode('&', $rs);
    }
    return $url;
}

/**
 * 得到 原生 URL(去问号后的 QUERY_STRING)
 * @param $uri
 * @return string URL
 */
function url_original(string $uri = ''): string
{
    if ('' == $uri) {
        $uri = $_SERVER['REQUEST_URI'];
    }
    $tmp = explode('?', $uri, 2);
    return $tmp[0];
}

/**
 * 通过uri得到mod
 * @param $uri string
 * @return array
 */
function url_to_mod(string $uri): array
{
    $uri = \explode('/', $uri, 2);
    $uri = \explode('.', urldecode($uri[1]), 2);
    $uri = \explode('/', $uri[0]);
    $mod = [];
    foreach ($uri as $v) {
        $v !== '' && $mod[] = $v;
    }
    return $mod;
}

/**
 * URL去重
 * @param  $url_original  string     网址
 * @param  $ext_req       bool       网址可否带参加数
 * @param  $domain        string     是否捡查 域名
 */
function url_check(string $url_original = "", bool $ext_req = true, string $domain = '')
{
    // URL去重
    $url       = explode('?', $_SERVER['REQUEST_URI'], 2);
    $url_reset = '';
    if (false == $ext_req && $url[1]) {
        $url_reset = $url_original;
    } elseif ($url_original != $url[0]) {
        $url_reset = $url_original;
        if ($ext_req && $url[1]) {
            $url_reset = "{$url_reset}?{$url[1]}";
        }
    }
    // echo("\$url_reset:{$url_reset} \$url_original:{$url_original}\n");
    // exit("\$domain:{$domain}\n");
    // 域名
    if ($domain && $domain != $_SERVER['HTTP_HOST']) {
        // $domain  = $_SERVER['HTTP_HOST'];
        $url_reset = $url_reset ? $url_reset : $_SERVER['REQUEST_URI'];
        $url_reset = "//{$domain}{$url_reset}";
        // exit("\$url_reset:{$url_reset} \$domain:{$domain}\n");
        go_url($url_reset, false, 301);
    } else if ($url_reset) {
        // exit("\$url_reset:{$url_reset}\n");
        go_url($url_reset, false, 301);
    }
    // exit("\$domain:{$domain}\n");
}

/**
 * @param string $url1
 * @param string $url2
 * @param string $note
 * @param bool $top
 */
function go_note(string $url1, string $url2, string $note, bool $top = false): void
{
    $top  = "\t" . ($top ? 'window.top.' : '');
    $note = $note ? $note : '点击“确定”继续操作  点击“取消” 中止操作';
    echo '<script type="text/javascript">' . "\n";
    if ($url2) {
        $url1 = $top . "location.href='{$url1}';\n";
        $url2 = $top . "location.href='{$url2}';\n";
        echo 'if(window.confirm(' . json_encode($note, JSON_UNESCAPED_UNICODE) . ')){' . "\n" . $url1 . '}else{' . "\n" . $url2 . '}' . "\n";
    } else {
        $url1 = $top . "location.href='{$url1}';\n";
        echo 'if(window.confirm(' . json_encode($note, JSON_UNESCAPED_UNICODE) . ')){' . "\n" . $url1 . '};' . "\n";
    }
    echo '</script>' . "\n";
    exit();
}

/**
 * @param $url
 * @param bool $top
 * @param int $head_code
 * @param int $delay 延时跳转(单位秒)
 */
function go_url(string $url, bool $top = false, int $head_code = 302, int $delay = 0): void
{
    if ($top) {
        echo '<script type="text/javascript">' . "\n";
        echo "window.top.location.href='{$url}';\n";
        echo '</script>' . "\n";
    } else {
        if (!headers_sent() && 0 == $delay) {
            header('Location: ' . $url, null, $head_code);
        } else {
            echo '<meta http-equiv="refresh" content="' . ((int)$delay) . ';url=' . $url . '">';
        }
    }
    exit();
}

/**
 * 返回
 */
function go_back(): void
{
    echo '<script type="text/javascript">', "\n", 'window.history.go(-1);', "\n", '</script>', "\n";
    exit();
}

/**
 * @param $msg
 * @param $url
 */
function go_msg(string $msg, string $url = ''): void
{
    if ($url) {
        exit(msg($msg) . '<meta http-equiv="refresh" content="0.5;url=' . $url . '">');
    } else {
        echo msg($msg);
        go_back();
    }
}


/**
 * 彈出alert對話框
 * @param string $msg
 * @param bool $outer
 * @param bool $meta
 * @return string
 */
function msg(string $msg, bool $outer = true, $meta = true): string
{
    $rs = "\n" . 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');' . "\n";
    if ($outer) {
        if ($meta) {
            $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        } else {
            $mt = '';
        }
        $rs = $mt . '<script type="text/javascript">' . "\n" . $rs . "\n" . '</script>' . "\n";
    }
    return $rs;
}

/**
 * 出错提示错
 * @param string $msg
 * @param bool $close
 */
function msg_close(string $msg, bool $close = false): void
{
    $rs = "\n" . 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');' . "\n";
    $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
    $rs = $mt . '<script type="text/javascript">' . "\n" . $rs . "\n" . '</script>' . "\n";
    echo $rs;
    if ($close) {
        // 本页自动关闭.
        echo '<script type="text/javascript">window.opener = null; window.open("", "_self", ""); window.close(); </script>';
    }
    exit();
}

/**
 * 判断服务器是否是HTTPS连接
 * @return bool
 */
function https_is()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}

/**
 * 返回一个错误
 * @param string $msg
 * @param int $status
 * @param mixed $data
 * @param array $extend 延伸数据
 * @return array
 */
function error(string $msg = '', int $status = 1, $data = null, $extend = [])
{
    $rs = [
        'msg'    => $msg,
        'status' => $status,
    ];
    if ($data) {
        $rs['data'] = $data;
    }
    if ($extend) {
        $rs = array_merge($extend, $rs);
    }
    return $rs;
}

/**
 * 确认是否错误 数据
 * @param $data
 * @return bool
 */
function error_is($data)
{
    if (empty($data) || !is_array($data) || !array_key_exists('status', $data) || (array_key_exists('status', $data) && $data['status'] == 0)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 返回错误提示信息
 * @param $data
 * @return string
 */
function error_message($data): string
{
    return $data['msg'];
}

/**
 * 返回错误代码
 * @param $data
 * @return int
 */
function error_code($data): int
{
    return $data['status'];
}

/**
 * @param mixed $data
 * @param string $message
 * @param array $extend 延伸数据
 * @return array
 */
function succeed($data, string $message = '', $extend = [])
{
    if ($extend) {
        return array_merge($extend, [
            'msg'    => $message,
            'status' => 0,
            'data'   => $data
        ]);
    }
    return [
        'msg'    => $message,
        'status' => 0,
        'data'   => $data
    ];
}

/**
 * 返回 数据
 * @param $data
 * @return mixed
 */
function succeed_data($data)
{
    return $data['data'];
}

/**
 * Ajax方式返回数据到客户端
 * @param mixed $data 要返回的数据
 * @param string $type AJAX返回数据格式
 * @param string $jsonp_callback
 * @param int $json_options 传递给json_encode的option参数
 */
function out($data, string $type = '', string $jsonp_callback = '', int $json_options = JSON_UNESCAPED_UNICODE)
{
    if (empty($type)) {
        $type = \ounun\c::Format_Json;
    }
    switch ($type) {
        // 返回JSON数据格式到客户端 包含状态信息
        case \ounun\c::Format_Json :
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data, $json_options));
        // 返回xml格式数据
        case \ounun\c::Format_Xml :
            header('Content-Type:text/xml; charset=utf-8');
            exit(\ounun\utils\db::xml_encode($data));
        // 返回JSON数据格式到客户端 包含状态信息
        case \ounun\c::Format_Jsonp:
            header('Content-Type:application/javascript; charset=utf-8');
            if (empty($jsonp_callback)) {
                $jsonp_callback = (isset($_GET['jsonp_callback']) && $_GET['jsonp_callback']) ? $_GET['jsonp_callback'] : 'jsonp_callback';
            }
            exit($jsonp_callback . '(' . json_encode($data, $json_options) . ');');
        // 返回可执行的js脚本
        case  \ounun\c::Format_JS :
        case  \ounun\c::Format_Eval :
            header('Content-Type:application/javascript; charset=utf-8');
            exit($data);
        // 返回可执行的js脚本
        // case \ounun\mvc\c::Format_Html :
        default :
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}

/**
 * 获得 json字符串数据
 * @param $data
 * @return string
 */
function json_encode_unescaped($data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/**
 * 对 json格式的字符串进行解码
 * @param string $json_string
 * @return mixed
 */
function json_decode_array(?string $json_string)
{
    return json_decode($json_string, true);
}

/**
 * 获得 extend数据php
 * @param string $extend_string
 * @return array|mixed
 */
function extend_decode_php(string $extend_string)
{
    $ext = [];
    if ($extend_string) {
        $ext = unserialize($extend_string);
    }
    return $ext;
}

/**
 * 获得 extend数据json
 * @param string $extend_string
 * @return array|mixed
 */
function extend_decode_json(string $extend_string)
{
    $extend = [];
    if ($extend_string) {
        $extend = json_decode($extend_string, true);
    }
    return $extend;
}

/**
 * 对字符串进行编码，这样可以安全地通过URL
 * @param string $string to encode
 * @return string
 */
function base64_url_encode(string $string = null): string
{
    return strtr(base64_encode($string), '+/=', '-_~');
}

/**
 * 解码一个 URL传递的字符串
 * @param string $string to decode
 * @return string
 */
function base64_url_decode(string $string = null): string
{
    return base64_decode(strtr($string, '-_~', '+/='));
}

/**
 * 编号 转 字符串
 * @param  $id int to encode
 * @return string
 */
function short_url_encode(int $id = 0): string
{
    if ($id < 10) {
        return (string)$id;
    }
    $show = '';
    while ($id > 0) {
        $s    = $id % 62;
        $show = ($s > 35 ? chr($s + 61) : ($s > 9 ? chr($s + 55) : $s)) . $show;
        $id   = floor($id / 62);
    }
    return $show;
}

/**
 * 字符串 转 编号
 * @param  $string string 字符串
 * @return int
 */
function short_url_decode(string $string = ''): int
{
    $p = 0;
    while ($string !== '') {
        $s      = substr($string, 0, 1);
        $n      = is_numeric($s) ? $s : ord($s);
        $p      = $p * 62 + (($n >= 97) ? ($n - 61) : ($n >= 65 ? $n - 55 : $n));
        $string = substr($string, 1);
    }
    return $p;
}

/**
 * HTTP缓存控制
 * @param int $expires 缓存时间 0:为不缓存 单位:s
 * @param string $etag ETag
 * @param int $last_modified 最后更新时间
 * @param string $content_type 文件类型
 */
function expires(int $expires = 0, string $etag = '', int $last_modified = 0, string $content_type = '')
{
    if ($content_type) {
        header('Content-Type: ' . $content_type);
    }
    if ($expires > 0) {
        $time = time();
        header("Expires: " . gmdate("D, d M Y H:i:s", $time + $expires) . " GMT");
        header("Cache-Control: max-age=" . $expires);
        if ($last_modified) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified) . " GMT");
        }
        if ($etag) {
            if ($etag == $_SERVER["HTTP_IF_NONE_MATCH"]) {
                header("Etag: " . $etag, true, 304);
                exit();
            } else {
                header("Etag: " . $etag);
            }
        }
    } else {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
    }
}

/**
 * error 404
 * @param string $msg
 */
function error404(string $msg = ''): void
{
    header('HTTP/1.1 404 Not Found');
    exit('<html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>404 Not Found</title>
            </head>
            <body bgcolor="white">
                <div align="center">
                    <h1>404 Not Found</h1>
                </div>
                <hr>
                <div align="center"><a href="' . \ounun::$root_www . '">返回网站首页</a></div>
                ' . ($msg ? '<div style="border: #EEEEEE 1px solid;padding: 5px;color: grey;margin-top: 20px;">' . $msg . '</div>' : '') . '
            </body>
            </html>
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- ' . \ounun::$app_name . ' -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->' . "\n");
}

/**
 * 获得libs Data数据
 *
 * @param string $filename
 * @return mixed
 */
function data(string $filename)
{
    if (file_exists($filename)) {
        return require $filename;
    }
    return null;
}

/**
 * 重试指定次数的操作。
 * Retry an operation a given number of times.
 *
 * @param int $times
 * @param callable $callback
 * @param int $sleep
 * @return mixed
 *
 * @throws \Exception
 */
function retry($times, callable $callback, $sleep = 0)
{
    $times--;
    beginning:
    try {
        return $callback();
    } catch (Exception $e) {
        if (!$times) {
            throw $e;
        }
        $times--;
        if ($sleep) {
            usleep($sleep * 1000);
        }
        goto beginning;
    }
}

/**
 * 当前开发环境
 *
 * @return string '','2','-dev'
 */
function environment()
{
    if (isset($GLOBALS['_environment_'])) {
        return $GLOBALS['_environment_'];
    }
    /** @var string $env_file 读取环境配制 */
    $env_file = Dir_Root . '.environment.php';
    if (is_file($env_file)) {
        $ini = require $env_file;
        if (empty($ini)) {
            $GLOBALS['_environment_'] = '';
        } else {
            $GLOBALS['_environment_'] = ($ini && $ini['global'] && $ini['global']['environment']) ? $ini['global']['environment'] : '';
            \ounun::environment_set($ini);
        }
    } else {
        $GLOBALS['_environment_'] = '2';
    }
    return $GLOBALS['_environment_'];
}

/**
 * 构造模块基类
 * Class ViewBase
 *
 * @package ounun
 */
abstract class v
{
    /** @var \ounun\db\pdo DB */
    public static $db_v;

    /** @return \ounun\db\pdo DB */
    public static function db_v_get()
    {
        if (empty(static::$db_v)) {
            static::$db_v = \ounun\db\pdo::i(\ounun::database_default_get());
        }
        return static::$db_v;
    }

    /** @var int cache_html_time */
    public static $cache_html_time = 2678400; // 31天

    /** @var bool html_trim */
    public static $cache_html_trim = true;

    /** @var ounun\cache\html cache_html */
    public static $cache_html;

    /**
     * Cache
     * @param $key
     */
    public function cache_html($key)
    {
        if ('' == Environment && \ounun::$global['cache_html']) {
            $cfg                = \ounun::$global['cache_html'];
            $cfg['mod']         = 'html_' . \ounun::$app_name . '_' . \ounun::$tpl_style . '_' . \ounun::$tpl_type;
            $key2               = \ounun::$app_name . '_' . \ounun::$tpl_style . '_' . \ounun::$tpl_type . '_' . $key;
            $debug              = \ounun::$global['debug'];
            $debug              = $debug && isset($debug['header']) ? $debug['header'] : ('' != Environment);
            static::$cache_html = new \ounun\cache\html($cfg, $key2, static::$cache_html_time, static::$cache_html_trim, $debug);
            static::$cache_html->run(true);
        }
    }

    /**
     * 是否马上输出cache
     * @param bool $output
     */
    public function cache_html_stop(bool $output)
    {
        if (static::$cache_html) {
            static::$cache_html->stop($output);
            static::$tpl->replace();
        }
    }

    /** @var  \ounun\template  Template句柄容器 */
    public static $tpl;

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param string $filename
     * @param string $addon_tag
     * @param bool $show_debug
     * @return string
     */
    static public function tpl_fixed(string $filename, string $addon_tag = '', bool $show_debug = true): string
    {
        $tpl = static::$tpl->tpl_fixed($filename, $addon_tag, $show_debug);
        if ($tpl) {
            return $tpl;
        }
        return '';
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param string $filename
     * @return string
     */
    static public function tpl_curr(string $filename, string $addon_tag = ''): string
    {
        return static::$tpl->tpl_curr($filename, $addon_tag);
    }

    /** @var \ounun\debug 调试 相关 */
    public static $debug;

    /**
     * @param string $filename
     * @param bool $is_out_buffer
     * @param bool $is_out_get
     * @param bool $is_out_post
     * @param bool $is_out_url
     * @param bool $is_run_time
     * @param bool $is_bof
     */
    public static function debug_init($filename = '404', $is_out_buffer = true, $is_out_get = false, $is_out_post = false, $is_out_url = false, $is_run_time = false, $is_bof = false)
    {
        if (empty(static::$debug)) {
            static::$debug = new \ounun\debug(\ounun::$dir_data . 'logs/' . $filename . '_' . date('Ymd') . '.txt', $is_out_buffer, $is_out_get, $is_out_post, $is_out_url, $is_run_time, $is_bof);
        }
    }

    /**
     * 调试日志
     * @param $k
     * @param $log
     */
    public function debug_logs(string $k, $log)
    {
        if (static::$debug) {
            static::$debug->logs($k, $log);
        }
    }

    /**
     * 停止 调试
     */
    public function debug_stop()
    {
        if (static::$debug) {
            static::$debug->stop();
        }
    }

    /**
     * ounun_view constructor.
     * @param $url_mods
     */
    public function __construct($url_mods)
    {
        if (!$url_mods) {
            $url_mods = [\ounun::def_method];
        }
        $method       = $url_mods[0];
        \ounun::$view = $this;
        $this->$method($url_mods);
    }

    /**
     * 初始化Page
     * @param string $page_file
     * @param bool $is_cache_html
     * @param bool $ext_req
     * @param string $domain
     * @param int $cache_html_time
     * @param bool $cache_html_trim
     */
    public function init_page(string $page_file = '', bool $is_cache_html = true, bool $ext_req = true, string $domain = '', int $cache_html_time = 0, bool $cache_html_trim = true)
    {
        // url_check
        \ounun::url_page(\ounun::$url_addon_pre . $page_file);
        url_check(\ounun::$page_url, $ext_req, $domain);

        // cache_html
        if ('' == Environment) {
            $debug                   = \ounun::$global['debug'];
            static::$cache_html_trim = $debug && isset($debug['html_trim']) ? $debug['html_trim'] : $cache_html_trim;
        } else {
            static::$cache_html_trim = false;
        }
        if ($is_cache_html) {
            static::$cache_html_time = $cache_html_time > 300 ? $cache_html_time : static::$cache_html_time;
            $this->cache_html(\ounun::$page_url);
        }

        // template
        if (empty(static::$tpl)) {
            static::$tpl = new \ounun\template(\ounun::$tpl_style, \ounun::$tpl_style_default, \ounun::$tpl_type, \ounun::$tpl_type_default, static::$cache_html_trim);
        }

        // db
        if (empty(static::$db_v)) {
            static::$db_v = \ounun\db\pdo::i(\ounun::database_default_get());
        }
    }

    /**
     * 默认 首页
     * @param array $mod
     */
    public function index($mod)
    {
        error404("<strong>method</strong>  --> " . __METHOD__ . " <br />\n  
                       <strong>mod</strong> ------> " . json_encode($mod, JSON_UNESCAPED_UNICODE) . " <br />\n  
                       <strong>class</strong> ------> " . get_class($this));
    }

    /**
     * 默认 robots.txt文件
     * @param array $mod
     */
    public function robots($mod)
    {
        url_check('/robots.txt');
        $filename = \ounun::$dir_app . 'robots.txt';
        print_r(['$filename' => $filename]);
        $type = 'text/plain';
        $time = 14400;
        if (file_exists($filename)) {
            $mtime = filemtime($filename);
            expires($time, $mtime, $mtime, $type);
            readfile($filename);
        } else {
            $mtime = time();
            expires($time, $mtime, $mtime, $type);
            exit("User-agent: *\nDisallow:");
        }
    }

    /**
     * 默认 ads.txt文件    google.com
     * @param array $mod
     */
    public function ads($mod)
    {
        url_check('/ads.txt');
        $filename = \ounun::$dir_app . 'ads.txt';
        $type     = 'text/plain';
        $time     = 14400;
        if (file_exists($filename)) {
            $mtime = filemtime($filename);
            expires($time, $mtime, $mtime, $type);
            readfile($filename);
        } else {
            $mtime = time();
            expires($time, $mtime, $mtime, $type);
            exit("google.com, pub-7081168645550959, DIRECT, f08c47fec0942fa0");
        }
    }

    /**
     * /favicon.ico
     */
    public function favicon($mod)
    {
        $filename = Dir_Root . 'public/static/favicon.ico';
        $type     = 'image/x-icon';
        $time     = 14400;
        if (file_exists($filename)) {
            $mtime = filemtime($filename);
            expires($time, $mtime, $mtime, $type);
            readfile($filename);
        } else {
            $filename = Dir_Root . 'public/favicon.ico';
            if (file_exists($filename)) {
                $mtime = filemtime($filename);
                expires($time, $mtime, $mtime, $type);
                readfile($filename);
            }
        }
        if ($_GET['t']) {
            error404();
        }
        go_url(\ounun::$root_static . 'favicon.ico?t=' . time(), false, 301);
    }

    /**
     * 没定的方法
     * @param string $method
     * @param String $arguments
     */
    public function __call($method, $arguments)
    {
        header('HTTP/1.1 404 Not Found');
        if (Environment) {
            $this->debug_init('404');
        }
        error404("<strong>method</strong> -->   {$method} <br />\n 
                        <strong>args</strong> ------> " . json_encode($arguments, JSON_UNESCAPED_UNICODE) . " <br />\n 
                        <strong>class</strong> -----> " . get_class($this));
    }
}
