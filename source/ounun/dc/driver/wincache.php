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
        'prefix_tag'    => 't',
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
            $key   = $this->cache_key_get($key);
        }
        // first
        $first     = false;
        if ($this->_tagset && !$this->has($key)) {
            $first = true;
        }
        if(!$this->_options['format_string']){
            $value = $this->serialize($value);
        }
        // 数据压缩
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            $value = gzcompress($value, 3);
        }
        // 写
        if (wincache_ucache_set($key, $value, $expire)) {
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
        if($this->has($key,false)){
            return $default;
        }
        // 读
        $content    =  wincache_ucache_get($key);
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
     * 判断缓存是否存在
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return bool
     */
    public function has(string $key, bool $add_prefix = true)
    {
        if($add_prefix){
            $key   = $this->cache_key_get($key);
        }
        return wincache_ucache_exists($key);
    }

    /**
     * 删除缓存
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return bool
     */
    public function delete(string $key, bool $add_prefix = true)
    {
        if($add_prefix){
            $key    = $this->cache_key_get($key);
        }
        return wincache_ucache_delete($key);
    }

    /**
     * 清除所有缓存
     * @return bool
     */
    public function clear()
    {
        return wincache_ucache_clear();
    }
}
