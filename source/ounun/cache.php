<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;

use ounun\cache\driver;

class cache
{
    /** @var int key长度 */
    const Key_Length = 64;

    /** @var int 有效期 1分钟（秒） */
    const Expire_Short = 60;
    /** @var int 有效期 5分钟（秒） */
    const Expire_Middle = 300;
    /** @var int 有效期 长,1小时（秒） */
    const Expire_Long = 3600;



    /** @var int 驱动类型  0:[错误,没设定驱动] 1:File 2:Memcache 3:Redis */
    protected $_type = 0;

    /** @var array */
    static protected $_instance = [];

    /**
     * @param string $cache_data_key
     * @param array $config
     * @return $this
     */
    static public function i(string $cache_data_key = 'data', array $config = [])
    {
        if (empty(static::$_instance[$cache_data_key])) {
            static::$_instance[$cache_data_key] = new static($config);
        }
        return static::$_instance[$cache_data_key];
    }

    /**  @var \ounun\cache\driver */
    private $_drive = null;

    /** @var driver 缓存驱动 */
    protected $_cache_driver;
    /** @var string 模块名称 */
    protected $_tag;

    /** @var array 数据 */
    protected $_value  = [];
    /** @var string key */
    protected $_key    = '';
    /** @var int 缓存有效时长 */
    protected $_expire = 0;
    /** @var bool  是否活加前缀 */
    protected $_add_prefix = false;

    /** @var bool false:没读    true:已读 */
    protected $_is_read   = false;

    /**
     * config constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->_is_read = false;
        // Cache
        if (redis::Type == $config['driver_type']){
            $this->_cache_driver = new redis($config);
        }elseif (memcached::Type == $config['driver_type']){
            $this->_cache_driver = new memcached($config);
        }elseif (wincache::Type == $config['driver_type']){
            $this->_cache_driver = new wincache($config);
        }else{
            $this->_cache_driver = new file($config);
        }
    }

    /**
     * 构造函数
     * cache constructor.
     * @param int $type
     */
    public function __construct2(int $type = 0)
    {
        $this->_type = 0;
    }

    /**
     * 读取数据
     * @return mixed
     */
    public function get()
    {
        if ($this->_is_read) {
            return $this->_value;
        }
        if (empty($this->_key)) {
            trigger_error("ERROR! key is null.", E_USER_ERROR);
        }
        // read
        $this->_is_read = true;
        $this->_value   = $this->_cache_driver->get($this->_key);
        return $this->_value;
    }

    /**
     * 写入已设定的数据
     * @return bool
     */
    public function set()
    {
        if (false == $this->_is_read) {
            trigger_error("ERROR! value is null.", E_USER_ERROR);
        }
        return $this->_cache_driver->set($this->_key, $this->_value, $this->_expire);
    }

    /**
     * 删除数据
     * @return bool
     */
    public function del()
    {
        return $this->_cache_driver->delete($this->_key);
    }

    /**
     * 读取数据中$key的值
     * @param $sub_key
     */
    public function get2($sub_key)
    {
        return $this->_drive->get($sub_key);
    }

    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals
     */
    public function set2($sub_key, $sub_val)
    {
        $this->_drive->set($sub_key, $sub_val);
    }

    /**
     * 读取数据中$key的值
     * @param string $sub_key
     * @return mixed
     */
    public function sub_get($sub_key)
    {
        if (!$this->_is_read) {
            $this->get();
        }
        if ($this->_value && is_array($this->_value)) {
            return $this->_value[$sub_key];
        }
        return null;
    }

    /**
     * 设定数据中$sub_key为$sub_val
     * @param string $sub_key
     * @param mixed  $sub_val
     */
    public function sub_set(string $sub_key, $sub_val)
    {
        if (!$this->_is_read) {
            $this->get();
        }
        if(empty($this->_value)){
            $this->_value = [];
        }
        $this->_value[$sub_key] = $sub_val;
    }

    /**
     * 设定数据keys
     * @param string $key
     */
    public function set_key(string $key)
    {
        $this->_is_read = false;
        $this->_value   = null;
        $this->_key     = "{$this->_tag}:{$key}";
    }

    /**
     * 设定数据Value
     * @param mixed $val
     * @param int   $expire
     */
     public function set_value($val,int $expire = 0,bool $add_prefix = false)
     {
         $this->_is_read = true;
         $this->_value   = $val;
         $this->_expire  = $expire;
         $this->_add_prefix = $add_prefix;
     }

