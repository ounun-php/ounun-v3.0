<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

class debug
{
    /** @var array  日志数组 */
    private $_logs = [];
    /** @var string 输出文件名 */
    private $_logs_buffer = '';
    /** @var int    输出文件名 */
    private $_time = 0;

    /** @var string 输出文件名 */
    private $_filename = '';
    /** @var bool 是否添加到文件开头EOF */
    private $_is_bof = true;
    /** @var bool 是否输出 run time */
    private $_is_run_time = true;

    /** @var bool 是否输出 buffer */
    private $_is_out_buffer = true;
    /** @var bool 是否输出 get */
    private $_is_out_get = true;
    /** @var bool 是否输出 post */
    private $_is_out_post = true;
    /** @var bool 是否输出 url */
    private $_is_out_url = true;
    /** @var bool 是否输出 cookie */
    private $_is_out_cookie = true;
    /** @var bool 是否输出 session */
    private $_is_out_session = true;
    /** @var bool 是否输出 server */
    private $_is_out_server = false;


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
        if (class_exists('\ounun\template')) {
            template::ob_start();
        } else {
            ob_start();
        }
        register_shutdown_function(array($this, 'callback'));

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
     * @param string $k
     * @param mixed $log 日志内容
     * @param bool $is_replace 是否替换
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
                        $this->_logs[$k] = array($this->_logs[$k], $log);
                    }
                } else {
                    $this->_logs[$k] = $log;
                }
            }
            $this->write();
        }
    }

    /** 停止调试 */
    public function stop()
    {
        $this->_logs     = [];
        $this->_filename = '';
    }

    /** 内部内调 */
    public function callback()
    {
        $buffer = ob_get_contents();
        ob_clean();
        ob_implicit_flush(1);
        if ($this->_is_out_buffer) {
            $this->_logs_buffer = $buffer;
        }
        $this->write(true);
        exit($buffer);
    }

    /** 析构调试相关 */
    public function write(bool $is_end = false)
    {
        if (!$this->_filename) {
            return;
        }
        $filename = $this->_filename;
        $str      = '';
        // 环境参数
        if ($this->_is_out_url) {
            $this->_is_out_url = false;
            $str               .= date('Y-m-d H:i:s') . ' URL :' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https:' : 'http:') . '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
        }
        if ($this->_is_out_get && $_GET) {
            $this->_is_out_get = false;
            $t                 = [];
            foreach ($_GET as $k => $v) {
                $t[] = "{$k} => {$v}";
            }
            $str .= 'GET :' . implode("\n    ", $t) . PHP_EOL;
        }
        if ($this->_is_out_post && $_POST) {
            $str .= 'POST:' . var_export($_POST, true) . PHP_EOL;
        }
        if ($this->_is_out_post) {
            $this->_is_out_post = false;
            $input              = file_get_contents('php://input');
            if ($input) {
                $str .= 'INPUT:' . $input . PHP_EOL;
            }
        }
        if ($this->_is_out_cookie && $_COOKIE) {
            $this->_is_out_cookie = false;
            $str                  .= 'COOKIE:' . var_export($_COOKIE, true) . PHP_EOL;
        }
        if ($this->_is_out_session && $_SESSION) {
            $this->_is_out_session = false;
            $str                   .= 'SESSION:' . var_export($_SESSION, true) . PHP_EOL;
        }
        if ($this->_is_out_server && $_SERVER) {
            $this->_is_out_server = false;
            $str                  .= 'SERVER:' . var_export($_SERVER, true) . PHP_EOL;
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
                $run_time    = 'RunTime:' . sprintf('%f', $this->_time);
            } else {
                $run_time = '';
            }
            if ($this->_is_out_buffer && $this->_logs_buffer) {
                $str .= '--- DATE:' . date("Y-m-d H:i:s") . ' RunTime:' . $run_time . '---' . PHP_EOL . $this->_logs_buffer . PHP_EOL;
            }
            $this->_logs_buffer = '';
        }
        // 写文件
        if ($this->_is_bof) {
            if (file_exists($filename)) {
                $str = $str . '------------------' . PHP_EOL . file_get_contents($filename);
            }
            file_put_contents($filename, $str);
        } else {
            file_put_contents($filename, $str . '------------------' . PHP_EOL, FILE_APPEND);
        }
    }

    /** @var int header idx */
    static private $_header_idx = 0;

    /**
     * 在header输出头数据
     *
     * @param string $k
     * @param mixed $v
     * @param bool $debug
     * @param string $function
     * @param int $line
     */
    static public function header(string $k, $v, string $function = '', int $line = 0)
    {
        $debug = (\ounun::$global['debug'] && \ounun::$global['debug']['header']) ?? false;
        if ($debug && !headers_sent()) {
            static::$_header_idx++;
            if ($line) {
                $key[] = $line;
                if ($function) {
                    $key[] = $function;
                }
                if ($k) {
                    $key[] = $k;
                }
            } else {
                $key[] = $k;
                if ($function) {
                    $key[] = $function;
                }
            }
            $key = implode('-', $key);
            $idx = str_pad(static::$_header_idx, 4, '0', STR_PAD_LEFT);
            header("o{$idx}-{$key}: {$v}", false);
        }
    }

    /** @var self 单例模式 */
    static protected $_instances = [];

    /**
     * @param string $channel
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
     * @return $this 调试日志单例
     */
    public static function i($channel = 'comm', $filename = 'debug.txt', $is_out_buffer = true, $is_out_get = true, $is_out_post = true, $is_out_url = true,
                             $is_out_cookie = true, $is_out_session = true, $is_out_server = false,
                             $is_bof = false, $is_run_time = true): self
    {
        if (empty(static::$_instances[$channel])) {
            $dir                          = (\ounun::$global['debug'] && \ounun::$global['debug']['out']) ? \ounun::$global['debug']['out'] : Dir_Root . 'storage/logs/';
            $filename                     = $dir . date('Y-m-d') . '_' . $channel . '_' . $filename;
            static::$_instances[$channel] = new static($filename, $is_out_buffer, $is_out_get, $is_out_post, $is_out_url,
                $is_out_cookie, $is_out_session, $is_out_server,
                $is_bof, $is_run_time);
        }
        return static::$_instances[$channel];
    }
}
