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
        'prefix_tag'    => 't',

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
        // 建立连接 / 支持集群
        $this->_handler->addServers($this->_options['servers']);
        // 受权
        if ('' != $this->_options['username']) {
            $this->_handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->_handler->setSaslAuthData($this->_options['username'], $this->_options['password']);
        }
    }

    /**
     * 写入缓存
     * @param  string    $key         缓存变量名
     * @param  mixed     $value       存储数据
     * @param  int       $expire      有效时间（秒）
     * @param  bool      $add_prefix  是否活加前缀
     * @return bool
     */
    public function set(string $key, $value,int $expire = 0, bool $add_prefix = true)
    {
        $this->_times['write']  = ((int)$this->_times['write']) + 1;
        if($add_prefix){
            $key    = $this->cache_key_get($key);
        }
        // first
        $first      = false;
        if ($this->_tagset && !$this->has($key,$add_prefix)) {
            $first  = true;
        }
        if(!$this->_options['format_string']){
            $value = $this->serialize($value);
        }
        // 数据压缩
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            $value = gzcompress($value, 3);
        }
        // 写
        $expire = 0 == $expire ? 0 : time() + $expire;
        if ($this->_handler->set($key, $value, $expire)) {
            if($first){
                $this->_tagset->append($key,false);
            }
            return true;
        }
        return false;
    }

    /**
     * 读取缓存
     * @param  string    $key         缓存变量名
     * @param  mixed     $default     默认值
     * @param  bool      $add_prefix  是否活加前缀
     * @return mixed
     */
    public function get(string $key, $default = 0, bool $add_prefix = true)
    {
        $this->_times['read']  = ((int)$this->_times['read']) + 1;
        if($add_prefix){
            $key    = $this->cache_key_get($key);
        }
        $content     = $this->_handler->get($key);
        if (empty($content)) {
            return $default;
        }
        // 数据压缩
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            $content = gzuncompress($content);
        }
        // 解析
        if($this->_options['format_string']){
            return $content;
        }else{
            return $this->unserialize($content);
        }
    }

    /**
     * 删除缓存
     * @param    string  $key         缓存变量名
     * @param    bool    $add_prefix  是否活加前缀
     * @param    bool    $ttl
     * @return bool
     */
    public function delete(string $key, bool $add_prefix = true, $ttl = false)
    {
        if($add_prefix){
            $key    = $this->cache_key_get($key);
        }
        return false === $ttl
                ? $this->_handler->delete($key)
                : $this->_handler->delete($key, $ttl);
    }

    /**
     * 清除所有缓存
     * @return bool
     */
    public function clear()
    {
        return $this->_handler->flush();
    }
}