    /**
     * 简单方式，设定$key对应值$val
     * @param string $key
     * @param mixed  $val
     * @return bool
     */
    public function fast_set(string $key, $val,int $expire = 0, bool $add_prefix = false)
    {
        return $this->_cache_driver->set($key,$val,$expire,$add_prefix);
    }

    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param string $key
     * @param string $sub_key
     * @return mixed
     */
    public function fast_get(string $key, string $sub_key = null, $default = 0, bool $add_prefix = false)
    {
        $value = $this->_cache_driver->get($key,$default,$add_prefix);
        if ($sub_key) {
            return $value[$sub_key];
        }
        return $value;
    }

    /**
     * 简单方式，删除$key对应值$val
     * @param string $key
     */
    public function fast_del(string $key,bool $add_prefix = false)
    {
        $this->_cache_driver->delete($key,$add_prefix);
    }


    /**
     * 简单方式，设定$key对应值$val
     * @param $key
     * @param $val
     */
    public function fast_set2($key, $val)
    {
        $this->_drive->key($key);
        $this->_drive->val($val);
        $this->_drive->write();
    }

    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param $key
     * @param $val
     */
    public function fast_get2($key, $sub_key = null)
    {
        $this->_drive->key($key);
        if ($sub_key) {
            return $this->_drive->get($sub_key);
        }
        return $this->_drive->read();
    }

    /**
     * 简单方式，删除$key对应值$val
     * @param $key
     * @param $val
     */
    public function fast_del2($key)
    {
        $this->_drive->key($key);
        $this->_drive->delete();
    }


    /**
     * 取得Tag名称
     * @return string
     */
    public function tag()
    {
        return $this->_tag;
    }

    /** @var \ounun\db\pdo */
    protected $_db;

    /** @var int 最后更新时间，大于这个时间数据都过期 */
    protected $_last_time;

    /**
     * @param int $last_time
     */
    public function last_modify_set(int $last_time)
    {
        $this->_last_time = $last_time;
    }

    /**
     * @param $tag_key
     */
    protected function _clean($tag_key)
    {
        $this->_value[$tag_key] = null;
        unset($this->_value[$tag_key]);

        $this->fast_del($tag_key);
    }

    /**
     * @param $tag_key
     * @param $mysql_method
     * @param null $args
     * @return mixed
     */
    protected function _data($tag_key, $mysql_method, $args = null)
    {

        if (!$this->_value[$tag_key]) {
            $this->set_key($tag_key);
            $c = $this->get();
            // $this->_cd[$tag_key]->mtime = time();
            // debug_header('$last_modify',$last_modify,true);
            // debug_header('$this_mtime',$this->_cd[$tag_key]->mtime,true);
            if ($c == null) {
                //debug_header('$this_mtime2',222,true);
                $this->_value[$tag_key] = $this->$mysql_method($args);
                $this->_dc->set_key($tag_key);
                $this->_dc->set_value(['t' => time(), 'v' => $this->_value[$tag_key]]);
                $this->_dc->set();
            } elseif (!is_array($c) || (int)$c['t'] < $this->_last_time) {
                // debug_header('$this_mtime3',3333,true);
                $this->_value[$tag_key] = $this->$mysql_method($args);
                $this->_dc->set_key($tag_key);
                $this->_dc->set_value(['t' => time(), 'v' => $this->_value[$tag_key]]);
                $this->_dc->set();
            } else {
                $this->_value[$tag_key] = $c['v'];
            }
        }
        return $this->_value[$tag_key];
    }









