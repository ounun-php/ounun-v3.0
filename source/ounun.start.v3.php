<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

use ounun\addons\addons;
use ounun\debug;
use ounun\c;
use ounun\cache\html;
use ounun\template;

/** 是否Cli - 环境常量 */
define('Is_Cli', PHP_SAPI == 'cli');
/** 是否Win - 环境常量 */
define('Is_Win', strpos(PHP_OS, 'WIN') !== false);
/** Ounun版本号 */
define('Ounun_Version', '3.4.0');

/** root根目录 **/
defined('Dir_Root') || define('Dir_Root', realpath(__DIR__ . '/../') . '/');
/** libs库文件目录 **/
defined('Dir_Ounun') || define('Dir_Ounun', __DIR__ . '/');
/** libs目录 **/
defined('Dir_Vendor') || define('Dir_Vendor', Dir_Root . 'vendor/');
/** Storage目录 **/
defined('Dir_Storage') || define('Dir_Storage', Dir_Root . 'storage/');
/** data目录 **/
defined('Dir_Data') || define('Dir_Data', Dir_Storage . 'data/');
/** cache目录 **/
defined('Dir_Cache') || define('Dir_Cache', Dir_Storage . 'cache/');
/** cache html目录 **/
defined('Dir_Cache_Html') || define('Dir_Cache_Html', Dir_Storage . 'html/');
/** Environment目录 **/
defined('Environment') || define('Environment', environment());

// 测试环境
if (Environment) {
    /** 开始时间戳 **/
    define('Ounun_Start_Time', microtime(true));
    /** 开始内存量 **/
    define('Ounun_Start_Memory', memory_get_usage());
}


/**
 * 语言包
 *
 * @param string $s
 * @return string
 */
function l(string $s)
{
    if ($l = $GLOBALS['_lang_']) {
        if ($lang = $l[\ounun::$lang]) {
            if ($s2 = $lang[$s]) {
                return $s2;
            }
        }
        if ($lang_default = $l[\ounun::$lang_default]) {
            if ($s2 = $lang_default[$s]) {
                return $s2;
            }
        }
    }
    return $s;
}

/**
 * 得到访客的IP
 *
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
 *
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
 *
 * @param string $uri
 * @return string URL
 */
function url_original(string $uri = ''): string
{
    if (empty($uri)) {
        $uri = $_SERVER['REQUEST_URI'];
    }
    $tmp = explode('?', $uri, 2);
    return $tmp[0];
}

/**
 * 通过uri得到mod
 *
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
 *
 * @param string $url_original 网址
 * @param bool $ext_req 网址可否带参加数
 * @param string $domain 是否捡查 域名
 */
function url_check(string $url_original = '', bool $ext_req = true, string $domain = '')
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
 * 提示用户选择
 *
 * @param string $url1
 * @param string $url2
 * @param string $note
 * @param bool $top
 */
