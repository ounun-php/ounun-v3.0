<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\cache;


use ounun\cache;
use ounun\cache\driver\redis;

class lock
{
    /** @var array */
    protected static array $_instances = [];

    /**
     * @param string $storage_key
     * @param array $config
     * @return self
     */
    static public function i(string $storage_key = 'data', array $config = []): lock
    {
        if (empty(static::$_instances[$storage_key])) {
            $cache = cache::i($storage_key,$config);
            static::$_instances[$storage_key] = new static($cache);
        }
        return static::$_instances[$storage_key];
    }


    /** @var \Redis */
    protected $redis;

    /**
     * RedisLock constructor.
     *
     * @param cache $cache
     */
    public function __construct(cache $cache)
    {
        if($cache){
            if($cache->driver_type() == redis::Type){
                $this->redis = $cache->driver()->handler();
            }else{
                error_php('driver_type:'.$cache->driver_type().' error');
            }
        }
        error_php('$cache:null error');
    }

    /**
     * 获取锁 带过期时间的锁
     *
     * @param string $key
     * @param int $expire
     * @return bool
     */
    public function lock(string $key,int $expire = 5)
    {
        $isLock = $this->redis->setnx($key, time() + $expire);
        if ($isLock) {
            $this->redis->expire($key, $expire);
            return true;
        }
        return false;
    }

    /**
     * 释放锁
     *
     * @param string $key 锁标识
     * @return bool
     */
    public function unlock(string $key): bool
    {
        return $this->redis->del($key);
    }
}