    /**
     * 设定 Cache配制
     * @param array $config Cache配制
     * $GLOBALS['scfg']['cache1'] = array
     * (
     * 'type'            => \ounun\Cache::Type_File,
     * 'mod'            => 'html',
     * 'root'            => Dir_Cache,
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache2'] = array
     * (
     * 'type'          => \ounun\Cache::Type_Memcache,
     * 'mod'            => 'html',
     * 'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
     * 'zip_threshold' => 5000,
     * 'zip_min_saving'=> 0.3,
     * 'expire'        => (3600*24*30 - 3600),
     * 'flag'          => MEMCACHE_COMPRESSED,
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache2'] = array
     * (
     * 'type'          => \ounun\Cache::Type_Memcached,
     * 'mod'            => 'html',
     * 'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
     * 'auth'          => array('username'=>'username','password'=>'password'),
     * 'expire'        => (3600*24*30 - 3600),
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache3'] = array
     * (
     * 'type'            => \ounun\Cache::Type_Redis,
     * 'mod'            => 'html',
     * 'sfg'            => array(array('host'=>'192.168.1.181','port'=>6379)),
     * 'expire'        => (3600*24*30 - 3600),
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     */
    public function config($config, $mod = null)
    {
        $mod       = $mod ? $mod : $config['mod'];
        $type_list = [self::Type_File, self::Type_Memcache, self::Type_Memcached, self::Type_Redis];
        $type      = in_array($config['type'], $type_list) ? $config['type'] : self::Type_File;
        if (self::Type_Redis == $type) {
            $sfg           = $config['sfg'];
            $expire        = $config['expire'];
            $auth          = $config['auth'];
            $format_string = $config['format_string'];
            $large_scale   = $config['large_scale'];
            $this->config_redis($sfg, $mod, $expire, $large_scale, $format_string, $auth);
        } elseif (self::Type_Memcache == $type) {
            $sfg            = $config['sfg'];
            $zip_threshold  = $config['zip_threshold'];
            $zip_min_saving = $config['zip_min_saving'];
            $expire         = $config['expire'];
            $flag           = $config['flag'];
            $format_string  = $config['format_string'];
            $large_scale    = $config['large_scale'];
            $this->config_memcache($sfg, $mod, $expire, $format_string, $large_scale, $zip_threshold, $zip_min_saving, $flag);
        } elseif (self::Type_Memcached == $type) {
            $sfg           = $config['sfg'];
            $expire        = $config['expire'];
            $auth          = $config['auth'];
            $format_string = $config['format_string'];
            $large_scale   = $config['large_scale'];
            $this->config_memcached($sfg, $mod, $expire, $format_string, $large_scale, $auth);
        } else //if(self::Type_File == $type)
        {
            $root          = $config['root'];
            $format_string = $config['format_string'];
            $large_scale   = $config['large_scale'];
            $this->config_file($mod, $root, $format_string, $large_scale);
        }
    }

    /**
     * 设定 file Cache配制
     * @param string $mod
     * @param $root
     * @param bool|false $large_scale
     */
    public function config_file($mod = 'def', $root = '', $format_string = false, $large_scale = false)
    {
        if (0 == $this->_type) {
            $this->_type  = self::Type_File;
            $this->_drive = new cache\driver\file($mod, $root, $format_string, $large_scale);
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_file().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function config_memcache(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $zip_threshold = 5000, $zip_min_saving = 0.3, $flag = MEMCACHE_COMPRESSED)
    {
        if (0 == $this->_type) {
            $this->_type  = self::Type_Memcache;
            $this->_drive = new cache\driver\memcache($mod, $expire, $format_string, $large_scale, $zip_threshold, $zip_min_saving, $flag);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->add_server($v['host'], $v['port'], $v['weight']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_memcache().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port'],['host','port'],...)
     * @return bool
     */
    public function config_redis(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $auth = false)
    {
        if (0 == $this->_type) {
            $this->_type  = self::Type_Redis;
            $this->_drive = new cache\driver\redis($mod, $expire, $large_scale, $format_string, $auth);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->connect($v['host'], $v['port']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_redis().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcached服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function config_memcached(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $auth = false)
    {
        if (0 == $this->_type) {
            $this->_type  = self::Type_Memcached;
            $this->_drive = new cache\driver\memcached($mod, $expire, $format_string, $large_scale, $auth);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->add_server($v['host'], $v['port'], $v['weight']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_memcached().", E_USER_ERROR);
        }
    }

    /**
     * 设定数据keys
     * @param $keys
     */
    public function key($keys)
    {
        $this->_drive->key($keys);
    }

    /**
     * 设定数据Value
     * @param $vals
     */
    public function val($vals)
    {
        $this->_drive->val($vals);
    }

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    public function read()
    {
        return $this->_drive->read();
    }

    /**
     * 写入已设定的数据
     * @return bool
     */
    public function write()
    {
        return $this->_drive->write();
    }



    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        return $this->_drive->delete();
    }


    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    public function filename()
    {
        return $this->_drive->filename();
    }
}
