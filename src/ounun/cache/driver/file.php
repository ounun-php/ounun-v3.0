<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\cache\driver;

use ounun\cache\driver;

/**
 * 文件类型缓存类
 */
class file extends driver
{
    /** @var string file类型 */
    const Type          = 'file';

    /** @var array 配制 */
    protected array $_options = [
        // 'module'     => '', // 模块名称   转 prefix
        // 'filename'   => '', // 文件名
        'expire'    => 0,  // 有效时间 0为永久
        'serialize' => ['json_encode_unescaped', 'json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_list'   => 'f',

        // 'cache_subdir'  => true,   用 large_scale
        'path'          => Dir_Cache,
        'data_code'     => false,
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
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has(string $key, bool $add_prefix = true)
    {
        return $this->get($key) ? true : false;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = 0, bool $add_prefix = true, array $options = [])
    {
        $filename = $this->key_get($key,$add_prefix);
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
            $expire = $this->_options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        $filename = $this->key_get($name, true);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }
        $data = serialize($value);
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
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
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->expire;
        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->expire;
        } else {
            $value  = -$step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
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
    public function clear($tag = null)
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
    private function deldir($path)
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
    private function unlink($path)
    {
        return is_file($path) && unlink($path);
    }



    public function delete(string $key, bool $add_prefix = true)
    {
        // TODO: Implement delete() method.
    }

    public function key_set($key)
    {
        // TODO: Implement key() method.
    }

    public function val($val)
    {
        // TODO: Implement val() method.
    }

    public function read()
    {
        // TODO: Implement read() method.
    }

    public function write()
    {
        // TODO: Implement write() method.
    }

    public function get2($sub_key)
    {
        // TODO: Implement get2() method.
    }

    public function delete2()
    {
        // TODO: Implement delete2() method.
    }

    public function filename()
    {
        // TODO: Implement filename() method.
    }

    public function mod()
    {
        // TODO: Implement mod() method.
    }

    /**
     * 读取缓存并删除
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return mixed
     */
    public function pull(string $key, bool $add_prefix = true)
    {
        $result = $this->get($key, null, $add_prefix);
        if ($result) {
            $this->delete($key, $add_prefix);
            return $result;
        }
        return null;
    }

    /**
     * 追加（数组）缓存
     * @param string $key 缓存变量名
     * @param mixed $value 存储数据
     * @param int $expire 有效时间 0为永久
     * @param int $max_length 最大长度
     * @param bool $add_prefix 是否活加前缀
     * @return void
     */
    public function push(string $key, $value, int $max_length = 1000, int $expire = 0, bool $add_prefix = true): void
    {
        $item = $this->get($key, [], $add_prefix);
        if (!is_array($item)) {
            throw new \InvalidArgumentException('only array cache can be push');
        }
        $item[] = $value;
        if (count($item) > $max_length) {
            array_shift($item);
        }
        $item = array_unique($item);
        $this->set($key, $item, $expire, $add_prefix);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @param string    $name        缓存变量名
     * @param int       $step        步长
     * @param  bool     $add_prefix  是否活加前缀
     * @return int
     */
    public function increase(string $name,int $step = 1, bool $add_prefix = true)
    {
        if ($this->has($name)) {
            $value  = $this->get($name,$add_prefix) + $step;
            $expire = $this->_options['expire'];
        } else {
            $value  = $step;
            $expire = 0;
        }
        return $this->set($name, $value, $expire,$add_prefix) ? $value : 0;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @param string    $name        缓存变量名
     * @param int       $step        步长
     * @param  bool     $add_prefix  是否活加前缀
     * @return int
     */
    public function decrease(string $name, int $step = 1, bool $add_prefix = true)
    {
        if ($this->has($name,$add_prefix)) {
            $value  = $this->get($name) - $step;
            $expire = $this->_options['expire'];
        } else {
            $value  = -$step;
            $expire = 0;
        }
        return $this->set($name, $value, $expire,$add_prefix) ? $value : 0;
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

    public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = [])
    {
        // TODO: Implement set() method.
    }

    public function key_get(string $key, bool $add_prefix = true, bool $is_list = false): string
    {
        // TODO: Implement key_get() method.
    }
}
