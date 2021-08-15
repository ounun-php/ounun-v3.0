<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun;


class debug
{
    /** @var array  日志数组 */
    private array $_logs = [];
    /** @var string|null 输出文件名 */
    private ?string $_logs_buffer;
    /** @var float 输出文件名 */
    private float $_time = 0;

    /** @var string 输出文件名 */
    private string $_filename;
    /** @var bool 是否添加到文件开头EOF */
    private bool $_is_bof;
    /** @var bool 是否输出 run time */
    private bool $_is_run_time;

    /** @var bool 是否输出 buffer */
    private bool $_is_out_buffer;
    /** @var bool 是否输出 get */
    private bool $_is_out_get;
    /** @var bool 是否输出 post */
    private bool $_is_out_post;
    /** @var bool 是否输出 url */
    private bool $_is_out_url;
    /** @var bool 是否输出 cookie */
    private bool $_is_out_cookie;
    /** @var bool 是否输出 session */
    private bool $_is_out_session;
    /** @var bool 是否输出 server */
    private bool $_is_out_server;

    /**
     * 构造函数
     * debug constructor.
     * @param string $filename 输出文件名
     * @param bool $is_out_buffer 是否输出 buffer
     * @param bool $is_out_get 是否输出 get
     * @param bool $is_out_post 是否输出 post
     * @param bool $is_out_url 是否输出 url
     * @param bool $is_out_cookie 是否输出 cookie
     * @param bool $is_out_session 是否输出 session
     * @param bool $is_out_server 是否输出 server
     * @param bool $is_bof 倒序(后面的日志写到前面)
     * @param bool $is_run_time 运行时间毫秒
     */
    public function __construct(string $filename = 'debug.txt',
                                bool $is_out_buffer = true, bool $is_out_get = true, bool $is_out_post = true, bool $is_out_url = true,
                                bool $is_out_cookie = true, bool $is_out_session = true, bool $is_out_server = false,
                                bool $is_bof = false, bool $is_run_time = true)
    {
//        print_r(['$filename'       => $filename, '$is_out_buffer' => $is_out_buffer, '$is_out_get' => $is_out_get, '$is_out_url' => $is_out_url, '$is_out_cookie' => $is_out_cookie,
//                 '$is_out_session' => $is_out_session, '$is_out_server' => $is_out_server, '$is_bof' => $is_bof, '$is_run_time' => $is_run_time]);
        if ($filename) {
            $dirname = dirname($filename);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
        }

        // callback
        if (class_exists(template::class) && template::ob_status()) {
            template::ob_func([$this, 'callback'], []);
        } else {
            ob_start();
            register_shutdown_function([$this, 'callback']);
        }
        set_error_handler([$this, 'callback_error'], E_ERROR);
        set_exception_handler([$this, 'callback_exception']);

        $this->_filename    = $filename;
        $this->_is_bof      = $is_bof;
        $this->_is_run_time = $is_run_time;

        $this->_is_out_url    = $is_out_url;
        $this->_is_out_buffer = $is_out_buffer;
        $this->_is_out_get    = $is_out_get;
        $this->_is_out_post   = $is_out_post;

        $this->_is_out_cookie  = $is_out_cookie;
        $this->_is_out_session = $is_out_session;
        $this->_is_out_server  = $is_out_server;

        if ($this->_is_run_time) {
            $this->_time = -microtime(true);
        }

        // init write
        $this->write();

        // header
        // static::$_header_idx = 0;
    }

    /**
     * 通过配置单独定义函数的配置
     * @param string $k buffer/get/post/url
     * @param bool $v
     * @return $this
     */
    public function out(string $k, bool $v): self
    {
        $_k        = '_is_out_' . $k;
        $this->$_k = $v;
        return $this;
    }


    /**
     * 设置文件路径
     * @param string $filename 文件路径
     * @return $this
     */
    public function filename(string $filename): self
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * 运行时间是否显示
     * @param bool $show true:显示 false:不显示
     * @return $this
     */
    public function run_time(bool $show): self
    {
        $this->_is_run_time = $show;
        return $this;
    }

    /**
     * 日志追加位置
     *
     * @param bool $bof true:倒序(新内容在头部) false:正序(新内容在尾部)
     * @return $this
     */
    public function bof(bool $bof): self
    {
        $this->_is_bof = $bof;
        return $this;
    }

