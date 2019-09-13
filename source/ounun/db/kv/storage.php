<?php
namespace ounun\db\kv;

abstract class storage
{
    /** @var self 单例 */
    protected static $_instance;

    /**
     * @return $this 返回数据库连接对像
     */
    public static function instance(): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }


    protected $id, $handler;

    public static function &get_instance($storage = 'dba', $handler = 'flatfile')
    {
        $class = 'dbkv_storage_'.$storage;
        if (!class_exists($class)) {
            require(dirname(__FILE__).DS.'storage'.DS.$storage.'.php');
        }
        return new $class($handler);
    }

    abstract public function open($path, $mode = 'n');

    abstract public function popen($path, $mode = 'n');

    abstract public function set($key, $value);

    abstract public function get($key);

    abstract public function rm($key);

    abstract public function exists($key);

    abstract public function close();
}