<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc\driver;


/**
 * Redis缓存驱动，适合单机部署、有前端代理实现高可用的场景，性能最好
 * 有需要在业务层实现读写分离、或者使用RedisCluster的需求，请使用Redisd驱动
 *
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class redis extends \ounun\dc\driver
{
    /** @var string redis类型 */
    const Type          = 'redis';

    /** @var \Redis  */
    protected $_handler;

    /** @var array 配制 */
    protected $_options = [
        // 'module'     => '', // 模块名称   转 prefix
        // 'filename'   => '', // 文件名
        'expire'        => 0,  // 有效时间 0为永久
        'serialize'     => ['json_encode_unescaped','json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_tag'    => 't_',

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
     * 判断缓存
     * @param string $key 缓存变量名
     * @return bool
     */
    public function has($key)
    {
        return $this->_handler->get($this->cache_key_get($key)) ? true : false;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = $this->_handler->get($this->cache_key_get($name));
        if (is_null($value) || false === $value) {
            return $default;
        }

        try {
            $result = 0 === strpos($value, 'think_serialize:') ? unserialize(substr($value, 16)) : $value;
        } catch (\Exception $e) {
            $result = $default;
        }

        return $result;
    }

    /**
     * 写入缓存
     * @param string            $key 缓存变量名
     * @param mixed             $value  存储数据
     * @param int       $expire  有效时间（秒）
     * @return boolean
     */
    public function set(string $key, $value, int $expire = 0)
    {
        if ($this->_tags && !$this->has($key)) {
            $first = true;
        }
        $key   = $this->cache_key_get($key);
        $value = is_scalar($value) ? $value : 'think_serialize:' . serialize($value);
        if ($expire) {
            $result = $this->_handler->setex($key, $expire, $value);
        } else {
            $result = $this->_handler->set($key, $value);
        }
        isset($first) && $this->tag_item_set($key);
        return $result;
    }

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @return boolean
     */
    public function delete($key)
    {
        return $this->_handler->del($this->cache_key_get($key));
    }

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear(string $tag = '')
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->tag_items_get($tag);
            foreach ($keys as $key) {
                $this->_handler->del($key);
            }
            $this->delete('tag_' . md5($tag));
            return true;
        }
        return $this->_handler->flushDB();
    }

}
