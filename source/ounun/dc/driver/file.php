<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc\driver;

use ounun\dc\code;
use ounun\utils\time;

/**
 * 文件类型缓存类
 */
class file extends \ounun\dc\driver
{
    /** @var string file类型 */
    const Type          = 'file';

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

        // 'cache_subdir'  => true,   用 large_scale
        'path'          => Dir_Cache,
        'data_code'     => false,
        'data_compress' => false,
    ];

    /**
     * 构造函数
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        // 创建项目缓存目录
        if (!is_dir($this->_options['path'])) {
            mkdir($this->_options['path'], 0755, true);
        }
    }

    /**
     * 获取实际的缓存标识
     * @param  string $key 缓存名
     * @return string
     */
    public function cache_key_get(string $key): string
    {
        $key = md5($key);
        if ($this->_options['large_scale']) {
            // false:少量(不使用子目录)    true:大量(使用子目录)
            $key  = substr($key, 0, 1) . '/'.substr($key, 1, 1).'/' . substr($key, 2);
        }
        if ($this->_options['prefix']) {
            $key  = $this->_options['prefix'] . '/' . $key;
        }
        return $this->_options['path'] . $key . '.c';
    }

    /**
     * 获取实际标签名
     * @param  string $tagset_tag 标签名
     * @return string
     */
    public function tagset_key_get(string $tagset_tag): string
    {
        if($this->_options['prefix_tag']){
            if($this->_options['prefix']){
                return $this->_options['path'].$this->_options['prefix'] .'/'.$this->_options['prefix_tag'].'/'. $tagset_tag.'.c';
            }
            return $this->_options['path'].'c/'.$this->_options['prefix_tag']. '/' . $tagset_tag.'.c';
        }
        if($this->_options['prefix']){
            return $this->_options['path'].$this->_options['prefix'] .'/t/'. $tagset_tag .'.c';
        }
        return $this->_options['path'].'c/t/'.$tagset_tag. '.c';
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
            $filename = $this->cache_key_get($key);
        }else{
            $filename = $key;
        }
        $first     = false;
        if ($this->_tagset && $this->has($filename,false)) {
            $first = true;
        }
        if($this->_options['data_code']){
            $result =  code::write($filename,$value,true);
        }else{
            if(!$this->_options['format_string']){
                $value = $this->serialize($value);
            }
            // 数据压缩
            if ($this->_options['data_compress'] && function_exists('gzcompress')) {
                $value = gzcompress($value, 3);
            }
            // 创建目录
            $dir      = dirname($filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            // 写文件
            $result = file_put_contents($filename, $value);
        }
        if ($result) {
            if($first){
                $this->_tagset->append($key,false);
            }
            return true;
        } else {
            return false;
        }
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
            $filename = $this->cache_key_get($key);
        }else{
            $filename = $key;
        }
        if (!$this->has($filename,false)) {
            return $default;
        }
        // 看是否过期
        if($this->_options['expire'] > 0 ){
            $mtime     = filemtime($filename);
            if(time() >  $this->_options['expire'] + $mtime){
                return $default;
            }
        }
        // 读
        if($this->_options['data_compress']){
            return code::read($filename);
        }
        $content    = file_get_contents($filename);
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
            $key = $this->cache_key_get($key);
        }
        if (is_file($key)) {
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @param  string $key         缓存变量名
     * @param  bool   $add_prefix  是否活加前缀
     * @return boolean
     */
    public function delete(string $key, bool $add_prefix = true)
    {
        if($add_prefix){
            $key = $this->cache_key_get($key);
        }
        return $this->unlink($key);
    }

    /**
     * 清除所有缓存
     * @return boolean
     */
    public function clear()
    {
        $files = glob($this->_options['path'] . ($this->_options['prefix'] ? $this->_options['prefix'] . '/' : '') . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . '/*.c');
                if (is_array($matches)) {
                    array_map('unlink', $matches);
                }
                // rmdir($path);
                $this->rmdir($path);
            } else {
                unlink($path);
            }
        }
        return true;
    }


    /**
     * 清空文件夹函数和清空文件夹后删除空文件夹函数的处理
     * @param string $path
     */
    protected function rmdir(string $path)
    {
        // 如果是目录则继续
        if(is_dir($path)){
            // 扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                // 排除目录中的.和..
                if($val !="." && $val !=".."){
                    // 如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        // 子目录中操作删除文件夹和文件
                        $this->rmdir($path.$val.'/');
                        // 目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        // 如果是文件直接删除
                        unlink($path.'/'.$val);
                    }
                }
            }
        }
    }

    /**
     * 判断文件是否存在后，删除
     * @param string $filename
     * @return bool
     */
    protected function unlink(string $filename)
    {
        return is_file($filename) && unlink($filename);
    }

}
