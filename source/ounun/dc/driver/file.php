<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc\driver;

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
        'prefix_tag'    => 't_',

        // 'cache_subdir'  => true,   用 large_scale
        'path'          => Dir_Cache,
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
     * 取得变量的存储文件名
     * @param  string $key 缓存变量名
     * @return string
     */
    public function cache_key_get(string $key): string
    {
        $key = md5($key);
        if ($this->_options['large_scale']) {
            // false:少量(不使用子目录)    true:大量(使用子目录)
            $key = substr($key, 0, 2) . '/'.substr($key, 2, 2).'/' . substr($key, 4);
        }
        if ($this->_options['prefix']) {
            $key = $this->_options['prefix'] . '/' . $key;
        }
        $filename = $this->_options['path'] . $key . '.php';
        $dir      = dirname($filename);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }

    /**
     * 判断缓存是否存在
     * @param string $key 缓存变量名
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) ? true : false;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $filename = $this->cache_key_get($name);
        if (!is_file($filename)) {
            return $default;
        }
        $content                  = file_get_contents($filename);
        $this->_options['expire'] = 0;
        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                return $default;
            }
            $this->_options['expire'] =  $expire;
            $content                  = substr($content, 32);
            if ($this->_options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return $default;
        }
    }

    /**
     * 写入缓存
     * @param string    $key     缓存变量名
     * @param mixed     $value   存储数据
     * @param int       $expire  有效时间（秒）
     * @return boolean
     */
    public function set($key, $value, $expire = 0)
    {
        if (empty($expire)) {
            $expire = $this->_options['expire'];
        }
        $filename = $this->cache_key_get($key);
        if ($this->_tags && !is_file($filename)) {
            $first = true;
        }
        $data = serialize($value);
        if ($this->_options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        $data   = "<?php\n//" . sprintf('%012d', $expire) . "\n" . $data;
        $result = file_put_contents($filename, $data);
        if ($result) {
            isset($first) && $this->tag_items_set($filename);
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }


    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @return boolean
     */
    public function delete(string $key)
    {
        $filename = $this->cache_key_get($key);
        return $this->unlink($filename);
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
                $this->unlink($key);
            }
            $this->delete('tag_' . md5($tag));
            return true;
        }
        $files = (array) glob($this->_options['path'] . ($this->_options['prefix'] ? $this->_options['prefix'] . '/' : '') . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . '/*.php');
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
     * @param $path
     */
    protected function rmdir($path)
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
     * @param $path
     * @return bool
     * @return boolean
     */
    protected function unlink($path)
    {
        return is_file($path) && unlink($path);
    }

}
