<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

class debug
{
    /** @var array  日志数组 */
    private array $_logs = [];
    /** @var string|null 输出文件名 */
    private ?string $_logs_buffer;
    /** @var int    输出文件名 */
    private int $_time = 0;

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
    public function __construct($filename = 'debug.txt',
                                $is_out_buffer = true, $is_out_get = true, $is_out_post = true, $is_out_url = true,
                                $is_out_cookie = true, $is_out_session = true, $is_out_server = false,
                                $is_bof = false, $is_run_time = true)
    {
        if ($filename) {
            $dirname = dirname($filename);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
        }
        //
        if (class_exists(template::class) && template::ob_status()) {
            template::ob_func([$this, 'callback'], []);
        } else {
            ob_start();
            register_shutdown_function([$this, 'callback']);
        }

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

        // init写
        $this->write();

        // header
        // static::$_header_idx = 0;
    }

    /**
     * 通过配置单独定义函数的配置
     * @param string $k buffer/get/post/url
     * @param bool $v
     * @return debug
     */
    public function out(string $k, bool $v)
    {
        $_k        = '_is_out_' . $k;
        $this->$_k = $v;
        return $this;
    }


    /**
     * 设置文件路径
     * @param string $filename 文件路径
     * @return debug
     */
    public function filename(string $filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * 运行时间是否显示
     * @param bool $show true:显示 false:不显示
     * @return debug
     */
    public function run_time(bool $show)
    {
        $this->_is_run_time = $show;
        return $this;
    }

    /**
     * 日志追加位置
     *
     * @param bool $bof true:倒序(新内容在头部) false:正序(新内容在尾部)
     * @return debug
     */
    public function bof(bool $bof)
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
     * @return debug
     */
    public function logs(string $k, $log, $is_replace = true)
    {
        if ($k && $log) {
            // 直接替换
            if ($is_replace) {
                $this->_logs[$k] = $log;
            } else {
                if ($this->_logs[$k]) {
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
    public function stop()
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
            ob_implicit_flush(1);
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
     * 析构调试相关
     *
     * @param bool $is_end 是否当前请求 最后一次写入
     * @return debug
     */
    public function write(bool $is_end = false)
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
            if ($this->_is_out_cookie && $_COOKIE) {
                $this->_is_out_cookie = false;
                $str                  .= 'COOKIE:' . var_export($_COOKIE, true) . PHP_EOL;
            }
            // session
            if ($this->_is_out_session && $_SESSION) {
                $this->_is_out_session = false;
                $str                   .= 'SESSION:' . var_export($_SESSION, true) . PHP_EOL;
            }
            // server
            if ($this->_is_out_server && $_SERVER) {
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
            file_put_contents($filename, $str);
        } elseif ($str) {
            file_put_contents($filename, $str, FILE_APPEND);
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
    static public function header($v, string $k = '', string $filename = '', int $line = 0)
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
            $idx = str_pad(static::$_header_idx, 4, '0', STR_PAD_LEFT);
            header("o{$idx}-{$key}: {$v}", false);
        }
    }

    /** @var self 单例模式 */
    static protected $_instances = [];

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
    public static function i(string $channel = 'comm', $is_out_buffer = true, $is_out_get = true, $is_out_post = true, $is_out_url = true,
                             $is_out_cookie = true, $is_out_session = true, $is_out_server = false,
                             $is_bof = false, $is_run_time = true, string $date_dir = '_', ?string $filename = null): self
    {
        if (empty(static::$_instances[$channel])) {
            $debug                        = global_all('debug', []);
            $dir                          = ($debug && $debug['out_dir']) ? $debug['out_dir'] : Dir_Storage . 'logs/';
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
    public static function is_header()
    {
        $debug = global_all('debug');
        return ($debug && $debug['header']) ?? false;
    }
}
