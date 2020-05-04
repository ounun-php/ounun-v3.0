<?php


namespace ounun\cache;

use ounun\template;

class html
{
    /** Cache最小文件大小           */
    const Cache_Mini_Size = 2024;
    /** Cache生成过程最长临时过度时间 */
    const Cache_Time_Interval = 300;

    /** 全部 - Cdn 类型  */
    const Cdn_Type_Full = 1;
    /** CDN只存etag/expiry/mtime(Redis,Memcached,Sqlite) - Cdn 类型   */
    const Cdn_Type_Min = 2;

    /** @var bool */
    public $stop = false;

    /** @var driver 缓存驱动 */
    protected $_cache_driver;
    /** @var string 页面key */
    protected $_cache_key = '';
    /** @var array 数据 */
    protected $_cache_value = [];
    /** @var string 类型 */
    protected $_cache_type = '';
    /** @var string 缓存文件 */
    protected $_cache_filename = '';

    /** @var int */
    protected $_cache_time = -1;
    /** @var int */
    protected $_cache_time_t = -1;
    /** @var int */
    protected $_cache_size = -1;
    /** @var int */
    protected $_cache_size_t = -1;

    /** @var int 当前时间 */
    protected $_time_curr;
    /** @var int 缓存时间长度 */
    protected $_time_expire = 3600;


    // 下面 高级应用
    /** @var bool 是否 启用压缩 */
    protected $_is_gzip = true;
    /** @var bool 是否 去空格换行 */
    protected $_is_trim = false;
    /** @var bool false:没读    true:已读 */
    protected $_is_read = false;