function go_confirm(string $url1, string $url2, string $note, bool $top = false): void
{
    $top  = "\t" . ($top ? 'window.top.' : '');
    $note ??= '点击“确定”继续操作  点击“取消” 中止操作';
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
 * 页跳转
 *
 * @param string $url
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
 * @param string $msg
 * @param string $url
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
 * @param string $charset
 * @return string
 */
function msg(string $msg, bool $outer = true, $meta = true, $charset = 'utf-8'): string
{
    $rs = "\n" . 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');' . "\n";
    if ($outer) {
        if ($meta) {
            $mt = '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />' . "\n";
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
function msg_close(string $msg, bool $close = false, $charset = 'utf-8'): void
{
    $rs = "\n" . 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');' . "\n";
    $mt = '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />' . "\n";
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
    $rs = ['msg' => $msg, 'status' => $status,];
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
 * @param mixed $data
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
 * @param mixed $data
 * @return string
 */
function error_message($data): string
{
    return $data['msg'];
}

/**
 * 返回错误代码
 * @param mixed $data
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
    $rs = ['msg' => $message, 'status' => 0, 'data' => $data];
    if ($extend) {
        return array_merge($extend, $rs);
    }
    return $rs;
}

/**
 * 返回 数据
 * @param mixed $data
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
        $type = c::Format_Json;
    }
    switch ($type) {
        // 返回JSON数据格式到客户端 包含状态信息
        case c::Format_Json :
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data, $json_options));
        // 返回xml格式数据
        case c::Format_Xml :
            header('Content-Type:text/xml; charset=utf-8');
            exit(\ounun\db\utils::xml_encode($data));
        // 返回JSON数据格式到客户端 包含状态信息
        case c::Format_Jsonp:
            header('Content-Type:application/javascript; charset=utf-8');
            if (empty($jsonp_callback)) {
                $jsonp_callback = (isset($_GET['jsonp_callback']) && $_GET['jsonp_callback']) ? $_GET['jsonp_callback'] : 'jsonp_callback';
            }
            exit($jsonp_callback . '(' . json_encode($data, $json_options) . ');');
        // 返回可执行的js脚本
        case  c::Format_JS :
        case  c::Format_Eval :
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
 * @param mixed $data
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
 * @param int $id to encode
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
 * @param string $string 字符串
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
 * error 404
 *
 * @param string $msg
 */
function error404(string $msg = ''): void
{
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('HTTP/1.1 404 Not Found');
    exit('<html lang="zh">
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
 * error 404
 *
 * @param string $error_msg
 * @param string $error_html
 */
function error_php(string $error_msg, string $error_html = ''): void
{
    if ($error_html) {
        echo $error_html;
    }
    echo '<pre>' . PHP_EOL;
    debug_print_backtrace();
    echo PHP_EOL . '</pre>';
    trigger_error($error_msg, E_USER_ERROR);
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
function retry(int $times, callable $callback, int $sleep = 0)
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
    // 读取环境配制
    $file = Dir_Storage . 'runtime/.environment.php';
    if (is_file($file)) {
        require $file;
    } else {
        $file = Dir_Root . 'env/environment.example.php';
        if (is_file($file)) {
            require $file;
        } else {
            error_php('file not found:' . $file);
        }
    }
    if (!isset($GLOBALS['_environment_'])) {
        $GLOBALS['_environment_'] = '2';
    }
    return $GLOBALS['_environment_'];
}

/**
 * 公共配制数据
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function global_all(string $key, $default = null)
{
    if ($value = \ounun::$global[$key]) {
        return $value;
    }
    return $default;
}

/**
 * 公共配制数据(应用)
 *
 * @param string $key
 * @param mixed $default
 * @param string $app_name
 * @return mixed
 */
function global_apps(string $key, $default = null, string $app_name = '')
{
    $app_name ??= \ounun::$app_name;
    if ($app_name) {
        $tag = \ounun::$global_apps[$app_name];
        if ($tag && $value = $tag[$key]) {
            return $value;
        }
    }
    return $default;
}

/**
 * 公共配制数据(插件)
 *
 * @param string $key
 * @param mixed $default
 * @param string $addon_tag
 * @return mixed
 */
function global_addons(string $key, string $addon_tag, $default = null)
{
    // 加载  -> runtime_apps 生成时加载
//    if (!isset(\ounun::$global_addons[$addon_tag])) {
//        $filename = Dir_Storage . 'runtime/.global_' . $addon_tag . '.php';
//        if (is_file($filename)) {
//            require $filename;
//        } else {
//            \ounun::$global_addons[$addon_tag] = [];
//        }
//    }

    // 转换翻译
    $tag = \ounun::$global_addons[$addon_tag];
    if ($tag && $value = $tag[$key]) {
        return $value;
    }
    return $default;
}

/**
 * 路由
 * @param $routes      array  目录路由表
 * @param $host        string 主机
 * @param $mod         array  目录数组
 * @param $default_app string 默认应用
 * @return string 应用
 */
class ounun
{
    /** @var string 默认模块名称 */
    const Def_Module = 'index';
    /** @var string 默认插件名称 */
    const Def_Addon = 'system';
    /** @var string 默认操作名称 */
    const Def_Method = 'index';

    /** @var string web 网页 */
    const App_Name_Web = 'web';
    /** @var string api 数据API接口 */
    const App_Name_Api = 'api';
    /** @var string control 控制后台 */
    const App_Name_Control = 'control';
    /** @var string process 异步进程 */
    const App_Name_Process = 'process';
    /** @var string command 后台任务（定时任务） */
    const App_Name_Command = 'command';
    /** @var array 应用名称组 */
    const App_Names = [
        self::App_Name_Web,
        self::App_Name_Api,
        self::App_Name_Control,
        self::App_Name_Process,
        self::App_Name_Command,
    ];

    /** @var v */
    public static v $view;

    /** @var array 公共配制数据 */
    public static array $global = [];
    /** @var array 公共配制数据(插件) */
    public static array $global_addons = [];
    /** @var array 公共配制数据(应用) */
    public static array $global_apps = [];

    /** @var array DB配制数据 */
    public static array $database = [];
    /** @var string 默认 数据库 */
    public static string $database_default = '';

    /** @var array 命令s */
    public static array $commands = [];

    /** @var array 添加App路径(根目录) */
    public static array $paths = [];

    /** 应用app数据 */
    public static array $app = [];
    /** 应用app数据(默认) */
    public static array $app_default = ['app_name' => self::App_Name_Web, 'path' => '/'];

    /** @var string 当前APP */
    public static string $app_name = '';
    /** @var string 当前APP Url前缀Path */
    public static string $app_path = '';

    /** @var string 域名Domain */
    public static string $app_domain = '';
    /** @var string 项目代号 */
    public static string $app_code = '';
    /** @var string 当前版本号(本地cache) 1.1.1 */
    public static string $app_version = '1.1.1';
    /** @var string 当前app之前通信内问key */
    public static string $app_key_communication_private = '';


    /** @var array 自动加载路径paths */
    public static array $maps_path = [];
    /** @var array 自动加载路径maps */
    public static array $maps_class = [];
    /** @var array 当前已安装的功能模块(插件) */
    public static array $maps_installed_addon = [];


    /** @var array 插件addons挂载数据 */
    public static array $addon_mount = [];
    /** @var array 插件addons网址映射url前缀Path(URL)数据 */
    public static array $addon_path = [];
    /** @var string 当前插件addon  网址Url前缀Path(URL) */
    public static string $addon_path_curr = '';

    /** @var string 当前面页(文件名)  Page Base */
    public static string $page_base_file = '';
    /** @var string 当前面页(网址)    Page URL */
    public static string $page_url = '';

    /** @var string Www Page */
    public static string $page_www = '';
    /** @var string Mobile Page */
    public static string $page_wap = '';
    /** @var string Mip Page */
    public static string $page_mip = '';

    /** @var string Www Root_Url */
    public static string $root_www = '';
    /** @var string Wap Root_Url */
    public static string $root_wap = '';
    /** @var string Mip Root_Url */
    public static string $root_mip = '';
    /** @var string Api Root_Url */
    public static string $root_api = '';

    /** @var string Res URL */
    public static string $url_res = '';
    /** @var string Static URL */
    public static string $url_static = '';
    /** @var string StaticG URL */
    public static string $url_static_g = '';
    /** @var string Upload URL */
    public static string $url_upload = '';

    /** @var string 应用模板类型pc/wap/mip - 模板 */
    public static string $tpl_type = 'pc';
    /** @var string 应用模板类型pc/wap/mip[默认] - 模板 */
    public static string $tpl_type_default = 'pc';

    /** @var string 主题风格(主题目录) */
    public static string $tpl_theme = 'default';
    /** @var string 主题风格(主题目录)[默认] - 模板 */
    public static string $tpl_theme_default = 'default';

    /** @var array Template view目录 */
    public static array $tpl_paths = [];
    /** @var array 模板替换数据组 */
    public static array $tpl_replace_array = [];

    /** @var string 当前语言 */
    public static string $lang = 'zh_cn';
    /** @var string 默认语言 */
    public static string $lang_default = 'zh_cn';
    /** @var array 支持的语言 "zh"=>"繁體中文", "ja"=>"日本語", */
    public static array $lang_supports = ["en_us" => "English", "zh_cn" => "简体中文",];

    /** @var array 站点SEO */
    public static array $seo_site = ['sitename' => '', 'keywords' => '', 'description' => '', 'slogan' => ''];

    /**
     * 添加命令行
     *
     * @param array $commands
     */
    static public function commands_set(array $commands)
    {
        foreach ($commands as $command) {
            if ($command && !in_array($command, \ounun::$commands)) {
                \ounun::$commands[] = $command;
            }
        }
    }

    /**
     * 本地环境变量设定 (应用)
     * @param string $app_name
     */
//    static public function environment_app_set(string $app_name)
//    {
//        // 为空时直接返回
//        if (empty($app_name)) {
//            return;
//        }
//        $config_ini = static::$global_apps[$app_name];
//
//        // print_r(['$app_name'=>$app_name,'$config_ini'=>$config_ini]);
//        if ($config_ini) {
//            static::environment_set($config_ini);
//        }
//    }

    /**
     * 本地环境变量设定
     * @param array $config_ini
     */
//    static public function environment_set(array $config_ini = [])
//    {
//        // 为空时直接返回
//        if (empty($config_ini)) {
//            return;
//        }
//
//        // 添加App路径(根目录)
//        $key = 'paths';
//        if (isset($config_ini[$key])) {
//            $vs = $config_ini[$key];
//
//            if ($vs && is_array($vs)) {
//                foreach ($vs as $v) {
//                    if (is_array($v) && $v['path']) {
//                        static::path_set($v['path'], $v['is_auto_helper']);
//                    }
//                }
//            } else {
//                if (file_exists(Dir_Root)) {
//                    static::path_set(Dir_Root, true);
//                }
//                if (file_exists(Dir_Vendor . 'cms.cc/')) {
//                    static::path_set(Dir_Vendor . 'cms.cc/', true);
//                }
//            }
//        }
//
//        // app数据
//        $key = 'apps';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                $routes_default = $config['default'] ?? [];
//                unset($config['default']);
//                if ($config && $routes_default) {
//                    static::apps_set($config, $routes_default, []);
//                }
//            }
//        }
//
//        // 挂载模块路由
//        $key = 'routes';
//        if (isset($config_ini[$key])) {
//            $addons = $config_ini[$key];
//            if ($addons && is_array($addons)) {
//                addons::mount_multi($addons);
//            }
//        }
//
//        // 域名&项目代号&当前app之前通信内问key
//        $key = 'domain';
//        if (isset($config_ini[$key])) {
//            $vs = $config_ini[$key];
//            if ($vs && is_array($vs)) {
//                static::domain_set($vs['domain'], $vs['code'], $vs['version'], $vs['key']);
//            }
//        }
//
//        // 统计 / 备案号 / Baidu / xzh / 配制cache_file
//        $key = 'global';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::global_set($config);
//            }
//        }
//
//        // 设定模板目录
//        $key = 'template_paths';
//        if (isset($config_ini[$key])) {
//            $tpl_dirs = $config_ini[$key];
//            if ($tpl_dirs && is_array($tpl_dirs)) {
//                static::template_paths_set($tpl_dirs);
//            }
//        }
//
//        // html变量替换
//        $key = 'template_array';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::template_array_set($config);
//            }
//        }
//
//        // 配制database
//        $key = 'databases';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::database_set($config, $config_ini['database_default']);
//            }
//        }
//
//        // 设定语言 & 设定支持的语言
//        $key = 'lang';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::lang_set('', $config['default'], $config['support']);
//            }
//        }
//
//        // 设定路由数据
//        $key = 'urls';
//        if (isset($config_ini[$key])) {
//            $urls = $config_ini[$key];
//            if ($urls && is_array($urls)) {
//                static::urls_set($urls['root_www'], $urls['root_wap'], $urls['root_mip'], $urls['root_api'], $urls['url_res'], $urls['url_upload'], $urls['url_static'], $urls['url_static_g']);
//            }
//        }
//
//        // 设定站点页面SEO
//        $key = 'seo_site';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            static::seo_site_set($config['sitename'], $config['keywords'], $config['description'], $config['slogan']);
//        }
//
////        // 设定站点页面SEO
////        $key = 'seo_page';
////        if (isset($config_ini[$key])) {
////            $config = $config_ini[$key];
////            static::seo_page_set((string)$config['title'], (string)$config['keywords'], (string)$config['description'], (string)$config['h1'], (string)$config['etag']);
////        }
//
//        // 公共配制数据(应用)
////        $key = '__app__';
////        if (isset($config_ini[$key])) {
////            $configs = $config_ini[$key];
////            if ($configs && is_array($configs)) {
////                foreach ($configs as $app_name => $config) {
////                    if ($config && is_array($config)) {
////                        static::global_addons_set($config, '', $app_name);
////                    }
////                }
////            }
////        } // end if
//
//        // 公共配制数据(插件)
////        $key = '__addons__';
////        if (isset($config_ini[$key])) {
////            $configs = $config_ini[$key];
////            if ($configs && is_array($configs)) {
////                foreach ($configs as $addon_tag => $config) {
////                    if ($config && is_array($config)) {
////                        static::global_addons_set($config, $addon_tag, '');
////                    }
////                }
////            }
////        } // end if
//    }

    /**
     * 设定语言 & 设定支持的语言
     *
     * @param string $lang
     * @param string $lang_default
     * @param array $lang_support_list 设定支持的语言
     */
    static public function lang_set(string $lang = '', string $lang_default = '', array $lang_support_list = [])
    {
        if ($lang) {
            static::$lang = $lang;
        }
        if ($lang_default) {
            static::$lang_default = $lang_default;
        }
        if ($lang_support_list && is_array($lang_support_list)) {
            foreach ($lang_support_list as $lang => $lang_name) {
                static::$lang_supports[$lang] = $lang_name;
            }
        }
        // 加载 语言包
        if (static::$lang && static::$lang != static::$lang_default) {
            $file = Dir_Storage . 'runtime/.lang_' . static::$app_name . '_' . static::$lang . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
        // 加载 默认语言包 -> runtime_apps 自动加载
    }

    /**
     * 设定站点的SEO
     * @param string $sitename
     * @param string $keywords
     * @param string $description
     * @param string $slogan
     */
//    static public function seo_site_set(string $sitename = '', string $keywords = '', string $description = '', string $slogan = '')
//    {
//        $sitename && static::$seo_site['sitename'] = $sitename;
//        $keywords && static::$seo_site['keywords'] = $keywords;
//        $description && static::$seo_site['description'] = $description;
//        $slogan && static::$seo_site['slogan'] = $slogan;
//    }

    /**
     * 设定页面的SEO
     *
     * @param string $title
     * @param string $keywords
     * @param string $description
     * @param string $h1
     * @param string $etag
     */
    static public function seo_page_set(string $title = '', string $keywords = '', string $description = '', string $h1 = '', string $etag = '')
    {
        $seo_page = [];
        $title && $seo_page['{$seo_title}'] = $title;
        $keywords && $seo_page['{$seo_keywords}'] = $keywords;
        $description && $seo_page['{$seo_description}'] = $description;
        $h1 && $seo_page['{$seo_h1}'] = $h1;
        $etag && $seo_page['{$seo_etag}'] = $etag;
        $seo_page && static::$tpl_replace_array = array_merge(static::$tpl_replace_array, []);
    }

    /**
     * 设定公共配制数据
     *
     * @param array $config
     */
    static public function global_set(array $config = [])
    {
        if ($config) {
            foreach ($config as $key => $value) {
                static::$global[$key] = $value;
            }
        }
    }

    /**
     * 设定公共配制数据(应用)
     *
     * @param array $config
     * @param string $app_name
     */
    static public function global_apps_set(array $config = [], string $app_name = '')
    {
        if ($config) {
            $app_name ??= static::$app_name;
            if (!isset(static::$global_apps[$app_name])) {
                static::$global_apps[$app_name] = [];
            } elseif (is_array(static::$global_apps[$app_name])) {
                static::$global_apps[$app_name] = [];
            }
            foreach ($config as $key => $value) {
                static::$global_apps[$app_name][$key] = $value;
            }
        } // end $config
    }

    /**
     * 设定公共配制数据(插件)
     *
     * @param array $config
     * @param string $addon_tag
     */
    static public function global_addons_set(array $config = [], string $addon_tag = '')
    {
        if ($config) {
            if ($addon_tag) {
                if (!isset(static::$global_addons[$addon_tag])) {
                    static::$global_addons[$addon_tag] = [];
                } elseif (is_array(static::$global_addons[$addon_tag])) {
                    static::$global_addons[$addon_tag] = [];
                }
                foreach ($config as $key => $value) {
                    static::$global_addons[$addon_tag][$key] = $value;
                }
            }
        } // end $config
    }

    /**
     * 设定DB配制数据
     *
     * @param array $database_config
     * @param string $database_default
     */
    static public function database_set(array $database_config = [], string $database_default = '')
    {
        if ($database_config) {
            foreach ($database_config as $db_key => $db_cfg) {
                static::$database[$db_key] = $db_cfg;
            }
        }
        if ($database_default) {
            static::$database_default = $database_default;
        }
    }

    /**
     * 默认 数据库
     *
     * @return string
     */
    static public function database_default_get()
    {
        if (empty(static::$database_default)) {
            static::$database_default = static::$app_name;
        }
        return static::$database_default;
    }

    /**
     * 添加$addon
     *
     * @param string $addon_apps
     */
    static public function addons_set(string $addon_apps)
    {
        if ($addon_apps && !in_array($addon_apps, static::$maps_installed_addon)) {
            array_push(static::$maps_installed_addon, $addon_apps);
        }
    }

    /**
     * 添加App路径(根目录)
     *
     * @param string $path_root
     */
    static public function path_set(string $path_root)
    {
        /** src-0 \         自动加载 */
        ounun::load_class_set($path_root . 'src/', '', false);
        /** src-0 \addons   自动加载  */
        ounun::load_class_set($path_root . 'addons/', 'addons', true);
    }

    /**
     * 设定地址
     * @param string $root_www
     * @param string $root_wap
     * @param string $root_mip
     * @param string $root_api
     * @param string $url_res
     * @param string $url_static
     * @param string $url_upload
     * @param string $url_static_g
     */
    static public function urls_set(string $root_www, string $root_wap, string $root_mip, string $root_api,
                                    string $url_res, string $url_upload, string $url_static, string $url_static_g)
    {
        /** Www URL */
        static::$root_www = $root_www;
        /** Wap URL Mobile */
        static::$root_wap = $root_wap;
        /** Mip URL */
        static::$root_mip = $root_mip;
        /** Api URL */
        static::$root_api = $root_api;

        /** Res URL */
        static::$url_res = $url_res;
        /** Upload URL */
        static::$url_upload = $url_upload;
        /** Static URL */
        static::$url_static = $url_static;
        /** StaticG URL */
        static::$url_static_g = $url_static_g;
    }

    /**
     * 域名&项目代号&当前app之前通信内问key
     * @param string $app_domain
     * @param string $app_code
     * @param string $app_version
     * @param string $app_key_communication_private
     */
    static public function domain_set(string $app_domain = '', string $app_code = '', string $app_version = '', string $app_key_communication_private = '')
    {
        /** 项目主域名 */
        $app_domain && static::$app_domain = $app_domain;
        /** 项目代号 */
        $app_code && static::$app_code = $app_code;
        /** 当前版本号(本地cache) 1.1.1 */
        $app_version && static::$app_version = $app_version;
        /** 当前app之前通信内问key */
        $app_key_communication_private && static::$app_key_communication_private = $app_key_communication_private;
    }

    /**
     * 设定 模板类型/主题风格
     *
     * @param string $tpl_type 类型
     * @param string $tpl_type_default 类型(默认)
     * @param string $tpl_theme 主题风格
     * @param string $tpl_theme_default 主题风格(默认)
     */
    static public function tpl_theme_set(string $tpl_type = '', string $tpl_type_default = '',
                                         string $tpl_theme = '', string $tpl_theme_default = '')
    {
        // 类型
        $tpl_type && static::$tpl_type = $tpl_type;
        // 类型(默认)
        $tpl_type_default && static::$tpl_type_default = $tpl_type_default;

        // 主题风格
        $tpl_theme && static::$tpl_theme = $tpl_theme;
        // 主题风格(默认)
        $tpl_theme_default && static::$tpl_theme_default = $tpl_theme_default;
    }


    /**
     * 设定 模板tpl根目录
     *
     * @param array $paths 模板tpl根目录
     */
    static public function tpl_paths_set(array $paths = [])
    {
        // 模板根目录
        if ($paths && is_array($paths)) {
            foreach ($paths as $tpl_dir) {
                // print_r(['__LINE__'=>__LINE__,'$tpl_dir'=>$tpl_dir]);
                if (!in_array($tpl_dir, static::$tpl_paths) && is_dir($tpl_dir['path'])) {
                    static::$tpl_paths[] = $tpl_dir;
                }
            }
        }
    }

    /**
     * 设定模板替换
     *
     * @param string $key
     * @param string $value
     */
    static public function tpl_replace_array_set(string $key, string $value)
    {
        static::$tpl_replace_array[$key] = $value;
    }

    /**
     * 设定模板替换
     *
     * @param array $data
     */
    static public function tpl_replace_array_multi_set(?array $data)
    {
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                static::$tpl_replace_array[$key] = $value;
            }
        }
    }

    /**
     * 赋值(默认) $seo + $url
     *
     * @return array
     */
    static public function tpl_replace_array_get()
    {
        return array_merge([
            '{$page_url}'  => static::$page_url,      // $lang/$app_path/$base_url,
            '{$page_file}' => static::$page_base_file,// 基础url,
            // 根目录/面面路径
            '{$page_www}'  => static::$page_www,
            '{$page_wap}'  => static::$page_wap,
            '{$page_mip}'  => static::$page_mip,
            // 根目录
            '{$root_www}'  => static::$root_www,
            '{$root_wap}'  => static::$root_wap,
            '{$root_mip}'  => static::$root_mip,
            '{$root_api}'  => static::$root_api,

            '{$root_res}'         => static::$url_res,
            '{$root_upload}'      => static::$url_upload, '/public/uploads/' => static::$url_upload,
            '{$root_static}'      => static::$url_static, '/public/static/' => static::$url_static,
            '{$root_static_g}'    => static::$url_static_g, '/public/static_g/' => static::$url_static_g,
            // seo_site
            '{$site_name}'        => static::$seo_site['name'],
            '{$site_keywords}'    => static::$seo_site['keywords'],
            '{$site_description}' => static::$seo_site['description'],
            '{$site_slogan}'      => static::$seo_site['slogan'],
            // app_name
            '{$app_name}'         => static::$app_name,
            '{$app_domain}'       => static::$app_domain,
        ], static::$tpl_replace_array);
    }

    /**
     * 当前面页
     *
     * @param string $url
     * @param string $lang
     * @return string
     */
    static public function url_page_get(string $url = '', $lang = '')
    {
        if (empty($lang)) {
            $lang = static::$lang;
        }
        if ($url !== '' && $url[0] == '/') {
            $page_base_file = $url;
            if ($lang == static::$lang_default) {
                $page_lang = '';
                $page_url  = static::$app_path . substr($url, 1);
            } else {
                $page_lang = '/' . $lang;
                $page_url  = $page_lang . static::$app_path . substr($url, 1);
            }
        } else {
            $page_base_file = '/' . $url;
            if ($lang == static::$lang_default) {
                $page_lang = '';
                $page_url  = static::$app_path . $url;
            } else {
                $page_lang = '/' . $lang;
                $page_url  = $page_lang . static::$app_path . $url;
            }
        }
        if (empty(static::$page_url)) {
            static::url_page_set($page_base_file, $page_url, $page_lang);
        }
        return $page_url;
    }

    /**
     * 设定$page_www/$page_wap/$page_mip
     *
     * @param string $page_base_file
     * @param string $page_url
     * @param string $page_lang
     */
    static public function url_page_set(string $page_base_file, string $page_url, string $page_lang)
    {
        /** @var string Base Page */
        static::$page_base_file = $page_base_file;
        /** @var string URL Page */
        static::$page_url = $page_url;

        /** @var string Www Page */
        $a                = explode('/', static::$root_www, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_www = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";

        /** @var string Mobile Page */
        $a                = explode('/', static::$root_wap, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_wap = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";

        /** @var string Mip Page */
        $a                = explode('/', static::$root_mip, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_mip = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";
    }

    /**
     * 静态地址
     *
     * @param $url
     * @param string $static_root
     * @return string
     */
    static public function root_static($url, string $static_root = '/static/'): string
    {
        if ($url && is_array($url)) {
            $url = count($url) > 1 ? '??' . implode(',', $url) : $url[0];
        }
        return "{$static_root}{$url}";
    }

    /**
     * 当前带http的网站根
     *
     * @return string
     */
    static public function root_curr_get()
    {
        if (static::$tpl_type == template::Type_Mip) {
            return static::$root_mip;
        } elseif (static::$tpl_type == template::Type_Wap) {
            return static::$root_wap;
        }
        return static::$root_www;
    }

    /**
     * 添加类库映射 (为什么不直接包进来？到时才包这样省一点)
     *
     * @param string $class
     * @param string $filename
     * @param bool $is_require 是否默认加载
     */
    static public function class_set(string $class, string $filename, bool $is_require = false)
    {
        // echo __FILE__.':'.__LINE__.' $class:'."{$class} \$filename:{$filename}\n";
        if ($is_require && is_file($filename)) {
            require $filename;
        } else {
            static::$maps_class[$class] = $filename;
        }
    }

    /**
     * 加载helper
     * @param string $path_root
     */
    static public function load_helper(string $path_root)
    {
        is_file($path_root . 'app/helper.php') && require $path_root . 'app/helper.php';
        if (static::$app_name) {
            is_file($path_root . 'app/helper.' . static::$app_name . '.php') && require $path_root . 'app/helper.' . static::$app_name . '.php';
        }
    }

    /**
     * 自动加载的类
     * @param string $class
     */
    static public function load_class(string $class)
    {
//      echo // __FILE__.':'.__LINE__. ' $class:'."{$class}<br />\n";
        $filename = static::load_class_file_exists($class);
        if ($filename) {
            require $filename;
        }
    }

    /**
     * 添加自动加载路径
     * @param string $path_root 目录路径
     * @param string $namespace_prefix 命名空间
     * @param bool $cut_path 是否剪切 目录路径中的 命名空间
     */
    static public function load_class_set(string $path_root, string $namespace_prefix = '', bool $cut_path = false)
    {
        if ($path_root) {
            if ($namespace_prefix) {
                $first = explode('\\', $namespace_prefix)[0];
                $len   = strlen($namespace_prefix) + 1;
            } else {
                $first = '';
                $len   = 0;
            }
            if (!static::$maps_path
                || !static::$maps_path[$first]
                || !(is_array(static::$maps_path[$first]) && in_array($path_root, array_column(static::$maps_path[$first], 'path')))) {
                static::$maps_path[$first][] = [
                    'path'      => $path_root,
                    'len'       => $len,
                    'cut'       => $cut_path,
                    'namespace' => $namespace_prefix
                ];
            }
        }
    }

    /**
     * 加载的类文件是否存在
     *
     * @param $class
     * @return string
     */
    static protected function load_class_file_exists($class)
    {
        // 类库映射
        if (!empty(static::$maps_class[$class])) {
            $file = self::$maps_class[$class];
            // echo "\$file:{$file}\n";
            if ($file && is_file($file)) {
                return $file;
            }
        }

        // 查找 PSR-4 prefix
        $filename = strtr($class, '\\', '/') . '.php';
        $firsts   = [explode('\\', $class)[0], ''];
        foreach ($firsts as $first) {
            if (isset(static::$maps_path[$first])) {
                foreach (static::$maps_path[$first] as $v) {
                    if ('' == $v['namespace']) {
                        // print_r(static::$maps_paths);
                        $file = $v['path'] . $filename;
//                                                echo " load_class2  -> \$class1 :{$class}  \$first:{$first}   \$len:{$v['len']}\n".
//                                                    "                \t\t\$path:{$v['path']}\n".
//                                                    "                \t\t\$filename:{$filename}\n".
//                                                    "                \t\t\$file1:{$file} \n";
                        if (is_file($file)) {
                            return $file;
                        }
                    } elseif (0 === strpos($class, $v['namespace'])) {
                        $file = $v['path'] . (($v['cut'] && $v['len']) ? substr($filename, $v['len']) : $filename);
//                                                echo " load_class  -> \$class0 :{$class}  \$first:{$first}  \$len:{$v['len']}\n".
//                                                    "                \t\t\$path:{$v['path']}\n".
//                                                    "                \t\t\$filename:{$filename}\n".
//                                                    "                \t\t\$file1:{$file} \n".var_export($v,true);
                        if (is_file($file)) {
                            return $file;
                        }
                    }
                }
            }
        }
        // echo ' ---> bad';
        return '';
    }

    /**
     * 加载controller
     * @param string $class_filename
     * @return string
     */
//    static public function load_controller(string $class_filename)
//    {
//        $paths = static::$maps_path['app'];
//        if ($paths && is_array($paths)) {
//            foreach ($paths as $v) {
//                $filename = $v['path'] . static::$app_name . '/' . $class_filename;
//                //  echo "\$filename:{$filename}\n";
//                if (is_file($filename)) {
//                    return $filename;
//                }
//            }
//        }
//        return '';
//    }

    /**
     * 设定路由数据
     *
     * @param array $apps
     * @param array $apps_default
     * @param array $addons_mount
     */
//    static public function apps_set(array $apps, array $apps_default = [], array $addons_mount = [])
//    {
//        if ($apps) {
//            foreach ($apps as $k => $v) {
//                static::$app[$k] = $v;
//            }
//        }
//        if ($apps_default) {
//            static::$app_default = $apps_default;
//        }
//        if ($addons_mount) {
//            static::$addon_mount = $addons_mount;
//        }
//    }

    /**
     * 模块 快速路由
     *
     * @param array $url_mods
     * @return array
     */
    static public function apps_get(array $url_mods = [])
    {
        // 修正App_Name
        $app_name = (static::$app_name == static::App_Name_Web || in_array(static::$app_name, static::App_Names))
            ? static::$app_name
            : static::App_Name_Web;
        // debug::header(\ounun::$apps_cache, '', __FILE__, __LINE__);

        // 插件路由
        $addon_tag = '';

        /** @var addons $apps */
        if ($url_mods[1] && ($route = static::$addon_mount["{$url_mods[0]}/$url_mods[1]"]) && $apps = $route['apps']) {
            array_shift($url_mods);
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif ($url_mods[0] && ($route = static::$addon_mount[$url_mods[0]]) && $apps = $route['apps']) {
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif (($route = static::$addon_mount['']) && $apps = $route['apps']) {
            $addon_tag = $apps::Addon_Tag;
        } else {
            error_php('ounun::$apps_cache[\'\']: There is no default value -> $apps_cache:' . json_encode(ounun::$addon_mount) . '');
        }

        // api
        if ($app_name == static::App_Name_Api) {
            $filename   = Dir_Ounun . 'ounun/restful.php';
            $class_name = "\\ounun\\restful";
            return [$filename, $class_name, $addon_tag, $url_mods];
        }

        // view_class
        if ($route['view_class']) {
            $class_filename = "{$addon_tag}/{$app_name}/{$route['view_class']}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}\\{$route['view_class']}";
        } else {
            $class_filename = "{$addon_tag}/{$app_name}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}";
        }
        static::$addon_path_curr = $route['url'] ? '/' . $route['url'] : '';

        // paths
        if ($class_filename) {
            $paths = static::$maps_path['addons'];
            if ($paths && is_array($paths)) {
                foreach ($paths as $v) {
                    $filename = $v['path'] . $class_filename;
                    // echo "\$filename:{$filename0}\n";
                    if (is_file($filename)) {
                        //  echo " --> \$filename000:{$filename}\n";
                        if (empty($url_mods)) {
                            $url_mods = [static::Def_Method];
                        }
                        return [$filename, $class_name, $addon_tag, $url_mods];
                    }
                }
            } // if ($paths
        }
        return ['', '', '', $url_mods];
    }
}


/**
 * 构造模块基类 Class ViewBase
 *
 * @package ounun
 */
abstract class v
{
    /** @var int cache_html_time */
    public static int $cache_html_time = 2678400; // 31天

    /** @var bool html_trim */
    public static bool $cache_html_trim = true;

    /** @var html|null cache_html */
    public static ?html $cache_html;

    /** @var  template|null  Template句柄容器 */
    public static ?template $tpl;

    /** @var debug|null debug调试相关 */
    public static ?debug $debug;

    /** @var string 插件标识 */
    public string $addon_tag = '';

    /**
     * 调试初始化
     *
     * @param string $channel
     * @param string $filename
     * @return debug|null
     */
    public static function debug_init(string $channel = 'comm', string $filename = '404.txt')
    {
        if (empty(static::$debug)) {
            static::$debug = debug::i($channel, $filename);
        }
        return static::$debug;
    }

    /**
     * 调试日志
     *
     * @param string $k
     * @param mixed $log
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
     * 网页Cache
     *
     * @param $key
     */
    public function cache_html($key)
    {
        if ('' == Environment && $cache_config = global_all('cache', [])['html']) {
            $cache_config['prefix'] = 'html_' . \ounun::$app_name . '_' . \ounun::$tpl_theme . '_' . \ounun::$tpl_type;
            static::$cache_html     = new html($cache_config, $key, static::$cache_html_time, static::$cache_html_trim);
            static::$cache_html->run(true);
        }
    }

    /**
     * 是否马上输出cache
     *
     * @param bool $output
     */
    public function cache_html_stop(bool $output)
    {
        if (static::$cache_html) {
            static::$cache_html->stop($output);
            static::$tpl->replace();
        }
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     *
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
     *
     * @param string $filename
     * @param string $addon_tag
     * @return string
     */
    static public function tpl_curr(string $filename, string $addon_tag = ''): string
    {
        return static::$tpl->tpl_curr($filename, $addon_tag);
    }


    /**
     * ounun_view constructor.
     *
     * @param array $url_mods
     * @param string $addon_tag
     */
    public function __construct(array $url_mods, string $addon_tag = '')
    {
        if (empty($url_mods)) {
            $url_mods = [\ounun::Def_Method];
        }
        $method       = $url_mods[0];
        \ounun::$view = $this;

        $addon_tag && $this->addon_tag = $addon_tag;
        $this->_initialize($method);
        $this->$method($url_mods);
    }

    /**
     * 初始化
     *
     * @param string $method
     */
    protected function _initialize(string $method)
    {

    }

    /**
     * 初始化Page
     *
     * @param string $page_file
     * @param bool $is_cache_html
     * @param bool $ext_req
     * @param string $domain
     * @param int $cache_html_time
     * @param bool $cache_html_trim
     */
    public function init_page(string $page_file = '', bool $is_cache_html = true, bool $ext_req = true, string $domain = '',
                              int $cache_html_time = 0, bool $cache_html_trim = true)
    {
        // url_check
        \ounun::url_page_get(\ounun::$addon_path_curr . $page_file);
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
            static::$tpl = new template(\ounun::$tpl_theme, \ounun::$tpl_theme_default, \ounun::$tpl_type, \ounun::$tpl_type_default, static::$cache_html_trim);
        }
    }

    /**
     * 默认 首页
     *
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
     *
     * @param array $mod
     */
    public function robots($mod)
    {
        url_check('/robots.txt');
        $filename = Dir_Root . 'env/app.robots.' . \ounun::$app_name . '.txt';
        $type     = 'text/plain';
        $time     = 14400;
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
     *
     * @param array $mod
     */
    public function ads($mod)
    {
        url_check('/ads.txt');
        $filename = Dir_Root . 'env/app.ads.' . \ounun::$app_name . '.txt';
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
        $filenames = [Dir_Root . 'public/static/favicon.ico', Dir_Root . 'public/favicon.ico'];
        $type      = 'image/x-icon';
        $time      = 14400;
        foreach ($filenames as $filename) {
            if (file_exists($filename)) {
                $mtime = filemtime($filename);
                expires($time, $mtime, $mtime, $type);
                readfile($filename);
                exit();
            }
        }
        if ($_GET['t'] || empty(\ounun::$url_static)) {
            error404();
        }
        go_url(\ounun::$url_static . 'favicon.ico?t=' . time(), false, 301);
    }

    /**
     * 没定的方法
     *
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

/**
 * 开始
 * @param array $url_mods
 * @param string $host
 */
function start(array $url_mods, string $host)
{
    // 语言lang
    if ($url_mods && $url_mods[0] && ounun::$lang_supports[$url_mods[0]]) {
        $lang = array_shift($url_mods);
    } else {
        $lang = ounun::$lang ?? ounun::$lang_default;
    }

    // 应用app
    if ($url_mods && $url_mods[0] && $app = ounun::$app["{$host}/{$url_mods[0]}"]) {
        array_shift($url_mods);
    } elseif (ounun::$app[$host]) {
        $app = ounun::$app[$host];
    } else {
        $app = ounun::$app_default;
    }

    // 设定
    ounun::$app_name = (string)$app['app_name']; // 当前APP
    ounun::$app_path = (string)$app['path'];     // 当前APP Path

    // runtime_apps
    $filename = Dir_Storage . 'runtime/.runtime_' . ounun::$app_name . '.php';
    if (is_file($filename)) {
        require $filename;
    }

    // lang
    ounun::lang_set($lang);

    // paths
    foreach (ounun::$paths as $v) {
        // path_set
        ounun::path_set($v['path']);
        if ($v['is_auto_helper']) {
            // load_helper
            ounun::load_helper($v['path']);
        }
    }

    // template_set
    ounun::tpl_theme_set((string)$app['tpl_type'], (string)$app['tpl_type_default'], (string)$app['tpl_theme'], (string)$app['tpl_theme_default']);

    // 开始 重定义头
    header('X-Powered-By: cms.cc; ounun.org;');
    debug::header(['$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 设定 模块与方法(缓存)
    /** @var v $classname */
    list($filename, $classname, $addon_tag, $url_mods) = ounun::apps_get($url_mods);
    debug::header(['$filename' => $filename, '$classname' => $classname, '$addon_tag' => $addon_tag, '$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 包括模块文件
    if ($filename) {
        require $filename;
        if (class_exists($classname, false)) {
            new $classname($url_mods, $addon_tag);
            exit();
        } else {
            $error = "LINE:" . __LINE__ . " Can't find controller:'{$classname}' filename:" . $filename;
        }
    } else {
        $error = "LINE:" . __LINE__ . " Can't find controller:{$classname}";
    }
    header('HTTP/1.1 404 Not Found');
    error_php($error);
}

/** Web */
function start_web()
{
    $uri      = url_original($_SERVER['REQUEST_URI']);
    $url_mods = url_to_mod($uri);
    start($url_mods, $_SERVER['HTTP_HOST']);
}

/** 自动加载 src-4 \ounun  */
ounun::load_class_set(Dir_Ounun, 'ounun', false);
/** 注册自动加载 */
spl_autoload_register('\\ounun::load_class');
