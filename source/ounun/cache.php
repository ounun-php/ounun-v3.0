<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

use ounun\cache\driver;
use ounun\db\pdo;

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
    /** 有效Cache类型 */
    const Driver_Type_Valid = [
        driver\code::Type,
        driver\file::Type,
        driver\html::Type,
        driver\memcached::Type,
        driver\mysql::Type,
        driver\sqlite::Type,
        driver\redis::Type
    ];

    /** @var string storage_key  库名称 */
    public $storage_key = '';

    /** @var int 驱动类型  0:[错误,没设定驱动] 1:File 2:Memcache 3:Redis */
    protected $_driver_type = 0;
    /** @var driver 缓存驱动 */
    protected $_driver;

    /** @var array 数据 */
    protected $_value = [];

    /** @var array */
    static protected $_instances = [];

    /**
     * @param string $storage_key
     * @param array $config
     * @return $this
     */
    static public function i(string $storage_key = 'data', array $config = [])
    {
        if (empty(static::$_instances[$storage_key])) {
            $cache                            = new static($config);
            $cache->storage_key               = $storage_key;
            static::$_instances[$storage_key] = $cache;
        }
        return static::$_instances[$storage_key];
    }

    /**
     * 构造函数
     * config constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->_driver_type = $config['driver_type'];
        if($this->_driver_type && in_array($this->_driver_type,static::Driver_Type_Valid)){
            $cls = "driver\{$this->_driver_type}";
            $this->_driver = new $cls($config);
        }else{
            trigger_error("Can't support driver_type:{$this->_driver_type}", E_USER_ERROR);
        }
    }

    /**
     * 读取数据中$key的值
     * @param string $key 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @return mixed
     */
    public function get(string $key, $default = 0, bool $add_prefix = true)
    {
        return $this->_driver->get($key, $default, $add_prefix);
    }

    /**
     * 读取数据中$key的值
     * @param string $sub_key
     * @return mixed
     */
    public function get_sub(string $key, string $sub_key, $default = 0, bool $add_prefix = true)
    {
        if ($this->_value && is_array($this->_value)) {
            return $this->_value[$sub_key];
        }
        return null;
    }

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    public function get_read()
    {
        return $this->_driver->read();
    }

    /**
     * 缓存KEY
     * @param string $key
     * @param bool $add_prefix
     * @return string
     */
    public function key_get(string $key, bool $add_prefix = false)
    {
        return $this->_driver->key_get($key,$add_prefix);
    }



    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param string $key
     * @param string $key_sub
     * @param int $default
     * @param bool $add_prefix
     * @return mixed
     */
    public function get_fast(string $key, $default = 0, string $key_sub = '', bool $add_prefix = false)
    {
        $value = $this->_driver->get($key, $default, $add_prefix);
        if ($key_sub) {
            return $value[$key_sub];
        }
        return $value;
    }

    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param string $key
     * @param string $key_sub
     * @return bool|int|mixed|null
     */
    public function get_fast2($key, $key_sub = '')
    {
        $this->_driver->key_set($key);
        if ($key_sub) {
            return $this->_driver->get($key_sub);
        }
        return $this->_driver->read();
    }


    /**
     * 写入已设定的数据$sub_key为$sub_val
     * @return bool
     */
    public function set()
    {
        if (false == $this->_is_read) {
            trigger_error("ERROR! value is null.", E_USER_ERROR);
        }
        return $this->_driver->set($this->_key, $this->_value, $this->_expire);
    }

    /**
     * 写入已设定的数据
     * @return bool
     */
    public function set_write()
    {
        return $this->_driver->write();
    }

    /**
     * 设定数据keys
     * @param string $key
     * @param bool $add_prefix
     */
    public function set_key(string $key, bool $add_prefix = false)
    {
        $this->_is_read    = false;
        $this->_value      = null;
        $this->_add_prefix = $add_prefix;
        $this->_key        = "{$this->_add_prefix}:{$key}";
    }

    /**
     * 设定数据Value
     * @param mixed $val
     * @param int $expire
     * @param bool $add_prefix
     */
    public function set_value($val, int $expire = 0, bool $add_prefix = false)
    {
        $this->_is_read    = true;
        $this->_value      = $val;
        $this->_expire     = $expire;
        $this->_add_prefix = $add_prefix;
    }


    /**
     * 设定数据中$sub_key为$sub_val
     * @param string $sub_key
     * @param mixed $sub_val
     */
    public function set_sub(string $sub_key, $sub_val)
    {
        if (!$this->_is_read) {
            $this->get();
        }
        if (empty($this->_value)) {
            $this->_value = [];
        }
        $this->_value[$sub_key] = $sub_val;
    }

    /**
     * 简单方式，设定$key对应值$val
     * @param string $key
     * @param mixed $val
     * @param int $expire
     * @param bool $add_prefix
     * @return bool
     */
    public function set_fast(string $key, $val, int $expire = 0, bool $add_prefix = false)
    {
        return $this->_driver->set($key, $val, $expire, $add_prefix);
    }

    /**
     * 判断缓存是否存在
     * @param string $key 缓存变量名
     * @return mixed
     */
    public function has(string $key)
    {
        return $this->_driver->get($key) ? true : false;
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        return $this->_driver->delete($this->_key);
    }


    /**
     * 简单方式，删除$key对应值$val
     * @param string $key
     * @param bool $add_prefix
     */
    public function delete_fast(string $key, bool $add_prefix = false)
    {
        $this->_driver->delete($key, $add_prefix);
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function data($key, callable $callback)
    {
        /** @var int 最后更新时间，大于这个时间数据都过期 */
        $last_time = (int)\ounun::$global['cache']['last_time'];
        if (!$this->_value[$key]) {
            $this->set_key($key);
            $c = $this->get();
            if ($c == null) {
                $this->_value[$key] = $callback();
                $this->set_key($key);
                $this->set_value(['t' => time(), 'v' => $this->_value[$key]]);
                $this->set();
            } elseif (!is_array($c) || (int)$c['t'] < $last_time) {
                $this->_value[$key] = $callback();
                $this->set_key($key);
                $this->set_value(['t' => time(), 'v' => $this->_value[$key]]);
                $this->set();
            } else {
                $this->_value[$key] = $c['v'];
            }
        }
        return $this->_value[$key];
    }

    /**
     * @param $key
     */
    public function data_clean($key)
    {
        $this->_value[$key] = null;
        unset($this->_value[$key]);

        $this->delete_fast($key);
    }
}