    /**
     * 调试日志
     *
     * @param string $k
     * @param mixed $log 日志内容
     * @param bool $is_replace 是否替换
     * @return $this
     */
    public function logs(string $k, mixed $log, bool $is_replace = true): self
    {
        if ($k && $log) {
            // 直接替换
            if ($is_replace) {
                $this->_logs[$k] = $log;
            } else {
                if (isset($this->_logs[$k]) && $this->_logs[$k]) {
                    // 已是数组,添加到后面
                    if (is_array($this->_logs[$k])) {
                        $this->_logs[$k][] = $log;
                    } else {
                        $this->_logs[$k] = [$this->_logs[$k], $log];
                    }
                } else {
                    $this->_logs[$k] = $log;
                }
            }
            $this->write();
        }
        return $this;
    }

    /**
     * 停止调试
     *
     * @return debug
     */
    public function stop(): debug
    {
        $this->_logs     = [];
        $this->_filename = '';
        return $this;
    }

    /**
     * 内部内调
     * @param string|null $buffer
     */
    public function callback(?string $buffer = null)
    {
        if (empty($buffer)) {
            $buffer = ob_get_contents();
            ob_clean();
            ob_implicit_flush(true);
            if ($this->_is_out_buffer) {
                $this->_logs_buffer = $buffer;
            }
            $this->write(true);
            exit($buffer);
        } else {
            if ($this->_is_out_buffer) {
                $this->_logs_buffer = $buffer;
            }
            $this->write(true);
        }
    }

    /**
     * @param $exception
     */
    public function callback_exception($exception)
    {
        $this->logs('__exception__', $exception, false);
    }

    /**
     * @param $error_code
     * @param $error_str
     * @param $error_file
     * @param $error_line
     */
    public function callback_error($error_code, $error_str, $error_file, $error_line)
    {
        switch ($error_code) {
            case E_WARNING:
                // x / 0 错误 PHP7 依然不能很友好的自动捕获 只会产生 E_WARNING 级的错误
                // 捕获判断后 throw new DivisionByZeroError($err_str)
                // 或者使用 int_div(x, 0) 方法 会自动抛出 DivisionByZeroError 的错误
                if (strcmp('Division by zero', $error_str) == 0) {
                    throw new \DivisionByZeroError($error_str);
                }
                $level_tips = 'PHP Warning: ';
                break;
            case E_NOTICE:
                $level_tips = 'PHP Notice: ';
                break;
            case E_DEPRECATED:
                $level_tips = 'PHP Deprecated: ';
                break;
            case E_USER_ERROR:
                $level_tips = 'User Error: ';
                break;
            case E_USER_WARNING:
                $level_tips = 'User Warning: ';
                break;
            case E_USER_NOTICE:
                $level_tips = 'User Notice: ';
                break;
            case E_USER_DEPRECATED:
                $level_tips = 'User Deprecated: ';
                break;
            case E_STRICT:
                $level_tips = 'PHP Strict: ';
                break;
            default:
                $level_tips = 'Unknown Type Error: ';
                break;
        }
        $error = $error_str . ' in ' . $error_file . ' on ' . $error_line;
        $this->logs($level_tips, $error, false);
    }

