<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\cache\driver;

/**
 * 文件类型缓存类(Code)
 */
class code extends \ounun\cache\driver
{
    /** @var string file类型 */
    const Type          = 'code';

    /** @var array 配制 */
    protected $_options = [
        // 'module'     => '', // 模块名称   转 prefix
        // 'filename'   => '', // 文件名
        'expire'        => 0,  // 有效时间 0为永久
        'serialize'     => ['json_encode_unescaped','json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_list'   => 't',

        // 'cache_subdir'  => true,   用 large_scale
        'path'          => Dir_Cache,
        'data_code'     => true,

        'data_compress' => false,
    ];

    protected $expire;

    /**
     * 构造函数
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        if (substr($this->_options['path'], -1) != '/') {
            $this->_options['path'] .= '/';
        }
        // 创建项目缓存目录
        if (!is_dir($this->_options['path'])) {
            if (mkdir($this->_options['path'], 0755, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 取得变量的存储文件名
     * @access protected
     * @param string $name 缓存变量名
     * @param bool $auto 是否自动创建目录
     * @return string
     */
    public function key_get2(string $name, $auto = false)
    {
        $name = md5($name);
        if ($this->_options['cache_subdir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . '/' . substr($name, 2);
        }
        if ($this->_options['prefix']) {
            $name = $this->_options['prefix'] . '/' . $name;
        }
        $filename = $this->_options['path'] . $name . '.php';
        $dir      = dirname($filename);

        if ($auto && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }


    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get2(string $key, $default = 0, bool $add_prefix = true)
    {
        $filename = $this->key_get($name);
        if (!is_file($filename)) {
            return $default;
        }
        $content      = file_get_contents($filename);
        $this->expire = null;
        if (false !== $content) {
            $expire = (int)substr($content, 8, 12);
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                return $default;
            }
            $this->expire = $expire;
            $content      = substr($content, 32);
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return $default;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer|\DateTime $expire 有效时间（秒）
     * @return boolean
     */
    public function set2($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        $filename = $this->key_get($name, true);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }
        $data = serialize($value);
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        $data   = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;
        $result = file_put_contents($filename, $data);
        if ($result) {
            isset($first) && $this->tag_item_set($filename);
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }



    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm2($name)
    {
        $filename = $this->key_get($name);
        return $this->unlink($filename);
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear2($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->tag_item_get($tag);
            foreach ($keys as $key) {
                $this->unlink($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        $files = (array)glob($this->options['path'] . ($this->options['prefix'] ? $this->options['prefix'] . '/' : '') . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . '/*.php');
                if (is_array($matches)) {
                    array_map('unlink', $matches);
                }
                // rmdir($path);
                $this->deldir($path);
            } else {
                unlink($path);
            }
        }
        return true;
    }


    /**
     * 清空文件夹函数和清空文件夹后删除空文件夹函数的处理
     * @param $path
     */
    private function deldir2($path)
    {
        //如果是目录则继续
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . $val)) {
                        //子目录中操作删除文件夹和文件
                        $this->deldir($path . $val . '/');
                        //目录清空后删除空文件夹
                        @rmdir($path . $val . '/');
                    } else {
                        //如果是文件直接删除
                        unlink($path . '/' . $val);
                    }
                }
            }
        }
    }

    /**
     * 判断文件是否存在后，删除
     * @param $path
     * @return bool
     * @return boolean
     */
    private function unlink2($path)
    {
        return is_file($path) && unlink($path);
    }

    /**
     * 获得数据
     * @param string $filename
     * @return mixed
     */
    static public function read2(string $filename)
    {
        if (file_exists($filename)) {
            return require $filename;
        }
        return null;
    }


    /**
     * 获得数据
     * @param string $filename
     * @param mixed  $data
     * @param bool   $recursive
     * @return mixed
     */
    static public function write2(string $filename, $data, bool $recursive = false)
    {
        if($recursive){
            $dir      = dirname($filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, $recursive);
            }
        }
        $str = var_export($data, true);
        return file_put_contents($filename, '<?php ' . "return {$str};\n");
    }


    public function get(string $key, $default = 0, bool $add_prefix = true, array $options = [])
    {
        // TODO: Implement get() method.
    }

    public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = [])
    {
        // TODO: Implement set() method.
    }

    public function incrby(string $key, int $increment = 1, bool $add_prefix = true)
    {
        // TODO: Implement incrby() method.
    }

    public function decrby(string $key, int $increment = 1, bool $add_prefix = true)
    {
        // TODO: Implement decrby() method.
    }

    public function exists(string $key, bool $add_prefix = true): bool
    {
        // TODO: Implement exists() method.
    }

    public function expire(string $key, int $expire = 0, bool $add_prefix = true): bool
    {
        // TODO: Implement expire() method.
    }

    public function delete(string $key, bool $add_prefix = true)
    {
        // TODO: Implement delete() method.
    }

    public function hash_hget(string $key, string $field, $default = 0, bool $add_prefix = true)
    {
        // TODO: Implement hash_hget() method.
    }

    public function hash_hset(string $key, string $field, $value, bool $add_prefix = true)
    {
        // TODO: Implement hash_hset() method.
    }

    public function hash_hincrby(string $key, string $field, int $increment = 1, bool $add_prefix = true)
    {
        // TODO: Implement hash_hincrby() method.
    }

    public function hash_hexists(string $key, string $field, bool $add_prefix = true): bool
    {
        // TODO: Implement hash_hexists() method.
    }

    public function hash_hdel(string $key, string $field, bool $add_prefix = true)
    {
        // TODO: Implement hash_hdel() method.
    }

    public function hash_hgetall(string $key, $default = [], bool $add_prefix = true): array
    {
        // TODO: Implement hash_hgetall() method.
    }

    public function list_lpush(string $key, $value, bool $add_prefix = true): int
    {
        // TODO: Implement list_lpush() method.
    }

    public function list_lpop(string $key = '', bool $add_prefix = true)
    {
        // TODO: Implement list_lpop() method.
    }

    public function list_rpush(string $key, $value, bool $add_prefix = true): int
    {
        // TODO: Implement list_rpush() method.
    }

    public function list_rpop(string $key = '', bool $add_prefix = true)
    {
        // TODO: Implement list_rpop() method.
    }

    public function list_lrange(string $key, int $start = 0, int $end = -1, bool $add_prefix = true): array
    {
        // TODO: Implement list_lrange() method.
    }

    public function list_length(string $key, bool $add_prefix = true): int
    {
        // TODO: Implement list_length() method.
    }

    public function key_get(string $key, bool $add_prefix = true, bool $is_list = false): string
    {
        // TODO: Implement key_get() method.
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }
}
