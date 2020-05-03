<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\cache;

/**
 * 缓存基础类
 * Class driver
 * @package ounun\cache
 */
abstract class driver
{
    /** @var mixed 驱动句柄 */
    protected $_handler;
    /** @var tagset 缓存标签集 */
    protected $_tagset;
    /** @var string 缓存标签(标识名) */
    protected $_tagset_tag  = '';

    /** @var array 缓存 read:读取次数 write:写入次数 */
    protected $_times       = [ 'read'  => 0, 'write' => 0, ];

    /** @var array 缓存参数(配制数组) */
    protected $_options = [
     // 'module'        => '', // 模块名称   转 prefix
     // 'filename'      => '', // 文件名
        'expire'        => 0,  // 有效时间 0为永久
        'serialize'     => ['json_encode_unescaped','json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_tag'    => 't'
    ];

    /** @var array 数据 */
    protected $_data    = [];


    /**
     * 读取缓存
     * @param  string    $key         缓存变量名
     * @param  mixed     $default     默认值
     * @param  bool      $add_prefix  是否活加前缀
     * @return mixed
     */
    abstract public function get(string $key, $default = 0, bool $add_prefix = true);

    /**
     * 写入缓存
     * @param  string    $key         缓存变量名
     * @param  mixed     $value       存储数据
     * @param  int       $expire      有效时间（秒）
     * @param  bool      $add_prefix  是否活加前缀
     * @return bool
     */
    abstract public function set(string $key, $value,int $expire = 0, bool $add_prefix = true);

    /**
     * 判断缓存是否存在
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return bool
     */
    public function has(string $key, bool $add_prefix = true)
    {
        return $this->get($key,$add_prefix) ? true : false;
    }

    /**
     * 删除缓存
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return bool
     */
    abstract public function delete(string $key, bool $add_prefix = true);

    /**
     * 清除所有缓存
     * @return bool
     */
    abstract public function clear();

    /**
     * 取得  prefix | module:名称
     * @return string
     */
    public function prefix()
    {
        return $this->_options['prefix'];
    }

    /**
     * 获取实际的缓存标识
     * @param  string $key 缓存名
     * @return string
     */
    public function cache_key_get(string $key): string
    {
        if($this->_options['prefix']){
            return $this->_options['prefix'] .':'. $key;
        }
        return $key;
    }

    /**
     * 读取缓存并删除
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return mixed
     */
    public function pull(string $key, bool $add_prefix = true)
    {
        $result = $this->get($key, null,$add_prefix);
        if ($result) {
            $this->delete($key,$add_prefix);
            return $result;
        }
        return null;
    }

    /**
     * 追加（数组）缓存
     * @param  string $key         缓存变量名
     * @param  mixed  $value       存储数据
     * @param  int    $expire      有效时间 0为永久
     * @param  int    $max_length  最大长度
     * @param  bool   $add_prefix  是否活加前缀
     * @return void
     */
    public function push(string $key, $value, int $max_length = 1000,int $expire = 0, bool $add_prefix = true): void
    {
        $item = $this->get($key, [],$add_prefix);
        if (!is_array($item)) {
            throw new \InvalidArgumentException('only array cache can be push');
        }
        $item[] = $value;
        if (count($item) > $max_length) {
            array_shift($item);
        }
        $item = array_unique($item);
        $this->set($key, $item,$expire,$add_prefix);
    }

    /**
     * 如果不存在则写入缓存
     * @param  string $key         缓存变量名
     * @param  mixed  $value       存储数据
     * @param  int    $expire      有效时间 0为永久
     * @param  bool   $add_prefix  是否活加前缀
     * @return mixed
     */
    public function remember(string $key, $value, int $expire = 0, bool $add_prefix = true)
    {
        if ($this->has($key,$add_prefix)) {
            return $this->get($key,$add_prefix);
        }

        $time = time();
        while ($time + 5 > time() && $this->has($key . '_lock',$add_prefix)) {
            // 存在锁定则等待
            usleep(200000);
        }
        try {
            // 锁定
            $this->set($key . '_lock', true,0,$add_prefix);
            // 缓存数据
            $this->set($key, $value, $expire,$add_prefix);
            // 解锁
            $this->delete($key . '_lock',$add_prefix);
        } catch (\Exception | \throwable $e) {
            $this->delete($key . '_lock',$add_prefix);
            // throw $e;
        }
        return $value;
    }

    /**
     * 缓存标签
     * @param  string $tagset_tag  标签名
     * @param  int    $max_length  最大长度
     * @return tagset
     */
    public function tagset(string $tagset_tag = '', int $max_length = 10000)
    {
        if(empty($tagset_tag) && $this->_tagset){
            return $this->_tagset;
        }elseif($tagset_tag == $this->_tagset_tag && $this->_tagset){
            return $this->_tagset;
        }
        $this->_tagset_tag    = $tagset_tag;
        $this->_tagset = new tagset($tagset_tag, $this,$max_length);
        return $this->_tagset;
    }

    /**
     * 删除缓存标签
     * @param  string   $tagset_tag  标签标识
     * @param  bool     $add_prefix  是否活加前缀
     * @return void
     */
    public function tagset_clear(string $tagset_tag, bool $add_prefix = true): void
    {
        $keys = $this->tagset_items_get($tagset_tag,$add_prefix);
        // 指定标签清除
        $this->multiple_delete($keys,false);

        $this->delete($this->tagset_key_get($tagset_tag),false);
    }

    /**
     * 获取标签包含的缓存标识
     * @param  string   $tagset_tag         标签标识
     * @param  bool     $add_prefix  是否活加前缀
     * @return array
     */
    public function tagset_items_get(string $tagset_tag, bool $add_prefix = true): array
    {
        if($add_prefix){
            $tagset_tag = $this->tagset_key_get($tagset_tag);
        }
        return $this->get($tagset_tag, [],false);
    }

    /**
     * 获取实际标签名
     * @param  string $tagset_tag 标签名
     * @return string
     */
    public function tagset_key_get(string $tagset_tag): string
    {
        if($this->_options['prefix_tag']){
            if($this->_options['prefix']){
                return $this->_options['prefix'] .':'.$this->_options['prefix_tag'].':'. $tagset_tag;
            }
            return 'c:'.$this->_options['prefix_tag']. ':' . $tagset_tag;
        }
        if($this->_options['prefix']){
            return $this->_options['prefix'] .':t:'. $tagset_tag;
        }
        return 'c:t:'.$tagset_tag;
    }

    /**
     * 序列化数据
     * @param  mixed $data 缓存数据
     * @return string
     */
    protected function serialize($data): string
    {
        $serialize = $this->_options['serialize'][0] ?? function ($data) { return \serialize($data); };
        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @param  string $data 缓存数据
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        $unserialize = $this->_options['serialize'][1] ?? function ($data) { return \unserialize($data);};
        return $unserialize($data);
    }


    /**
     * @var string 模块名称
     */
    protected $_mod;

    /**
     * 设定数据keys
     * @param $key
     */
    abstract public function key($key);

    /**
     * 设定数据Value
     * @param $val
     */
    abstract public function val($val);

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    abstract public function read();

    /**
     * 写入已设定的数据
     * @return bool
     */
    abstract public function write();

    /**
     * 读取数据中$key的值
     * @param $sub_key
     */
    abstract public function get2($sub_key);

    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals
     */
    abstract public function set2($sub_key, $sub_val);

    /**
     * 删除数据
     * @return bool
     */
    abstract public function delete2();

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    abstract public function filename();

    /**
     * 取得 mod:名称
     * @return string
     */
    abstract public function mod();

    /**
     * 返回句柄对象，可执行其它高级方法
     * @return mixed
     */
    public function handler()
    {
        return $this->_handler;
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


    /**
     * 返回缓存读取次数
     * @return int
     */
    public function times_read_get(): int
    {
        return $this->_times['read'];
    }

    /**
     * 返回缓存写入次数
     * @return int
     */
    public function times_write_get(): int
    {
        return $this->_times['write'];
    }

    /**
     * 读取缓存
     * @param  array    $keys        缓存变量名
     * @param  mixed    $default     默认值
     * @param  bool     $add_prefix  是否活加前缀
     * @return array
     * @throws
     */
    public function multiple_get(array $keys, $default = 0, bool $add_prefix = true): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default,$add_prefix);
        }
        return $result;
    }

    /**
     * 写入缓存
     * @param  array    $values      缓存数据
     * @param  int      $expire      有效时间 0为永久
     * @param  bool     $add_prefix  是否活加前缀
     * @return bool
     */
    public function multiple_set($values, $expire = 0, bool $add_prefix = true): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $expire,$add_prefix);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除缓存
     * @param array $keys        缓存变量名
     * @param bool  $add_prefix  是否活加前缀
     * @return bool
     * @throws
     */
    public function multiple_delete($keys, bool $add_prefix = true): bool
    {
        foreach ($keys as $key) {
            $result = $this->delete($key, $add_prefix);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if($this->_handler){
            return call_user_func_array([$this->_handler, $method], $args);
        }
        return null;
    }
}
