<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

use JetBrains\PhpStorm\NoReturn;
use ounun\addons\apps;
use ounun\addons\console;
use ounun\addons\logic;
use ounun\cache\html;
use ounun\db\db;
use ounun\debug;
use ounun\c;
use ounun\template;

/** 是否Cli - 环境常量 */
define('Is_Cli', PHP_SAPI == 'cli');
/** 是否Win - 环境常量 */
define('Is_Win', str_contains(PHP_OS, 'WIN'));
/** Ounun版本号 */
define('Ounun_Version', '3.5.0');

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

/**
 * 语言包
 *
 * @param string $s
 * @return string
 */
function l(string $s)
{
    if ($l = $GLOBALS['$L']) {
        if ($lang = $l[ounun::$lang]) {
            if ($s2 = $lang[$s]) {
                return $s2;
            }
        }
        if ($lang_default = $l[ounun::$lang_default]) {
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
            }
        }
    }
    if (empty($hdr_ip)) {
        $hdr_ip = '127.0.0.1';
    }
    return $hdr_ip;
}

/**
 * 输出带参数的URL
 *
 * @param string $url URL
 * @param array|null $data_query 数据
 * @param array|null $replace_ext 要替换的数据
 * @param array|null $skip 忽略的数据 如:page
 * @return string
 */
