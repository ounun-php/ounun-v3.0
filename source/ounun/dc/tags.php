<?php


namespace ounun\dc;


class tags
{
    /** @var array  标签的缓存Key */
    protected $_tags  = [];

    /** @var driver 缓存句柄 */
    protected $_driver;

    /**
     * 架构函数
     * @param  array  $tag    缓存标签
     * @param  driver $driver 缓存对象
     */
    public function __construct(array $tag, driver $driver)
    {
        $this->_tags     = $tag;
        $this->_driver  = $driver;
    }

    /**
     * 写入缓存
     * @param  string  $name    缓存变量名
     * @param  mixed   $value   存储数据
     * @param  int     $expire  有效时间（秒）
     * @return bool
     */
    public function set(string $name, $value,int $expire = 0): bool
    {
        $this->_driver->set($name, $value, $expire);
        $this->append($name);
        return true;
    }

    /**
     * 追加缓存标识到标签
     * @param  string $name 缓存变量名
     * @return void
     */
    public function append(string $name): void
    {
        $name = $this->_driver->cache_key_get($name);
        foreach ($this->_tags as $tag) {
            $this->_driver->push($tag, $name);
        }
    }

    /**
     * 写入缓存
     * @param  iterable   $values  缓存数据
     * @param  int        $expire  有效时间 0为永久
     * @return bool
     */
    public function multiple_set($values,int $expire = 0): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $expire);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 如果不存在则写入缓存
     * @param  string $name    缓存变量名
     * @param  mixed  $value   存储数据
     * @param  int    $expire  有效时间 0为永久
     * @return mixed
     */
    public function remember(string $name, $value,int $expire = 0)
    {
        $result = $this->_driver->remember($name, $value, $expire);
        $this->append($name);
        return $result;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function tag_clear(): bool
    {
        // 指定标签清除
        foreach ($this->_tags as $tag) {
            $names = $this->_driver->tag_items_get($tag);
            $this->_driver->tag_clear($names);
            $this->_driver->handler()->del($tag);
        }
        return true;
    }
}
