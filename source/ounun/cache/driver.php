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
    protected \Redis $_handler;

    /** @var array 缓存 get read:读取次数 set write:写入次数 */
    protected array $_times = ['get' => 0, 'set' => 0,];

    /** @var array 缓存参数(配制数组) */
    protected array $_options = [
        'expire'    => 0,  // 有效时间 0为永久
        'serialize' => ['json_encode_unescaped', 'json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据  true:字符串
        'data_compress' => false, // bool false:不压缩    true:压缩
        'large_scale'   => false, // bool false:少量      true:大量

        'prefix'      => '',    // 模块名称
        'prefix_list' => 't',
    ];

    /** @var array 数据 */
    protected array $_value = [];

    /** @var array ['key'=>$key,'value'=>$value] 设置key的过期时间，超过时间后，将会自动删除该key */
    protected array $_expires = [];

    /**
     * 读取缓存
     * @param string $key 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @param array $options 参数 ['compress'=>$compress 是否返回压缩后的数据 ]
     * @return mixed
     */
    abstract public function get(string $key, $default = 0, bool $add_prefix = true, array $options = []);

    /**
     * 写入缓存
     * @param string $key 缓存变量名
     * @param mixed $value 存储数据
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @param array $options 参数 ['list_key'=>$list_key 汇总集合list标识 ]
     * @return bool
     */
    abstract public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = []);

    /**
     * 增加之后的value值。（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $increment 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    abstract public function incrby(string $key, int $increment = 1, bool $add_prefix = true);

    /**
     * 返回一个数字：减少之后的value值。（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $increment 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    abstract public function decrby(string $key, int $increment = 1, bool $add_prefix = true);

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
     * 清除所有缓存
     * @return bool
     */
    abstract public function clear();

    /**
     * 返回 key 指定的哈希集中该字段所关联的值
     * @param string $key
     * @param string $field
     * @param int $default
     * @param bool $add_prefix
     * @return int|string
     */
    abstract public function hash_hget(string $key, string $field, $default = 0, bool $add_prefix = true);

    /**
     * 设置 key 指定的哈希集中指定字段的值。
     * 如果 key 指定的哈希集不存在，会创建一个新的哈希集并与 key 关联。
     * 如果字段在哈希集中存在，它将被重写。
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @param bool $add_prefix
     * @return string
     */
    abstract public function hash_hset(string $key, string $field, $value, bool $add_prefix = true);

    /**
     * 增加 key 指定的哈希集中指定字段的数值。如果 key 不存在，会创建一个新的哈希集并与 key 关联。如果字段不存在，则字段的值在该操作执行前被设置为 0
     *   HINCRBY 支持的值的范围限定在 64位 有符号整数
     * @param string $key
     * @param string $field
     * @param int $increment
     * @param bool $add_prefix
     * @return string
     */
    abstract public function hash_hincrby(string $key, string $field, int $increment = 1, bool $add_prefix = true);

    /**
     * 返回hash里面field是否存在
     * @param string $key
     * @param string $field
     * @param bool $add_prefix
     * @return bool
     */
    abstract public function hash_hexists(string $key, string $field, bool $add_prefix = true): bool;

    /**
     * 从 key 指定的哈希集中移除指定的域。在哈希集中不存在的域将被忽略。
     *  如果 key 指定的哈希集不存在，它将被认为是一个空的哈希集，该命令将返回0。
     * @param string $key
     * @param string $field
     * @param bool $add_prefix
     * @return bool|int
     */
    abstract public function hash_hdel(string $key, string $field, bool $add_prefix = true);

    /**
     * 返回 key 指定的哈希集中所有的字段和值
     * @param string $key
     * @param array $default
     * @param bool $add_prefix
     * @return array
     */
    abstract public function hash_hgetall(string $key, $default = [], bool $add_prefix = true): array;

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
     * 删除缓存标签
     * @param string $key 标签标识
     * @param bool $add_prefix 是否活加前缀
     * @return void
     */
    public function list_clear(string $key, bool $add_prefix = true): void
    {
        // 缓存标签列表
        $keys = $this->list_lrange($key, 0, -1, $add_prefix);
        // 指定标签清除
        $this->multiple_delete($keys, $add_prefix);
        // 消除list
        $key = $this->key_get($key, $add_prefix, true);
        $this->delete($key, false);
    }

    /**
     * 获取实际的缓存标识
     * @param string $key 缓存名
     * @param bool $add_prefix 是否活加前缀
     * @param bool $is_list 是否列表前缀
     * @return string
     */
    abstract public function key_get(string $key, bool $add_prefix = true, bool $is_list = false): string;

    /**
     * 取得  prefix | module:名称
     * @param bool $is_list
     * @return string
     */
    public function prefix(bool $is_list = false)
    {
        if ($is_list) {
            return $this->_options['prefix_list'];
        }
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
                return \json_encode($data, JSON_UNESCAPED_UNICODE);
                // return \serialize($data);
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
                return \json_decode($data, true);
                // return \unserialize($data);
            };
        return $unserialize($data);
    }


    /**
     * 返回缓存读取次数
     * @return int
     */
    public function times_get(): int
    {
        return $this->_times['get'];
    }

    /**
     * 返回缓存写入次数
     * @return int
     */
    public function times_set(): int
    {
        return $this->_times['set'];
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