    /**
     * html constructor.
     * 创建缓存对像
     *
     * @param $config
     * @param string $key
     * @param int $expire
     * @param bool $trim
     * @param bool $debug
     */
    public function __construct($config, string $key = '', int $expire = 3600, bool $trim = true, bool $debug = true)
    {
        $this->stop = false;
        // 初始化参数
        $this->_time_expire = $expire;
        $this->_time_curr   = time();

        $this->_cache_time = 0;

        $this->_is_trim  = $trim;
        $this->_is_debug = $debug;
        // 是否支持gzip
        if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            $this->_is_gzip = false;
        } else {
            $this->_is_gzip = true;
        }
        // Cache
        $this->_cache_type = $config['driver_type'];
        if (driver\file::Type == $config['driver_type']) {
            $config['format_string'] = true;
            $this->_cache_driver     = new driver\file($config);
        } elseif (driver\memcached::Type == $config['driver_type']) {
            $config['format_string'] = false;
            $this->_cache_driver     = new driver\memcached($config);
        } elseif (driver\redis::Type == $config['driver_type']) {
            $config['format_string'] = false;
            $this->_cache_driver     = new driver\redis($config);
        } else {
            $config['format_string'] = true;
            $this->_cache_type       = driver\file::Type;
            $this->_cache_driver     = new driver\file($config);
        }
        $this->_cache_key = $key;
    }

    /**
     * [1/1] 判断->执行缓存->输出
     * @param bool $output ( 是否输出 )
     */
    public function run(bool $output = true)
    {
        // 是否清理本缓存
        if ($_GET && $_GET['clean']) {
            unset($_GET['clean']);
            $this->clean();
        }
        // 执行
        $is_cache = $this->run_cache_check();
        if ($is_cache) {
            if ($output) {
                $this->run_output();
            }
        } else {
            $this->run_execute($output);
        }
    }

    /**
     * [1/3] 判断是否存缓存
     * @return bool
     */
    public function run_cache_check()
    {
        $this->cache_time();
        \ounun\debug::header('time', $this->_cache_time, __FUNCTION__, __LINE__);
        \ounun\debug::header('expire', $this->_time_expire,  __FUNCTION__, __LINE__);
        if ($this->_cache_time + $this->_time_expire > $this->_time_curr) {
            \ounun\debug::header('xypc', $this->filename(),  __FUNCTION__, __LINE__);
            return true;
        }
        $cache_time_t = $this->cache_time_tmp();
        \ounun\debug::header('time_t', $cache_time_t,  __FUNCTION__, __LINE__);
        if ($cache_time_t + self::Cache_Time_Interval > $this->_time_curr) {
            \ounun\debug::header('xypc_t', $this->filename() . '.t time:' . $cache_time_t, __FUNCTION__, __LINE__);
            return true;
        }
        $this->_cache_time = 0;
        return false;
    }

    /**
     * [2/3] 执行缓存程序
     * @param bool $outpt ( 是否输出 )
     */
    public function run_execute(bool $output)
    {
        \ounun\debug::header('xypm', $this->filename(),  __FUNCTION__, __LINE__);
        $this->stop = false;
        $this->cache_time_tmp_set();
        // 生成
        // ob_start();
        template::ob_start();
        register_shutdown_function([$this, 'callback'], $output);
    }

    /**
     * [3/3] 输出缓存
     */
    public function run_output()
    {
        if ($this->_cache_time) {
            // 处理 etag
            $etag      = $this->_cache_time;
            $etag_http = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';

            // 处理 cache expire
            header('Expires: ' . gmdate('D, d M Y H:i:s', $this->_time_curr + $this->_time_expire) . ' GMT');
            header('Cache-Control: max-age=' . $this->_time_expire);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->_cache_time) . ' GMT');

            if ($etag && $etag == $etag_http) {
                header('Etag: ' . $etag, true, 304);
                exit;
            }
            header('Etag: ' . $etag);
            // 输出
            $this->cache_out($this->_is_gzip);
        }
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * 创建缓存
     * @param bool $output 是否有输出
     */
    public function callback(bool $output)
    {
        if ($this->stop) {
            return;
        }
        // 执行
        $buffer   = ob_get_contents();
        $filesize = strlen($buffer);
        ob_clean();
        ob_implicit_flush(1);
        // 写文件
        \ounun\debug::header('xypm_size', $filesize,  __FUNCTION__, __LINE__);
        if ($filesize > self::Cache_Mini_Size) {
            \ounun\debug::header('xypm_ok', $this->filename(),  __FUNCTION__, __LINE__);

            $buffer = template::trim($buffer, $this->_is_trim);
            $buffer = gzencode($buffer, 9);
            $this->cache_html($buffer);
            $this->_cache_time = $this->cache_time();
            if ($output) {
                $this->run_output();
            }
        } else {
            $this->clean();
            \ounun\debug::header('xypm_noc', 'nocache',  __FUNCTION__, __LINE__);
            if ($output) {
                header('Content-Length: ' . $filesize);
                exit($buffer);
            }
        }
    }

    /**
     * 停止Cache
     * @param $output
     */
    public function stop(bool $output)
    {
        $this->stop = true;
        if ($output) {
            \v::$tpl->replace();
            $this->run_output();
        }
    }

    /**
     * 是否清理本缓存
     * @return bool
     */
    public function clean()
    {
        $this->_cache_time   = -1;
        $this->_cache_time_t = -1;
        $this->_cache_size   = -1;
        $this->_cache_size_t = -1;

        $filename = $this->filename() . '.t';

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return $this->_cache_driver->delete($this->_cache_key);
    }

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    public function filename()
    {
        if (empty($this->_cache_filename)) {
            $this->_cache_filename = $this->_cache_driver->cache_key_get($this->_cache_key);
        }
        return $this->_cache_filename;
    }


    /**
     * 看是否存在cache
     * @return int 小于0:无Cache 大于0:创建Cache时间
     */
    public function cache_time(): int
    {
        if (0 <= $this->_cache_time) {
            return $this->_cache_time;
        }
        //
        $this->_cache_time = 0;
        if (driver\file::Type == $this->_cache_type) {
            $filename = $this->filename();
            \ounun\debug::header('filename', $filename,  __FUNCTION__, __LINE__);
            if (file_exists($filename)) {
                $this->_cache_time = filemtime($filename);
                \ounun\debug::header('cache_time', $this->_cache_time,  __FUNCTION__, __LINE__);
            }
        } else {
            $this->_cache_time = (int)$this->_cache_value['filemtime'];
        }
        return $this->_cache_time;
    }

    /**
     * 文件生成时间(临时)
     * @return int 文件生成时间(临时)
     */
    public function cache_time_tmp()
    {
        if (0 <= $this->_cache_time_t) {
            return $this->_cache_time_t;
        }
        //
        $this->_cache_time_t = 0;
        if (driver\file::Type == $this->_cache_type) {
            $filename = $this->filename() . '.t';
            \ounun\debug::header('file', $filename, __FUNCTION__, __LINE__);
            if (file_exists($filename)) {
                $this->_cache_time_t = filemtime($filename);
                $this->_cache_size_t = filesize($filename);
                \ounun\debug::header('time', $this->_cache_time_t,  __FUNCTION__, __LINE__);
            }
        } else {
            $this->_cache_time_t = (int)$this->_cache_value['filemtime_t'];
        }
        return $this->_cache_time_t;
    }

    /**
     * 文件大小(临时)
     * @return int
     */
    public function cache_size_tmp()
    {
        return $this->_cache_size_t;
    }

    /**
     * 标记(临时)
     */
    public function cache_time_tmp_set()
    {
        $this->_cache_time_t = time();
        if (driver\file::Type == $this->_cache_type) {
            $filename = $this->filename() . '.t';
            \ounun\debug::header('file', $filename,  __FUNCTION__, __LINE__);
            if (file_exists($filename)) {
                touch($filename);
            } else {
                $filedir = dirname($filename);
                if (!is_dir($filedir)) {
                    mkdir($filedir, 0777, true);
                }
                touch($filename);
            }
        } else {
            $this->_cache_value['filemtime_t'] = $this->_cache_time_t;
            $this->_cache_driver->set($this->_cache_key, $this->_cache_value);
        }
    }

    /**
     * 文件大小
     * @return int 文件大小
     */
    public function cache_size()
    {
        if (0 <= $this->_cache_size) {
            return $this->_cache_size;
        }
        if (driver\file::Type == $this->_cache_type) {
            $filename = $this->filename();
            \ounun\debug::header('file', $filename,  __FUNCTION__, __LINE__);
            if (file_exists($filename)) {
                $this->_cache_size = filesize($filename);
                \ounun\debug::header('size', $this->_cache_size, __FUNCTION__, __LINE__);
            }
            $this->_cache_size = 0;
        } else {
            $this->_cache_size = (int)$this->_cache_value['filesize'];
        }
        return $this->_cache_size;
    }

    /**
     * 保存数据
     */
    public function cache_html($html)
    {
        $this->_cache_time = time();
        if (driver\file::Type == $this->_cache_type) {
            $this->_cache_driver->set($this->_cache_key, $html, $this->_time_expire);
            $filename = $this->filename() . '.t';
            \ounun\debug::header('delfile', $filename,  __FUNCTION__, __LINE__);
            if (file_exists($filename)) {
                unlink($filename);
            }
        } else {
            $html = ['filemtime' => $this->_cache_time, 'filesize' => strlen($html), 'html' => $html];
            $this->_cache_driver->set($this->_cache_key, $html, $this->_time_expire);
        }
    }

    /**
     * 保存数据
     */
    public function cache_out($gzip)
    {
        // 输出
        if ($gzip) {// 输出 ( 支持 gzip )
            header('Content-Encoding: gzip');
            if (driver\file::Type == $this->_cache_type) {
                $filename = $this->filename();
                header('Content-Length: ' . filesize($filename));
                readfile($filename);
                exit;
            } else {
                header('Content-Length: ' . $this->_cache_value['filesize']);
                exit($this->_cache_value['html']);
            }
        } else {// 输出 ( 不支持 gzip )
            if (driver\file::Type == $this->_cache_type) {
                $filename = $this->filename();
                $content  = file_get_contents($filename);
            } else {
                $content = $this->_cache_value['html'];
            }
            $content  = gzdecode($content);
            $filesize = strlen($content);
            header('Content-Length: ' . $filesize);
            exit($content);
        }
    }

}
