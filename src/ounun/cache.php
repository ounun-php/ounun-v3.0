<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun;

use ounun;
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

    /** @var string storage_key  库名称 */
    public string $storage_key = '';

    /** @var int 驱动类型  0:[错误,没设定驱动] 1:File 2:Memcache 3:Redis */
    protected int $_driver_type = 0;
    /** @var driver 缓存驱动 */
    protected driver $_driver;

    /** @var array 数据 */
    protected array $_value = [];

    /** @var array */
    protected static array $_instances = [];

    /**
     * @param string $storage_key
     * @param array $config
     * @return $this
     */
    static public function i(string $storage_key = 'data', array $config = []): cache
    {
        if (empty(static::$_instances[$storage_key])) {
            if (empty($config)) {
                $config = ounun::$global['cache'][$storage_key];
            }
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
        if ($this->_driver_type) {
            $cls           = "driver\{$this->_driver_type}";
            if (is_subclass_of($cls, driver::class)) {
                $this->_driver = new $cls($config);
            }else{
                trigger_error("Can't support \$cls:{$cls}", E_USER_ERROR);
            }
        } else {
            trigger_error("Can't support driver_type:{$this->_driver_type}", E_USER_ERROR);
        }
    }

    /**
     * 读取缓存
     * @param string $key 缓存变量名
     * @param mixed $default 默认值
     * @param bool $add_prefix 是否活加前缀
     * @param array $options 参数 ['compress'=>$compress 是否返回压缩后的数据 ]
     * @return mixed
     */
    public function get(string $key, $default = 0, bool $add_prefix = true, array $options = [])
    {
        return $this->_driver->get($key, $default, $add_prefix, $options);
    }

    /**
     * 写入缓存
     * @param string $key 缓存变量名
     * @param mixed $value 存储数据
     * @param int $expire 有效时间（秒）
     * @param bool $add_prefix 是否活加前缀
     * @param array $options 参数 ['list_key'=>$list_key 汇总集合list标识 ]
     * @return bool
     */
    public function set(string $key, $value, int $expire = 0, bool $add_prefix = true, array $options = []): bool
    {
        return $this->_driver->set($key, $value, $expire, $add_prefix, $options);
    }

    /**
     * 返回 key 指定的哈希集中该字段所关联的值
     * @param string $key
     * @param string $field
     * @param int $default
     * @param bool $add_prefix
     * @return int|string
     */
    public function hash_hget(string $key, string $field, $default = 0, bool $add_prefix = true)
    {
        if ($this->_value[$key] && isset($this->_value[$key][$field])) {
            return $this->_value[$key][$field];
        }
        $this->_value[$key][$field] = $this->_driver->hash_hget($key, $field, $default, $add_prefix);
        return $this->_value[$key][$field];
    }


    /**
     * 设置 key 指定的哈希集中指定字段的值。
     * 如果 key 指定的哈希集不存在，会创建一个新的哈希集并与 key 关联。
     * 如果字段在哈希集中存在，它将被重写。
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @param bool $add_prefix
     * @return bool|int
     */
    public function hash_hset(string $key, string $field, $value, bool $add_prefix = true)
    {
        $this->_value[$key][$field] = $value;

        return $this->_driver->hash_hset($key, $field, $value, $add_prefix);
    }

    /**
     * 缓存KEY
     * @param string $key
     * @param bool $add_prefix
     * @return string
     */
    public function key_get(string $key, bool $add_prefix = false): string
    {
        return $this->_driver->key_get($key, $add_prefix);
    }

    /**
     * 返回key是否存在
     * @param string $key 缓存变量名
     * @param bool $add_prefix 是否活加前缀
     * @return bool
     */
    public function exists(string $key, bool $add_prefix = false): bool
    {
        return $this->_driver->exists($key, $add_prefix);
    }


    /**
     * 简单方式，删除$key对应值$val
     * @param string $key
     * @param bool $add_prefix
     */
    public function delete(string $key, bool $add_prefix = false)
    {
        $this->_driver->delete($key, $add_prefix);
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function data(string $key, callable $callback)
    {
        if (isset($this->_value[$key])) {
            return $this->_value[$key];
        }
        /** @var int 最后更新时间，大于这个时间数据都过期 */
        $last_time = global_all('cache_data',time(),'cache_last_time');
        $c         = $this->_driver->get($key, [], true);
        if ($c && $c['v'] && $c['t'] > $last_time) {
            $this->_value[$key] = $c['v'];
        }
        $this->_value[$key] = $callback();
        $this->_driver->set($key, ['t' => time(), 'v' => $this->_value[$key]], 0, true);
        return $this->_value[$key];
    }

    /**
     * @param $key
     */
    public function data_clean($key)
    {
        $this->_value[$key] = null;
        unset($this->_value[$key]);

        $this->delete($key, true);
    }

    /**
     * @return driver 返回缓存驱动
     */
    public function driver(): driver
    {
        return $this->_driver;
    }

    /**
     * @return string 返回驱动类型
     */
    public function driver_type()
    {
        return $this->_driver_type;
    }
}
