<?php


namespace ounun;


use ounun\dc\driver;
use ounun\dc\driver\file;
use ounun\dc\driver\memcached;
use ounun\dc\driver\redis;
use ounun\dc\driver\wincache;

class dc
{
    /** @var array */
    static protected $_instance = [];

    /**
     * @param string $tag
     * @param array $config
     * @return $this
     */
    static public function i(string $tag = 'tag', array $config = [])
    {
        if (empty(static::$_instance[$tag])) {
            static::$_instance[$tag] = new static($config);
        }
        return static::$_instance[$tag];
    }

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
     public function set_value($val,int $expire = 0)
     {
         $this->_is_read = true;
         $this->_value   = $val;
         $this->_expire  = $expire;
     }

    /**
     * 简单方式，设定$key对应值$val
     * @param string $key
     * @param mixed  $val
     * @return bool
     */
    public function fast_set(string $key, $val)
    {
        return $this->_cache_driver->set($key,$val);
    }

    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param string $key
     * @param string $sub_key
     * @return mixed
     */
    public function fast_get(string $key, string $sub_key = null)
    {
        $value = $this->_cache_driver->get($key);
        if ($sub_key) {
            return $value[$sub_key];
        }
        return $value;
    }

    /**
     * 简单方式，删除$key对应值$val
     * @param string $key
     */
    public function fast_del(string $key)
    {
        $this->_cache_driver->delete($key);
    }

    /**
     * 取得Tag名称
     * @return string
     */
    public function tag()
    {
        return $this->_tag;
    }
}
