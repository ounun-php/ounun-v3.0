<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc;

/**
 * 缓存基础类
 * Class driver
 * @package ounun\cache
 */
abstract class driver
{
    /** @var mixed 驱动句柄 */
    protected $_handler;

    /** @var array 缓存 read:读取次数 write:写入次数 */
    protected $_times   = [ 'read'  => 0, 'write' => 0, ];

    /** @var array 缓存参数(配制数组) */
    protected $_options = [
     // 'module'        => '', // 模块名称   转 prefix
     // 'filename'      => '', // 文件名
        'expire'        => 0,  // 有效时间 0为永久
        'serialize'     => ['json_encode_unescaped','json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_tag'    => 't_'
    ];

    /** @var array 数据 */
    protected $_data    = [];

    /** @var array 缓存标签(标识名) */
    protected $_tags    = [];


    /**
     * 读取缓存
     * @param string $key 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    abstract public function get(string $key, $default = 0);

    /**
     * 写入缓存
     * @param string    $key     缓存变量名
     * @param mixed     $value   存储数据
     * @param int       $expire  有效时间（秒）
     * @return boolean
     */
    abstract public function set(string $key, $value,int $expire = 0);

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @return boolean
     */
    abstract public function delete(string $key);

    /**
     * 清除所有缓存
     * @param string $tag 标签名
     * @return boolean
     */
    abstract public function clear(string $tag);

    /**
     * 判断缓存是否存在
     * @param string $key 缓存变量名
     * @return bool
     */
    abstract public function has(string $key);

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
        return $this->_options['prefix'] .':'. $key;
    }

    /**
     * 读取缓存并删除
     * @param  string $key 缓存变量名
     * @return mixed
     */
    public function pull(string $key)
    {
        $result = $this->get($key, null);
        if ($result) {
            $this->delete($key);
            return $result;
        }
        return null;
    }

    /**
     * 追加（数组）缓存
     * @param  string $key         缓存变量名
     * @param  mixed  $value       存储数据
     * @param  int    $max_length  最大长度
     * @return void
     */
    public function push(string $key, $value, int $max_length = 1000): void
    {
        $item = $this->get($key, []);
        if (!is_array($item)) {
            throw new \InvalidArgumentException('only array cache can be push');
        }
        $item[] = $value;
        if (count($item) > $max_length) {
            array_shift($item);
        }
        $item = array_unique($item);
        $this->set($key, $item);
    }

    /**
     * 如果不存在则写入缓存
     * @param  string $key     缓存变量名
     * @param  mixed  $value   存储数据
     * @param  int    $expire  有效时间 0为永久
     * @return mixed
     */
    public function remember(string $key, $value, int $expire = 0)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $time = time();
        while ($time + 5 > time() && $this->has($key . '_lock')) {
            // 存在锁定则等待
            usleep(200000);
        }
        try {
            // 锁定
            $this->set($key . '_lock', true);
            // 缓存数据
            $this->set($key, $value, $expire);
            // 解锁
            $this->delete($key . '_lock');
        } catch (\Exception | \throwable $e) {
            $this->delete($key . '_lock');
            // throw $e;
        }
        return $value;
    }

    /**
     * 缓存标签
     * @param  string|array $name 标签名
     * @return tags
     */
    public function tag($name)
    {
        $name = (array) $name;
        $key  = implode('-', $name);
        if (!isset($this->_tags[$key])) {
            $name = array_map(function ($val) {
                return $this->tag_key_get($val);
            }, $name);
            $this->_tags[$key] = new tags($name, $this);
        }
        return $this->_tags[$key];
    }

    /**
     * 删除缓存标签
     * @param  array  $keys 缓存标识列表
     * @return void
     */
    public function tag_clear(array $keys): void
    {
        // 指定标签清除
        $this->_handler->del($keys);
    }

    /**
     * 获取标签包含的缓存标识
     * @param  string $tag 标签标识
     * @return array
     */
    public function tag_items_get(string $tag): array
    {
        $name = $this->tag_key_get($tag);
        return $this->get($name, []);
    }

    /**
     * 获取实际标签名
     * @param  string $tag 标签名
     * @return string
     */
    public function tag_key_get(string $tag): string
    {
        return $this->_options['prefix_tag'] . md5($tag);
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
     * 返回句柄对象，可执行其它高级方法
     * @return mixed
     */
    public function handler()
    {
        return $this->_handler;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return int
     */
    public function increase(string $name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) + $step;
            $expire = $this->_options['expire'];
        } else {
            $value  = $step;
            $expire = 0;
        }
        return $this->set($name, $value, $expire) ? $value : 0;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return int
     */
    public function decrease($name, $step = 1)
    {
        if ($this->has($name)) {
            $value  = $this->get($name) - $step;
            $expire = $this->_options['expire'];
        } else {
            $value  = -$step;
            $expire = 0;
        }
        return $this->set($name, $value, $expire) ? $value : 0;
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
     * @param  array  $keys 缓存变量名
     * @param  mixed  $default 默认值
     * @return iterable
     * @throws
     */
    public function multiple_get(array $keys, $default = 0): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * 写入缓存
     * @param  array    $values 缓存数据
     * @param  int      $expire    有效时间 0为永久
     * @return bool
     */
    public function multiple_set($values, $expire = 0): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $expire);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除缓存
     * @param array $keys 缓存变量名
     * @return bool
     * @throws
     */
    public function multiple_delete($keys): bool
    {
        foreach ($keys as $key) {
            $result = $this->delete($key);
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
