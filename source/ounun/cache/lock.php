<?php


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
     * @return $this
     */
    static public function i(string $storage_key = 'data', array $config = [])
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
    public function lock(string $key, $expire = 5)
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
     * @param  String $key 锁标识
     * @return Boolean
     */
    public function unlock($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 检测
     *
     * @param string $key
     * @param string $pre
     * @return bool
     */
//    public function check(string $key,string $pre = '')
//    {
//        return $this->redis->exists($this->make_key($key,$pre));
//    }

    /**
     * down
     *
     * @param string $key
     * @param int $expire
     * @param string $pre
     * @return bool|void
     */
//    public function down(string $key,int $expire,string $pre = '')
//    {
//        $key1 = $this->make_key($key,$pre);
//        return $this->redis->set($key1,1,$expire);
//    }

    /**
     * make key
     *
     * @param string $key
     * @param string $pre
     * @return string
     */
//    private function make_key(string $key,string $pre = '')
//    {
//        return $key = $pre . md5($key . '-' . date('Y-m-d'));
//    }
}
