<?php


namespace ounun\dc;


class tagset
{
    /** @var string  标签的缓存Key */
    protected $_tag   = '';

    /** @var string  最大长度 */
    protected $_max_length   = 0;

    /** @var driver 缓存句柄 */
    protected $_driver;

    /**
     * 架构函数
     * @param  string $tag         缓存标签
     * @param  driver $driver      缓存对象
     * @param  int    $max_length  最大长度
     */
    public function __construct(string $tag, driver $driver, int $max_length = 10000)
    {
        $this->_tag        = $tag;
        $this->_driver     = $driver;
        $this->_max_length = $max_length;
    }

    /**
     * 追加缓存标识到标签
     * @param string $key       缓存变量名
     * @param bool $add_prefix  是否活加前缀
     */
    public function append(string $key, bool $add_prefix = true): void
    {
        if($add_prefix){
            $key = $this->_driver->tag_key_get($key);
        }
        $this->_driver->push($this->_tag, $key,$this->_max_length,0,true);
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
