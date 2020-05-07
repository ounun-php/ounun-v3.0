<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\cache\driver;


/**
 * Redis缓存驱动，适合单机部署、有前端代理实现高可用的场景，性能最好
 * 有需要在业务层实现读写分离、或者使用RedisCluster的需求，请使用Redisd驱动
 *
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class redis extends \ounun\cache\driver
{
    /** @var string redis类型 */
    const Type = 'redis';

    /** @var \Redis */
    protected $_handler;

    /** @var array 配制 */
    protected $_options = [
        'expire'    => 0,  // 有效时间 0为永久
        'serialize' => ['json_encode_unescaped', 'json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据  true:字符串
        'data_compress' => false, // bool false:不压缩    true:压缩
        'large_scale'   => false, // bool false:少量      true:大量

        'prefix'      => '',    // 模块名称
        'prefix_list' => 't',

        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'persistent' => false,
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        $this->_handler = new \Redis;
        if ($this->_options['persistent']) {
            $this->_handler->pconnect($this->_options['host'], $this->_options['port'], $this->_options['timeout'], 'persistent_id_' . $this->_options['select']);
        } else {
            $this->_handler->connect($this->_options['host'], $this->_options['port'], $this->_options['timeout']);
        }

        if ('' != $this->_options['password']) {
            $this->_handler->auth($this->_options['password']);
        }

        if (0 != $this->_options['select']) {
            $this->_handler->select($this->_options['select']);
        }
    }

    /**
     * 写入缓存
     * @param string $key 缓存变量名
     * @param mixed $value 存储数据
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @param array $options 参数 ['list_key'=>$list_key 汇总集合list标识 ]
     * @return bool
     */
    public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = [])
    {
        $this->_times['set'] = ((int)$this->_times['set']) + 1;
        $key1                = $this->key_get($key, $add_prefix);
        // first
        if (!$this->_options['format_string']) {
            $value = $this->serialize($value);
        }
        // 数据压缩
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            $value = gzcompress($value, 3);
        }
        // 写
        $result = $this->_handler->set($key1, $value);
        if ($result) {
            // 汇总集合
            if ($options['list_key']) {
                $this->list_lpush($options['list_key'], $key, $add_prefix);
            }
            // 有效时间（秒）
            if ($expire) {
                $this->_handler->expire($key1, $expire);
            }
        }
        return $result;
    }

    /**
     * 读取缓存
     * @param string $key 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @param array $options   参数 ['compress'=>$compress 是否返回压缩后的数据 ]
     * @return mixed
     */
    public function get(string $key, $default = 0, bool $add_prefix = true, array $options = [])
    {
        $this->_times['get'] = ((int)$this->_times['get']) + 1;
        $key                 = $this->key_get($key, $add_prefix);
        $val                 = $this->_handler->get($key);
        if (empty($val)) {
            return $default;
        }
        // 是否返回压缩后的数据
        if($options['compress']){
            return $val;
        }
        // 数据压缩
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            $val = gzuncompress($val);
        }
        // 解析
        if ($this->_options['format_string']) {
            return $val;
        } else {
            return $this->unserialize($val);
        }
    }

    /**
     * 返回key是否存在
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    public function exists(string $key, bool $add_prefix = true): bool
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->exists($key);
    }

    /**
     * 设置key的过期时间，超过时间后，将会自动删除该key
     * @param string $key 缓存变量名
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    public function expire(string $key, int $expire = 0, bool $add_prefix = true): bool
    {
        if($expire <=0){
            return true;
        }
        $key = $this->key_get($key,$add_prefix);
        return $this->_handler->expire($key,$expire);
    }

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    public function delete(string $key, bool $add_prefix = true)
    {
        $key = $this->key_get($key,$add_prefix);
        return $this->_handler->del($key);
    }

    /**
     * 增加之后的value值。（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $increment 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    public function incrby(string $key, int $increment = 1, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->incrBy($key, $increment);
    }

    /**
     * 返回一个数字：减少之后的value值。（针对数值缓存）
     * @param string $key 缓存变量名
     * @param int $increment 步长
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    public function decrby(string $key, int $increment = 1, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->decrBy($key, $increment);
    }


    /**
     * 清除所有缓存
     * @return bool
     */
    public function clear()
    {
        return $this->_handler->flushDB();
    }

    /**
     * 返回 key 指定的哈希集中该字段所关联的值
     * @param string $key
     * @param string $field
     * @param int $default
     * @param bool $add_prefix
     * @return int|string
     */
    public function hash_hget(string $key, string $field, $default = 0, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        $val = $this->_handler->hGet($key, $field);
        return $val ?? $default;
    }


    /**
     * 设置 key 指定的哈希集中指定字段的值。
     * 如果 key 指定的哈希集不存在，会创建一个新的哈希集并与 key 关联。
     * 如果字段在哈希集中存在，它将被重写。
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @param bool $add_prefix
     * @return bool|int
     */
    public function hash_hset(string $key, string $field, $value, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->hSet($key, $field, $value);
    }


    /**
     * 增加 key 指定的哈希集中指定字段的数值。如果 key 不存在，会创建一个新的哈希集并与 key 关联。如果字段不存在，则字段的值在该操作执行前被设置为 0
     *   HINCRBY 支持的值的范围限定在 64位 有符号整数
     * @param string $key
     * @param string $field
     * @param int $increment
     * @param bool $add_prefix
     * @return string
     */
    public function hash_hincrby(string $key, string $field, int $increment = 1, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->hIncrBy($key, $field, $increment);
    }

    /**
     * 返回hash里面field是否存在
     * @param string $key
     * @param string $field
     * @param bool $add_prefix
     * @return bool
     */
    public function hash_hexists(string $key, string $field, bool $add_prefix = true): bool
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->hExists($key, $field);
    }

    /**
     * 从 key 指定的哈希集中移除指定的域。在哈希集中不存在的域将被忽略。
     *  如果 key 指定的哈希集不存在，它将被认为是一个空的哈希集，该命令将返回0。
     * @param string $key
     * @param string $field
     * @param bool $add_prefix
     * @return bool|int
     */
    public function hash_hdel(string $key, string $field, bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix);
        return $this->_handler->hDel($key, $field);
    }

    /**
     * 返回 key 指定的哈希集中所有的字段和值
     * @param string $key
     * @param array $default
     * @param bool $add_prefix
     * @return array
     */
    public function hash_hgetall(string $key, $default = [], bool $add_prefix = true): array
    {
        $key = $this->key_get($key, $add_prefix);
        $val = $this->_handler->hGetAll($key);
        return $val ?? $default;
    }


    /**
     * 向存于 key 的列表的尾部插入所有指定的值。如果 key 不存在，那么会创建一个空的列表然后再进行 push 操作。 当 key 保存的不是一个列表，那么会返回一个错误。
     * 返回:push 操作后的 list 长度。
     * @param string $key
     * @param bool $add_prefix
     * @return int push 操作后的 list 长度。
     */
    public function list_lpush(string $key, $value, bool $add_prefix = true): int
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->lPush($key, $value);
    }

    /**
     * 移除并且返回 key 对应的 list 的第一个元素。
     * @param string $key
     * @param bool $add_prefix
     * @return mixed
     */
    public function list_lpop(string $key = '', bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->lPop($key);
    }

    /**
     * 向存于 key 的列表的尾部插入所有指定的值。如果 key 不存在，那么会创建一个空的列表然后再进行 push 操作。 当 key 保存的不是一个列表，那么会返回一个错误。
     *    push操作后的列表长度。
     * @param string $key
     * @param bool $add_prefix
     * @return int push操作后的列表长度
     */
    public function list_rpush(string $key, $value, bool $add_prefix = true): int
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->rPush($key, $value);
    }

    /**
     * 移除并返回存于 key 的 list 的最后一个元素。
     * @param string $key
     * @param bool $add_prefix
     * @return mixed
     */
    public function list_rpop(string $key = '', bool $add_prefix = true)
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->rPop($key);
    }

    /**
     * 获取标签包含的缓存标识
     *   返回存储在 key 的列表里指定范围内的元素。 start 和 end 偏移量都是基于0的下标，即list的第一个元素下标是0（list的表头），第二个元素下标是1，以此类推。
     *   偏移量也可以是负数，表示偏移量是从list尾部开始计数。 例如， -1 表示列表的最后一个元素，-2 是倒数第二个，以此类推。
     *
     * @param string $key 标签标识
     * @param int $start start 和 end 偏移量都是基于0的下标，即list的第一个元素下标是0（list的表头）
     * @param int $end 偏移量也可以是负数，表示偏移量是从list尾部开始计数。 例如， -1 表示列表的最后一个元素，-2 是倒数第二个，以此类推。
     * @param bool $add_prefix 是否活加前缀
     * @return array
     */
    public function list_lrange(string $key, int $start = 0, int $end = -1, bool $add_prefix = true): array
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->lRange($key, $start, $end);
    }

    /**
     * 返回存储在 key 里的list的长度。 如果 key 不存在，那么就被看作是空list，并且返回长度为 0。 当存储在 key 里的值不是一个list的话，会返回error。
     * @param string $key 标签名
     * @param bool $add_prefix 是否活加前缀
     * @return int
     */
    public function list_length(string $key, bool $add_prefix = true): int
    {
        $key = $this->key_get($key, $add_prefix, true);
        return $this->_handler->lLen($key);
    }

    /**
     * 获取实际的缓存标识
     * @param string $key 缓存名
     * @param bool $add_prefix 是否活加前缀
     * @param bool $is_list 是否列表前缀
     * @return string
     */
    public function key_get(string $key, bool $add_prefix = true, bool $is_list = false): string
    {
        if (!$add_prefix) {
            return $key;
        }
        if ($is_list) {
            $prefix_tag = $this->_options['prefix_list'] ?? 't';
            $prefix     = $this->_options['prefix'] ? $this->_options['prefix'] . ':' : '';
            return $prefix . $prefix_tag . ':' . $key;
        } else {
            if ($this->_options['prefix']) {
                return $this->_options['prefix'] . ':' . $key;
            }
            return $key;
        }
    }

}
