<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc\driver;


class memcached extends \ounun\dc\driver
{
    /** @var string memcached类型 */
    const Type          = 'memcached';

    /** @var \Memcached  */
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

        'servers'      => [
            ['127.0.0.1',11211,100],
            // ['127.0.0.1',11211,100]
        ],
        'timeout'  => 0, // 超时时间（单位：毫秒）
        'username' => '', //账号
        'password' => '', //密码
        'option'   => [],
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('memcached')) {
            throw new \BadFunctionCallException('not support: memcached');
        }
        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        $this->_handler = new \Memcached;
        if (!empty($this->_options['option'])) {
            $this->_handler->setOptions($this->_options['option']);
        }
        // 设置连接超时时间（单位：毫秒）
        if ($this->_options['timeout'] > 0) {
            $this->_handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->_options['timeout']);
        }
        // 支持集群
        $hosts = explode(',', $this->_options['host']);
        $ports = explode(',', $this->_options['port']);
        if (empty($ports[0])) {
            $ports[0] = 11211;
        }
        // 建立连接
        $servers = [];
        foreach ((array) $hosts as $i => $host) {
            $servers[] = [$host, (isset($ports[$i]) ? $ports[$i] : $ports[0]), 1];
        }
        $this->_handler->addServers($servers);
        if ('' != $this->_options['username']) {
            $this->_handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->_handler->setSaslAuthData($this->_options['username'], $this->_options['password']);
        }
    }

    /**
     * 判断缓存
     * @param string $key 缓存变量名
     * @return bool
     */
    public function has($key)
    {
        $key = $this->cache_key_get($key);
        return $this->_handler->get($key) ? true : false;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $this->_times['read']  = (int)$this->_times['read'] + 1;
        $result                = $this->_handler->get($this->cache_key_get($name));
        return false !== $result ? $result : $default;
    }

    /**
     * 写入缓存
     * @param string            $key 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = null)
    {
        $this->_times['write']  = (int)$this->_times['write'] + 1;
        if ($this->_tagset && !$this->has($key)) {
            $first = true;
        }
        $key    = $this->cache_key_get($key);
        $expire = 0 == $expire ? 0 : $_SERVER['REQUEST_TIME'] + $expire;
        if ($this->_handler->set($key, $value, $expire)) {
            if($first){
                $this->_tagset->append($key);
            }
            return true;
        }
        return false;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function increase($name, $step = 1)
    {
        $key = $this->cache_key_get($name);
        if ($this->_handler->get($key)) {
            return $this->_handler->increment($key, $step);
        }
        return $this->_handler->set($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function decrease($name, $step = 1)
    {
        $key   = $this->cache_key_get($name);
        $value = $this->_handler->get($key) - $step;
        $res   = $this->_handler->set($key, $value);
        if (!$res) {
            return false;
        } else {
            return $value;
        }
    }

    /**
     * 删除缓存
     * @param    string  $key 缓存变量名
     * @param bool|false $ttl
     * @return bool
     */
    public function delete($key, $ttl = false)
    {
        $key = $this->cache_key_get($key);
        return false === $ttl
                ? $this->_handler->delete($key)
                : $this->_handler->delete($key, $ttl);
    }

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return bool
     */
    public function clear(string $tag = '')
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->tag_item_get($tag);
            $this->_handler->deleteMulti($keys);
            $this->delete($this->tag_key_get($tag));
            return true;
        }
        return $this->_handler->flush();
    }
}
