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
    /** @var string 缓存标签(标识名) */
    protected $_tagset_tag = '';

    /** @var array 缓存 read:读取次数 write:写入次数 */
    protected $_times = ['read' => 0, 'write' => 0,];

    /** @var array 缓存参数(配制数组) */
    protected $_options = [
        // 'module'        => '', // 模块名称   转 prefix
        // 'filename'      => '', // 文件名
        'expire'    => 0,  // 有效时间 0为永久
        'serialize' => ['json_encode_unescaped', 'json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_tag'    => 't'
    ];

    /** @var array 数据 */
    protected $_data = [];

    /** @var array ['key'=>$key,'value'=>$value] 设置key的过期时间，超过时间后，将会自动删除该key */
    protected $_expires = [];


    /**
     * 读取缓存
     * @param string $key 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @return mixed
     */
    abstract public function get(string $key, $default = 0, bool $add_prefix = true);

    /**
     * 写入缓存
     * @param string $key 缓存变量名
     * @param mixed $value 存储数据
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    abstract public function set(string $key, $value, int $expire = 0, bool $add_prefix = true);

    /**
     * 返回key是否存在
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    abstract public function exists(string $key, bool $add_prefix = true): bool;

    /**
     * 设置key的过期时间，超过时间后，将会自动删除该key
     * @param string $key 缓存变量名
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    abstract public function expire(string $key, int $expire = 0, bool $add_prefix = true): bool;

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    abstract public function delete(string $key, bool $add_prefix = true);

    /**
     * 获取实际的缓存标识
     * @param string $key 缓存名
     * @param bool $add_prefix 是否活加前缀
     * @return string
     */
    abstract public function key_get(string $key, bool $add_prefix = true): string;

    /**
     * 自增缓存（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    abstract public function incr(string $key, int $step = 1, bool $add_prefix = true): int;

    /**
     * 自减缓存（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    abstract public function decr(string $key, int $step = 1, bool $add_prefix = true): int;


    /**
     * 向存于 key 的列表的尾部插入所有指定的值。如果 key 不存在，那么会创建一个空的列表然后再进行 push 操作。 当 key 保存的不是一个列表，那么会返回一个错误。
     * @param string $key
     * @param bool $add_prefix
     * @return int
     */
    abstract public function list_lpush(string $key, $value, bool $add_prefix = true): int;

    /**
     * 移除并且返回 key 对应的 list 的第一个元素。
     * @param string $key
     * @param bool $add_prefix
     * @return mixed
     */
    abstract public function list_lpop(string $key = '', bool $add_prefix = true);

    /**
     * 向存于 key 的列表的尾部插入所有指定的值。如果 key 不存在，那么会创建一个空的列表然后再进行 push 操作。 当 key 保存的不是一个列表，那么会返回一个错误。
     * @param string $key
     * @param bool $add_prefix
     * @return int
     */
    abstract public function list_rpush(string $key, $value, bool $add_prefix = true): int;

    /**
     * 移除并返回存于 key 的 list 的最后一个元素。
     * @param string $key
     * @param bool $add_prefix
     * @return mixed
     */
    abstract public function list_rpop(string $key = '', bool $add_prefix = true);

    /**
     * 获取标签包含的缓存标识
     *   返回存储在 key 的列表里指定范围内的元素。 start 和 end 偏移量都是基于0的下标，即list的第一个元素下标是0（list的表头），第二个元素下标是1，以此类推。
     *   偏移量也可以是负数，表示偏移量是从list尾部开始计数。 例如， -1 表示列表的最后一个元素，-2 是倒数第二个，以此类推。
     *
     * @param string $key 标签标识
     * @param int $start
     * @param int $end
     * @param bool $add_prefix 是否活加前缀
     * @return array
     */
    abstract public function list_lrange(string $key, int $start = 0, int $end = -1, bool $add_prefix = true): array;

    /**
     * 返回存储在 key 里的list的长度。 如果 key 不存在，那么就被看作是空list，并且返回长度为 0。 当存储在 key 里的值不是一个list的话，会返回error。
     * @param string $key 标签名
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    abstract public function list_length(string $key, bool $add_prefix = true): int;

    /**
     * 获取实际标签名
     * @param string $key 标签名
     * @param bool $add_prefix 是否活加前缀
     * @return string
     */
    abstract public function list_key_get(string $key, bool $add_prefix = true): string;

    /**
     * 删除缓存标签
     * @param string $key 标签标识
     * @param bool $add_prefix 是否活加前缀
     * @return void
     */
    public function list_clear(string $key, bool $add_prefix = true): void
    {
        $keys = $this->list_lrange($key, 0, -1, $add_prefix);
        // 指定标签清除
        $this->multiple_delete($keys, false);

        $this->delete($this->list_key_get($key), false);
    }

    /**
     * 取得  prefix | module:名称
     * @return string
     */
    public function prefix()
    {
        return $this->_options['prefix'];
    }

    /**
     * 序列化数据
     * @param mixed $data 缓存数据
     * @return string
     */
    protected function serialize($data): string
    {
        $serialize = $this->_options['serialize'][0] ?? function ($data) {
                return \serialize($data);
            };
        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @param string $data 缓存数据
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        $unserialize = $this->_options['serialize'][1] ?? function ($data) {
                return \unserialize($data);
            };
        return $unserialize($data);
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
     * @param array $keys 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @return array
     * @throws
     */
    public function multiple_get(array $keys, $default = 0, bool $add_prefix = true): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default, $add_prefix);
        }
        return $result;
    }

    /**
     * 写入缓存
     * @param array $values 缓存数据
     * @param int $expire 有效时间 0为永久
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    public function multiple_set($values, $expire = 0, bool $add_prefix = true): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $expire, $add_prefix);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除缓存
     * @param array $keys 缓存变量名
     * @param bool $add_prefix 是否活加前缀
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
     * 返回句柄对象，可执行其它高级方法
     * @return mixed
     */
    public function handler()
    {
        return $this->_handler;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ($this->_handler) {
            return call_user_func_array([$this->_handler, $method], $args);
        }
        return null;
    }
}