function url_build_query(string $url, ?array $data_query, ?array $replace_ext = null, ?array $skip = null): string
{
    // 参数
    $data_query ??= [];

    // replace_ext
    if (is_array($replace_ext)) {
        foreach ($replace_ext as $key => $value) {
            $data_query[$key] = $value;
        }
    }

    // skip
    if (is_array($skip)) {
        if (array_keys($skip) === range(0, count($skip) - 1)) {
            foreach ($skip as $value) {
                unset($data_query[$value]);
            }
        } else {
            foreach ($skip as $key => $value) {
                if (isset($data_query[$key])) {
                    if (is_array($value)) {
                        if (in_array($data_query[$key], $value)) {
                            unset($data_query[$key]);
                        }
                    } elseif (is_string($value)) {
                        if ($value == $data_query[$key]) {
                            unset($data_query[$key]);
                        }
                    } elseif (is_bool($value) && is_numeric($value)) {
                        if ($value) {
                            unset($data_query[$key]);
                        }
                    }
                }
            }
        }
    }

    // data_query
    $rs     = [];
    $rs_str = '';
    if (is_array($data_query)) {
        foreach ($data_query as $key => $value) {
            if (is_string($value) && '{' === $value[0]) {
                $rs_str = $key . '=' . $value; // '={page}'
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

    // url
    $url = trim($url);
    if ($rs) {
        $len = strlen($url);
        if ($url && $len > 0) {
            if (!str_contains($url, '?')) {
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
 * @param string|null $uri
 * @return string URL
 */
function url_original(?string $uri = null): string
{
    $uri ??= $_SERVER['REQUEST_URI'];
    return explode('?', $uri, 2)[0];
}

/**
 * 通过uri得到mod
 *
 * @param $uri string
 * @return array
 */
function url_to_mod(string $uri): array
{
    $uri = explode('/', $uri, 2);
    $uri = explode('.', urldecode($uri[1]), 2);
    $uri = explode('/', $uri[0]);
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
    // echo("\$domain:{$domain}\n");
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
#[NoReturn]
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
#[NoReturn]
function go_url(string $url, bool $top = false, int $head_code = 302, int $delay = 0): void
{
    // error_php($url);
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
 *
 * @param int $num
 */
#[NoReturn]
function go_back(int $num = -1): void
{
    echo '<script type="text/javascript">', "\n", 'window.history.go(' . $num . ');', "\n", '</script>', "\n";
    exit();
}

/**
 *  挑转网页 彈出alert對話框
 *
 * @param string $msg
 * @param string $url
 */
#[NoReturn]
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
 *
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
 *
 * @param string $msg
 * @param bool $close
 * @param string $charset
 */
#[NoReturn]
function msg_close(string $msg, bool $close = false, string $charset = 'utf-8'): void
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
 *
 * @return bool
 */
function https_is(): bool
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
 *
 * @param string $msg
 * @param int $status
 * @param mixed $data
 * @param array|null $extend 延伸数据
 * @return array
 */
function error(string $msg = '', int $status = 1, mixed $data = null, ?array $extend = null): array
{
    $result = ['msg' => $msg, 'status' => $status,];
    if ($data) {
        $result['data'] = $data;
    }
    if ($extend) {
        $result = array_merge($extend, $result);
    }
    return $result;
}

/**
 * 确认是否错误 数据
 *
 * @param array|null $result
 * @return bool
 */
function error_is(?array $result = null): bool
{
    if (empty($result) || !is_array($result) || !array_key_exists('status', $result) || (array_key_exists('status', $result) && $result['status'] == 0)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 返回错误提示信息
 *
 * @param array $result
 * @return string
 */
function error_message(array $result): string
{
    return $result['msg'];
}

/**
 * 返回 错误代码
 *
 * @param array $result
 * @return int
 */
function error_code(array $result): int
{
    return $result['status'];
}

/**
 * 返回 成功
 *
 * @param mixed $data
 * @param string $message
 * @param array $extend 延伸数据
 * @return array
 */
function succeed(mixed $data, string $message = '', array $extend = []): array
{
    $result = ['msg' => $message, 'status' => 0, 'data' => $data];
    if ($extend) {
        return array_merge($extend, $result);
    }
    return $result;
}

/**
 * 返回 成功数据
 *
 * @param array $result
 * @return mixed
 */
function succeed_data(array $result): mixed
{
    return $result['data'];
}

/**
 * 返回 成功数据
 *
 * @param array $result
 * @param mixed $data
 * @return mixed
 */
function succeed_data_set(array $result, mixed $data): array
{
    if (isset($result['data'])) {
        if (is_array($result['data'])) {
            $result['data'] = array_merge($result['data'], $data);
        } else {
            $result['data'] = array_merge($data, ['_data_' => $result['data']]);
        }
    } else {
        $result['data'] = $data;
    }
    return $result;
}

/**
 * Ajax方式返回数据到客户端
 *
 * @param mixed $data 要返回的数据
 * @param string $type AJAX返回数据格式
 * @param string $jsonp_callback
 * @param int $json_options 传递给json_encode的option参数
 */
#[NoReturn] function out(mixed $data, string $type = c::Format_Json, string $jsonp_callback = '', int $json_options = JSON_UNESCAPED_UNICODE)
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
            exit(db::xml_encode($data));
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
 *
 * @param mixed $data
 * @return string|null
 */
function json_encode_unescaped(mixed $data): ?string
{
    if (is_null($data)) {
        return null;
    }
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/**
 * 对 json格式的字符串进行解码
 *
 * @param string|null $json_string
 * @return mixed
 */
function json_decode_array(?string $json_string): mixed
{
    if (is_null($json_string)) {
        return null;
    } elseif (is_string($json_string)) {
        return json_decode($json_string, true);
    }
    return $json_string;
}

/**
 * 获得 extend数据php
 *
 * @param string $extend_string
 * @return mixed
 */
function extend_decode_php(string $extend_string): mixed
{
    $extend = [];
    if ($extend_string) {
        $extend = unserialize($extend_string);
    }
    return $extend;
}

/**
 * 获得 extend数据json
 *
 * @param string|null $extend_string
 * @return mixed
 */
function extend_decode_json(?string $extend_string): mixed
{
    $extend = [];
    if ($extend_string) {
        $extend = json_decode($extend_string, true);
    }
    return $extend;
}

/**
 * 对字符串进行编码，这样可以安全地通过URL
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_encode(string $string = ''): string
{
    return strtr(base64_encode($string), '+/=', '-_~');
}

/**
 * 解码一个 URL传递的字符串
 *
 * @param string $string to decode
 * @return string
 */
function base64_url_decode(string $string = ''): string
{
    return base64_decode(strtr($string, '-_~', '+/='));
}

/**
 * 编号 转 字符串
 *
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
 *
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
 *
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
function data(string $filename): mixed
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
#[NoReturn]
function error404(string $msg = '')
{
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('HTTP/1.1 404 Not Found');
    echo '<html lang="zh">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>404 Not Found</title>
            </head>
            <body style="background-color: white;">
                <div style="text-align: center;">
                    <h1>404 Not Found</h1>
                    <p style="color: gray;">' . $_SERVER['HTTP_HOST'] . '</p>
                </div>
                <hr>
                <div style="text-align: center;"><a href="' . ounun::$root_www . '">返回网站首页</a></div>';
    $is_backtrace = global_all('debug', false, 'backtrace');
    if ($is_backtrace) {
        echo($msg ? '<div style="border: #EEEEEE 1px solid;padding: 5px;color: grey;margin-top: 20px;">' . $msg . '</div>' : '');
        echo '<pre>' . PHP_EOL;
        debug_print_backtrace();
        echo '</pre>';
    }
    exit('</body>
            </html>
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- ' . ounun::$app_name . ' -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->' . "\n");

}

/**
 * error php
 *
 * @param string $error_msg
 * @param string $error_html
 * @param string $channel
 */
#[NoReturn]
function error_php(string $error_msg, string $error_html = '', string $channel = 'php')
{
    // global_all
    $out = global_all('debug_out');
    // if
    if ($out && isset($out['default']) && isset($out['default']['buffer'])) {
        if (isset($out[$channel]) && is_array($out[$channel])) {
            $c = array_merge($out['default'], $out[$channel]);
        } else {
            $c = $out['default'];
        }
        debug::i($channel, $c['buffer'], $c['get'], $c['post'], $c['url'], $c['cookie'], $c['session'], $c['server'], $c['bof'], $c['time'], $c['date_dir'], $c['filename']);
    } else {
        debug::i($channel);
    }
    // print_r(['$out' => $out, '$error_msg' => $error_msg, '$error_html' => $error_html, '$channel' => $channel]);
    if ($error_html) {
        echo $error_html;
    }
    echo '<pre>' . PHP_EOL;
    echo '$app:' . var_export(['$app_name' => ounun::$app_name, '$app_path' => ounun::$app_path, '$paths' => ounun::$paths], true) . PHP_EOL;
    debug_print_backtrace();
    echo '</pre>';
    trigger_error($error_msg, E_USER_ERROR);
}

/**
 * 重试指定次数的操作。
 * Retry an operation a given number of times.
 *
 * @param int $times
 * @param callable $callback
 * @param int $sleep 微秒 1/1000 秒
 * @return mixed
 * @throws Exception
 */
function retry(int $times, callable $callback, int $sleep = 0): mixed
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
function environment(): string
{
    if (isset($GLOBALS['_environment_'])) {
        return $GLOBALS['_environment_'];
    }
    // 读取环境配制
    $filename = Dir_Storage . 'runtime/.environment.php';
    if (is_file($filename)) {
        require $filename;
    } else {
        $filename = Dir_Root . 'env/example.environment.php';
        if (is_file($filename)) {
            require $filename;
        } else {
            error_php('Unable to find: ${Dir_Root}/env/example.environment.php');
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
 * @param string|null $sub_key
 * @return mixed
 */
function global_all(string $key, $default = null, ?string $sub_key = null)
{
    $ret = $default;
    if ($key && isset(ounun::$global[$key])) {
        if (is_null($sub_key)) {
            $ret = ounun::$global[$key];
        }
        $value = ounun::$global[$key];
        if (is_array($value) && isset($value[$sub_key])) {
            $ret = $value[$sub_key];
        }
    }
    if (is_null($default)) {
        return $ret;
    } elseif (empty($ret)) {
        return $default;
    }
    return $ret;
}

/**
 * 公共配制数据(插件)
 *
 * @param string $addon_tag
 * @param string|null $key
 * @param mixed|null $default
 * @param string|null $sub_key
 * @return mixed
 */
function global_addons(string $addon_tag, ?string $key = null, mixed $default = null, ?string $sub_key = null): mixed
{
    $ret    = $default;
    $values = ounun::$global_addons[$addon_tag] ?? [];
    if (is_null($key)) {
        $ret = $values;
    }
    if ($values && isset($values[$key])) {
        if (is_null($sub_key)) {
            $ret = $values[$key];
        }
        $value = $values[$key];
        if (is_array($value) && isset($value[$sub_key])) {
            $ret = $value[$sub_key];
        }
    }
    if (is_null($default)) {
        return $ret;
    } elseif (empty($ret)) {
        return $default;
    }
    return $ret;
}

/**
 * ounun
 *
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

    /** @var array DB配制数据 */
    public static array $database = [];
    /** @var string 默认 数据库 */
    public static string $database_default = '';

    /** @var array 添加App路径(根目录) */
    public static array $paths = [];

    /** 应用app数据 */
    public static array $app = [];
    /** 应用app数据(默认) */
    public static array $app_default = [];

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


    /** @var array 插件addons数据 */
    public static array $addon = [];
    /** @var array 插件addons路由数据 */
    public static array $addon_route = [];
    /** @var array 插件addons网址映射url前缀Path(URL)数据 */
    public static array $addon_path = [];

    /** @var string 当前面页(文件名)  Page Base */
    public static string $page_file_path = '';
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

    /** @var string 当前语言 */
    public static string $lang = 'zh_cn';
    /** @var string 默认语言 */
    public static string $lang_default = 'zh_cn';
    /** @var array 支持的语言 "zh"=>"繁體中文", "ja"=>"日本語", */
    public static array $lang_support = ["en_us" => "English", "zh_cn" => "简体中文",];

    /**
     * 添加命令行
     *
     * @param array $commands $key => $command   索引关键key:命令实例
     */
    static public function commands_set(array $commands)
    {
        console::add($commands);
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
        if ($is_require && is_file($filename)) {
            require $filename;
        } else {
            static::$maps_class[$class] = $filename;
        }
    }

    /**
     * 添加App路径(根目录)
     *
     * @param string $path_root
     */
    static public function path_root_set(string $path_root)
    {
        /** src-0 \         自动加载 */
        ounun::load_class_set($path_root . 'src/', '', false);
        /** src-0 \addons   自动加载  */
        ounun::load_class_set($path_root . 'addons/', 'addons', true);
    }

    /**
     * 设定语言 & 设定支持的语言
     *
     * @param string $lang
     * @param string $lang_default
     */
    static public function lang_set(string $lang = '', string $lang_default = '')
    {
        if ($lang) {
            static::$lang = $lang;
        }
        if ($lang_default) {
            static::$lang_default = $lang_default;
        }
        // 加载 语言包
        if (static::$lang && static::$lang != static::$lang_default) {
            $filename = Dir_Storage . 'runtime/.lang_' . static::$app_name . '_' . static::$lang . '.php';
            if (is_file($filename)) {
                require_once $filename;
            }
        }
        // 加载 默认语言包 -> runtime_apps 自动加载
    }

    /**
     * 设定公共配制数据
     *
     * @param array $config
     */
    static public function global_set(array $config = [])
    {
        if ($config) {
            if (empty(static::$global) || !is_array(static::$global)) {
                static::$global = [];
            }
            foreach ($config as $key => $value) {
                static::$global[$key] = $value;
            }
        }
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
                if (!isset(static::$global_addons[$addon_tag]) || !is_array(static::$global_addons[$addon_tag])) {
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
    static public function database_default(): string
    {
        if (empty(static::$database_default)) {
            static::$database_default = static::$app_name;
        }
        return static::$database_default;
    }

    /**
     * 网址路径前缀(Url)
     *
     * @param string $addon_tag
     * @param string $addon_view
     * @param string $path
     * @param string|null $lang
     * @param bool $is_current 是否当前页面
     * @return string 返回 $path 所对应的 $page_url
     */
    static public function url_addon(string $addon_tag, string $addon_view = '', string $path = '', ?string $lang = null, bool $is_current = false): string
    {
        // 空
        if (empty($addon_tag)) {
            return static::url($path, $lang, $is_current);
        }

        // tag view都存在
        if ($addon_view) {
            $key = '/' . $addon_tag . '/' . $addon_view;
            if (isset(static::$addon_path[$key])) {
                $page_file_path = static::$addon_path[$key] . $path;
                return static::url($page_file_path, $lang, $is_current);
            }
            $key2 = '/' . $addon_tag;
            if (isset(static::$addon_path[$key2])) {
                $page_file_path = static::$addon_path[$key2] . '/' . $addon_view . $path;
                return static::url($page_file_path, $lang, $is_current);
            }
            $page_file_path = $key . $path;
            return static::url($page_file_path, $lang, $is_current);
        }

        // tag
        $key = '/' . $addon_tag;
        if (isset(static::$addon_path[$key])) {
            $page_file_path = static::$addon_path[$key] . $path;
        } else {
            $page_file_path = $key . $path;
        }

        return static::url($page_file_path, $lang, $is_current);
    }

    /**
     * 静态地址
     *
     * @param string $url
     * @param string $static_root
     * @return string
     */
    static public function url_static(string $url, string $static_root = '/static/'): string
    {
        if ($url && is_array($url)) {
            $url = count($url) > 1 ? '??' . implode(',', $url) : $url[0];
        }
        return "{$static_root}{$url}";
    }

    /**
     * 当前面页
     *
     * @param string $page_file_path
     * @param string|null $lang
     * @param bool $is_current 是否当前页面
     * @return string 返回 $page_url | ounun::$page_url
     */
    static public function url(string $page_file_path = '', ?string $lang = null, bool $is_current = false): string
    {
        $lang ??= static::$lang;
        if ($lang == static::$lang_default) {
            $lang_path = '';
            $page_url  = static::$app_path . $page_file_path;
        } else {
            $lang_path = '/' . $lang;
            $page_url  = $lang_path . static::$app_path . $page_file_path;
        }

        if ($is_current && empty(static::$page_url)) {
            static::url_set($page_file_path, $page_url, $lang_path);
        }
        return $page_url;
    }

    /**
     * 设定$page_www/$page_wap/$page_mip/$page_url
     *
     * @param string $page_file_path
     * @param string $page_url
     * @param string $lang_path
     */
    static public function url_set(string $page_file_path, string $page_url, string $lang_path)
    {
        /** @var string Base Page */
        static::$page_file_path = $page_file_path;
        /** @var string URL Page */
        static::$page_url = $page_url;

        /** @var string Www Page */
        $pages            = explode('/', static::$root_www, 4);
        $path_dir         = isset($pages[3]) && $pages[3] ? "/{$pages[3]}" : '';
        static::$page_www = "{$pages[0]}//{$pages[2]}{$lang_path}{$path_dir}{$page_file_path}";

        /** @var string Mobile Page */
        $pages            = explode('/', static::$root_wap, 4);
        $path_dir         = isset($pages[3]) && $pages[3] ? "/{$pages[3]}" : '';
        static::$page_wap = "{$pages[0]}//{$pages[2]}{$lang_path}{$path_dir}{$page_file_path}";

        /** @var string Mip Page */
        $pages            = explode('/', static::$root_mip, 4);
        $path_dir         = isset($pages[3]) && $pages[3] ? "/{$pages[3]}" : '';
        static::$page_mip = "{$pages[0]}//{$pages[2]}{$lang_path}{$path_dir}{$page_file_path}";
    }

    /**
     * 当前app所对应http的网站根
     *
     * @return string
     */
    static public function url_root_current_app(): string
    {
        if (template::$type == template::Type_Mip) {
            return static::$root_mip;
        } elseif (template::$type == template::Type_Wap) {
            return static::$root_wap;
        }
        return static::$root_www;
    }

    /**
     * 加载helper
     *
     * @param string $path
     */
    static public function load_helper(string $path)
    {
        if (is_file($path . 'app/helper.php')) {
            require $path . 'app/helper.php';
        }
        if (static::$app_name && is_file($path . 'app/helper.' . static::$app_name . '.php')) {
            require $path . 'app/helper.' . static::$app_name . '.php';
        }
    }

    /**
     * 自动加载的类
     *
     * @param string $class
     */
    static public function load_class(string $class)
    {
        $filename = static::load_class_file_exists($class);
        if ($filename) {
            require $filename;
        }
    }

    /**
     * 添加自动加载路径(尽量少调用，生成配制)
     * @param string $root_path 目录路径
     * @param string $namespace_prefix 命名空间
     * @param bool $is_cut_path 是否剪切 目录路径中的 命名空间
     */
    static public function load_class_set(string $root_path, string $namespace_prefix = '', bool $is_cut_path = false)
    {
        if ($root_path) {
            if ($namespace_prefix) {
                $first = explode('\\', $namespace_prefix)[0];
                $len   = strlen($namespace_prefix) + 1;
            } else {
                $first = '';
                $len   = 0;
            }
            if (empty(static::$maps_path)
                || empty(static::$maps_path[$first])
                || !(is_array(static::$maps_path[$first]) && in_array($root_path, array_column(static::$maps_path[$first], 'path')))) {
                static::$maps_path[$first][] = ['path'      => $root_path,
                                                'len'       => $len,
                                                'cut'       => $is_cut_path,
                                                'namespace' => $namespace_prefix];
            }
        }
    }

    /**
     * 加载的类文件是否存在
     *
     * @param $class
     * @return string
     */
    static protected function load_class_file_exists($class): string
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
                    } elseif (str_starts_with($class, $v['namespace'])) {
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
}

/**
 * 构造模块基类 Class ViewBase
 *
 * @package ounun
 */
abstract class v
{
    /** @var logic logic */
    public static $logic;

    /** @var  template|null  Template句柄容器 */
    public static ?template $tpl;

    /** @var html|null cache_html */
    public static ?html $cache_html;

    /** @var string 插件唯一标识 */
    public static string $addon_tag = '';

    /** @var string 插件展示子类 */
    public static string $addon_view = '';

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     *
     * @param string $filename
     * @param string $addon_tag
     * @return string
     */
    static public function tpl_fixed(string $filename, string $addon_tag = ''): string
    {
        $tpl = static::$tpl->fixed($filename, $addon_tag);
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
        return static::$tpl->curr($filename, $addon_tag);
    }

    /**
     * ounun_view constructor.
     *
     * @param array $url_mods
     * @param string|null $addon_tag
     */
    public function __construct(array $url_mods, ?string $addon_tag = null)
    {
        if (empty($url_mods)) {
            $url_mods = [ounun::Def_Method];
        }
        $method      = $url_mods[0];
        ounun::$view = $this;

        empty($addon_tag) || static::$addon_tag = $addon_tag;
        $this->_initialize($method);
        $this->$method($url_mods);
    }

    /**
     * 初始化
     *
     * @param string $method
     */
    abstract protected function _initialize(string $method);

    /**
     * 默认 首页
     *
     * @param array $url_mods
     */
    abstract public function index(array $url_mods);

    /** toString */
    public function __toString(): string
    {
        return get_class($this);
    }
}

/** Web 开始 */
#[NoReturn]
function start_web()
{
    // 开始
    $host     = $_SERVER['HTTP_HOST'];
    $uri      = url_original($_SERVER['REQUEST_URI']);
    $url_mods = url_to_mod($uri);

    // 语言lang
    if ($url_mods && $url_mods[0] && isset(ounun::$lang_support[$url_mods[0]]) && ounun::$lang_support[$url_mods[0]]) {
        $lang = array_shift($url_mods);
    } else {
        $lang = ounun::$lang ?? ounun::$lang_default;
    }

    // 应用app
    if ($url_mods && $url_mods[0] && isset(ounun::$app["{$host}/{$url_mods[0]}"]) && $app = ounun::$app["{$host}/{$url_mods[0]}"]) {
        array_shift($url_mods);
    } elseif (ounun::$app[$host]) {
        $app = ounun::$app[$host];
    } else {
        $app = ounun::$app_default;
    }

    debug::header(['$host' => $host, '$app' => $app, '$lang' => $lang, '$url_mods' => $url_mods, 'REQUEST_URI' => $_SERVER['REQUEST_URI']], '$app', __FILE__, __LINE__);

    // 设定
    ounun::$app_name = (string)$app['app_name']; // 当前APP
    ounun::$app_path = (string)$app['path'];     // 当前APP Path

    // runtime_apps
    $filename = Dir_Storage . 'runtime/.runtime_' . ounun::$app_name . '.php';
    if (is_file($filename)) {
        require $filename;
    } else {
        $filename = Dir_Root . 'env/example.runtime_' . ounun::$app_name . '.php';
        if (is_file($filename)) {
            require $filename;
        }
    }

    // lang
    ounun::lang_set($lang);

    // paths
    foreach (ounun::$paths as $v) {
        // path_set
        ounun::path_root_set($v['path']);
        if ($v['is_auto_helper']) {
            // load_helper
            ounun::load_helper($v['path']);
        }
    }

    // template_set
    template::theme_set((string)$app['tpl_type'], (string)$app['tpl_type_default'], (string)$app['tpl_theme'], (string)$app['tpl_theme_default']);

    // 开始 重定义头
    header('X-Powered-By: cms.cc; ounun.org;');
    // debug::header(['$url_mods' => $url_mods,'REQUEST_URI' => $_SERVER['REQUEST_URI'],'$host'=>$host], '', __FILE__, __LINE__);

    // URL path_find
    $find = function (string $class_filename, array $url_mods, array $addon) {
        $paths = ounun::$maps_path['addons'];
        if ($paths && is_array($paths)) {
            foreach ($paths as $v) {
                $filename = $v['path'] . $class_filename;
                if (is_file($filename)) {
                    if (empty($url_mods)) {
                        if (isset($addon['method']) && $addon['method']) {
                            $url_mods = [$addon['method']];
                        } else {
                            $url_mods = [ounun::Def_Method];
                        }
                    }
                    return [$filename, $url_mods];
                }
            }
        }
        return ['', $url_mods];
    };

    // 模块 快速路由
    $addon_get = function ($url_mods) use ($find) {
        // 修正App_Name
        $app_name = (ounun::$app_name === ounun::App_Name_Web || in_array(ounun::$app_name, ounun::App_Names))
            ? ounun::$app_name
            : ounun::App_Name_Web;

        // 插件路由
        $addon_tag = '';
        /** @var apps $apps */
        if (isset($url_mods[1]) && (isset(ounun::$addon_route["{$url_mods[0]}/$url_mods[1]"]) && $addon = ounun::$addon_route["{$url_mods[0]}/$url_mods[1]"]) && $apps = $addon['apps']) {
            array_shift($url_mods);
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif (isset($url_mods[0]) && (isset(ounun::$addon_route[$url_mods[0]]) && $addon = ounun::$addon_route[$url_mods[0]]) && $apps = $addon['apps']) {
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif ((isset(ounun::$addon_route['']) && $addon = ounun::$addon_route['']) && $apps = $addon['apps']) {
            $addon_tag = $apps::Addon_Tag;
        } else {
            error404('ounun::$addon_route[\'\']: There is no default value -> $addon_route:' . json_encode(ounun::$addon_route) . '');
        }

        // api
        if ($app_name == ounun::App_Name_Api) {
            // 插件路由api
            $class_filename = "{$addon_tag}/restful.php";
            $class_name     = "\\addons\\{$addon_tag}\\restful";
            list($filename, $url_mods) = $find($class_filename, $url_mods, $addon);
            if ($filename) {
                return [$filename, $class_name, $addon_tag, $url_mods];
            }
            // 默认
            $filename   = Dir_Ounun . 'ounun/restful.php';
            $class_name = "\\ounun\\restful";
            return [$filename, $class_name, $addon_tag, $url_mods];
        }

        // view
        if ($addon['view']) {
            $class_filename = "{$addon_tag}/{$app_name}/{$addon['view']}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}\\{$addon['view']}";
        } else {
            $class_filename = "{$addon_tag}/{$app_name}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}";
        }
        debug::header([$addon_tag, $class_filename, $class_name, $addon], '$addon', __FILE__, __LINE__);

        // paths
        if ($class_filename) {
            list($filename, $url_mods) = $find($class_filename, $url_mods, $addon);
            if ($filename) {
                return [$filename, $class_name, $addon_tag, $url_mods];
            }
        }
        return ['', '', '', $url_mods];
    };

    // 设定 模块与方法(缓存)
    /** @var string $classname */
    list($filename, $classname, $addon_tag, $url_mods) = $addon_get($url_mods);
    debug::header(['$filename' => $filename, '$classname' => $classname, '$addon_tag' => $addon_tag, '$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 包括模块文件
    if ($filename) {
        require $filename;
        if (class_exists($classname, false)) {
            new $classname($url_mods, $addon_tag);
            exit();
        }
    }
    header('HTTP/1.1 404 Not Found');
    $error = "LINE:" . __LINE__ . " error:" . json_encode_unescaped(['$filename' => $filename, '$classname' => $classname, '$addon_tag' => $addon_tag, '$url_mods' => $url_mods]);
    error_php($error);
}

/**
 * Cli 开始
 * @param array $argv
 * @param array $commands
 * @param string $name
 * @param string $version
 * @return int
 */
function start_cli(array $argv, array $commands = [], string $name = 'Ounun Command', string $version = '0.1'): int
{
    ounun::$app_name = ounun::App_Name_Command;
    ounun::$app_path = '/';

    // runtime_apps
    $filename = Dir_Storage . 'runtime/.runtime_' . ounun::$app_name . '.php';
    if (is_file($filename)) {
        require $filename;
    } else {
        $filename = Dir_Root . 'env/example.runtime_' . ounun::$app_name . '.php';
        if (is_file($filename)) {
            require $filename;
        }
    }

    // paths
    foreach (ounun::$paths as $v) {
        // path_set
        ounun::path_root_set($v['path']);
        if ($v['is_auto_helper']) {
            // load_helper
            ounun::load_helper($v['path']);
        }
    }

    return (new console($commands, $name, $version))->execute($argv);
}

/** 自动加载 src-4 \ounun  */
ounun::load_class_set(Dir_Ounun, 'ounun', false);
/** 注册自动加载 */
spl_autoload_register('\\ounun::load_class');
/** Environment目录 */
defined('Environment') || define('Environment', environment());
/** 环境 */
if (Environment) {
    /** 开始时间戳 **/
    define('Ounun_Start_Time', microtime(true));
    /** 开始内存量 **/
    define('Ounun_Start_Memory', memory_get_usage());
}
