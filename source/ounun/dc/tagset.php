<?php


namespace ounun\dc;


class tagset
{
    /** @var string  标签的缓存Key */
    protected $_tag   = '';

    /** @var driver 缓存句柄 */
    protected $_driver;

    /**
     * 架构函数
     * @param  string $tag    缓存标签
     * @param  driver $driver 缓存对象
     */
    public function __construct(string $tag, driver $driver)
    {
        $this->_tag     = $tag;
        $this->_driver  = $driver;
    }

    /**
     * 追加缓存标识到标签
     * @param  string $key 缓存变量名
     * @return void
     */
    public function append(string $key, bool $): void
    {
        $key = $this->_driver->cache_key_get($key);
        $this->_driver->push($this->_tag, $key);
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
     * @param  string $key    缓存变量名
     * @param  mixed  $value   存储数据
     * @param  int    $expire  有效时间 0为永久
     * @return mixed
     */
    public function remember(string $key, $value, int $expire = 0)
    {
        $result = $this->_driver->remember($key, $value, $expire);
        $this->append($key);
        return $result;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function tag_clear(): bool
    {
        // 指定标签清除
        if($this->_tag){
            $this->_driver->tag_clear($this->_tag);
        }
        return true;
    }
}
