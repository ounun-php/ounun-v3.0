<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\cache\driver;


use ounun\cache\driver;
use ounun\db\pdo;

/**
 * 文件类型缓存类
 */
class mysql extends driver
{
    /** @var string file类型 */
    const Type          = 'mysql';

    /** @var pdo */
    protected $_handler;

    /** @var array  */
    protected $options = [
        // 'module'     => '', // 模块名称   转 prefix
        // 'filename'   => '', // 文件名
        'expire'    => 0,  // 有效时间 0为永久
        'serialize' => ['json_encode_unescaped', 'json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_list'   => 'd',


        'servers'  => [
            ['127.0.0.1', 3306, 100],
            // ['127.0.0.1',11211,100]
        ],
        'timeout'  => 0, // 超时时间（单位：毫秒）
        'username' => '', //账号
        'password' => '', //密码
        'option'   => [],
    ];

    /**
     * 构造函数
     * @access public
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (substr($this->options['path'], -1) != '/') {
            $this->options['path'] .= '/';
        }
    }


    public function get(string $key, $default = 0, bool $add_prefix = true,array $options = [])
    {
        // TODO: Implement get() method.
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

    public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = [])
    {
        // TODO: Implement set() method.
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }
}