    /**
     * 析构调试相关
     *
     * @param bool $is_end 是否当前请求 最后一次写入
     * @return $this
     */
    public function write(bool $is_end = false): self
    {
        if (!$this->_filename) {
            return $this;
        }
        $filename = $this->_filename;
        $str      = '';
        // web 环境参数
        if (!Is_Cli) {
            // url
            if ($this->_is_out_url) {
                $this->_is_out_url = false;
                $str               .= date('Y-m-d H:i:s') . ' URL :\'' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https:' : 'http:') . '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'\n";
            }
            // get
            if ($this->_is_out_get && $_GET) {
                $this->_is_out_get = false;
                $t                 = [];
                foreach ($_GET as $k => $v) {
                    $t[] = "{$k} => {$v}";
                }
                $str .= 'GET :' . implode("\n    ", $t) . PHP_EOL;
            }
            // post
            if ($this->_is_out_post && $_POST) {
                $str .= 'POST:' . var_export($_POST, true) . PHP_EOL;
            }
            // input
            if ($this->_is_out_post) {
                $this->_is_out_post = false;
                $input              = file_get_contents('php://input');
                if ($input) {
                    $str .= 'INPUT:' . $input . PHP_EOL;
                }
            }
            // cookie
            if ($this->_is_out_cookie && isset($_COOKIE)) {
                $this->_is_out_cookie = false;
                $str                  .= 'COOKIE:' . var_export($_COOKIE, true) . PHP_EOL;
            }
            // session
            if ($this->_is_out_session && isset($_SESSION)) {
                $this->_is_out_session = false;
                $str                   .= 'SESSION:' . var_export($_SESSION, true) . PHP_EOL;
            }
            // server
            if ($this->_is_out_server && isset($_SERVER)) {
                $this->_is_out_server = false;
                $str                  .= 'SERVER:' . var_export($_SERVER, true) . PHP_EOL;
            }
        }

        // 日志
        if ($this->_logs) {
            $str         .= 'LOGS:' . var_export($this->_logs, true) . PHP_EOL;
            $this->_logs = [];
        }

        // 日志尾部
        if ($is_end) {
            if ($this->_is_run_time) {
                $this->_time += microtime(true);
                $run_time    = sprintf('%f', $this->_time);
            } else {
                $run_time = '';
            }
            if ($this->_is_out_buffer && $this->_logs_buffer) {
                $str                .= '--- DATE:' . date("Y-m-d H:i:s") . ' RunTime:' . $run_time . '---' . PHP_EOL . $this->_logs_buffer;
                $this->_logs_buffer = '';
            }
            $str .= '------------------' . PHP_EOL;
        }

        // 写文件
        if ($this->_is_bof && $str) {
            if (file_exists($filename)) {
                $str = $str . file_get_contents($filename);
            }
            $ret = file_put_contents($filename, $str);
        } elseif ($str) {
            $ret = file_put_contents($filename, $str, FILE_APPEND);
        } else {
            $ret = 1;
        }
        if (empty($ret)) {
            error_php('debug write error');
        }
        return $this;
    }

    /** @var int header idx */
    private static int $_header_idx = 0;

    /**
     * 在header输出头数据
     *
     * @param string $k
     * @param mixed $v
     * @param string $filename
     * @param int $line
     */
    static public function header(mixed $v, string $k = '', string $filename = '', int $line = 0)
    {
        if (static::is_header() && !headers_sent()) {
            $key = [];
            static::$_header_idx++;

            empty($k) || $key[] = $k;
            empty($filename) || $key[] = basename($filename);
            empty($line) || $key[] = $line;

            $key = implode('-', $key);
            if (is_array($v) || is_object($v)) {
                $v = stripslashes(json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $idx = str_pad((string)static::$_header_idx, 4, '0', STR_PAD_LEFT);
            header("o{$idx}-{$key}: {$v}", true);
        }
    }

    /** @var array 单例模式 */
    static protected array $_instances = [];

    /**
     * @param string $channel
     * @param bool $is_out_buffer 是否输出 buffer
     * @param bool $is_out_get 是否输出 get
     * @param bool $is_out_post 是否输出 post
     * @param bool $is_out_url 是否输出 url
     * @param bool $is_out_cookie 是否输出 cookie
     * @param bool $is_out_session 是否输出 session
     * @param bool $is_out_server 是否输出 server
     * @param bool $is_bof 倒序(后面的日志写到前面)
     * @param bool $is_run_time 运行时间毫秒
     * @param string $date_dir 时间 以_或/ 目录或用_分开
     * @param string|null $filename 输出文件名
     * @return $this 调试日志单例
     */
    public static function i(string $channel = 'comm', bool $is_out_buffer = true, bool $is_out_get = true, bool $is_out_post = true, bool $is_out_url = true,
                             bool $is_out_cookie = true, bool $is_out_session = true, bool $is_out_server = false,
                             bool $is_bof = false, bool $is_run_time = true, string $date_dir = '_', ?string $filename = null): self
    {
        if (empty(static::$_instances[$channel])) {
            $dir                          = global_all('debug', Dir_Storage . 'logs/', 'out_dir');
            $filename                     = $dir . date('Y-m-d') . $date_dir . $channel . ($filename ? '_' . $filename : '.log');
            static::$_instances[$channel] = new static($filename, $is_out_buffer, $is_out_get, $is_out_post, $is_out_url,
                $is_out_cookie, $is_out_session, $is_out_server,
                $is_bof, $is_run_time);
        }
        return static::$_instances[$channel];
    }

    /**
     * 是否开启 http头debug
     * @return bool
     */
    public static function is_header(): bool
    {
        return global_all('debug', false, 'header');
    }
}
