<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc\driver;


/**
 * Wincache缓存驱动
 */
class wincache extends \ounun\dc\driver
{
    /** @var string wincache类型 */
    const Type          = 'wincache';

    /** @var array 配制 */
    protected $_options = [
        // 'module'     => '', // 模块名称   转 prefix
        // 'filename'   => '', // 文件名
        'expire'        => 0,     // 有效时间 0为永久
        'serialize'     => ['json_encode_unescaped','json_decode_array'], // encode decode

        'format_string' => false, // bool false:混合数据 true:字符串
        'large_scale'   => false, // bool false:少量    true:大量
        'prefix'        => '',    // 模块名称
        'prefix_tag'    => 't_',
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @throws
     */
    public function __construct($options = [])
    {
        if (!function_exists('wincache_ucache_info')) {
            throw new \BadFunctionCallException('not support: WinCache');
        }
        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
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
        return wincache_ucache_exists($key);
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $key = $this->cache_key_get($name);
        return wincache_ucache_exists($key) ? wincache_ucache_get($key) : $default;
    }

    /**
     * 写入缓存
     * @param string            $key 缓存变量名
     * @param mixed             $value  存储数据
     * @param int               $expire  有效时间（秒）
     * @return boolean
     */
    public function set($key, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->_options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        $key = $this->cache_key_get($key);
        if ($this->_tags && !$this->has($key)) {
            $first = true;
        }
        if (wincache_ucache_set($key, $value, $expire)) {
            isset($first) && $this->tag_items_set($key);
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @return boolean
     */
    public function delete($key)
    {
        return wincache_ucache_delete($this->cache_key_get($key));
    }

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            $keys = $this->tag_items_get($tag);
            foreach ($keys as $key) {
                wincache_ucache_delete($key);
            }
            $this->delete('tag_' . md5($tag));
            return true;
        } else {
            return wincache_ucache_clear();
        }
    }

}
