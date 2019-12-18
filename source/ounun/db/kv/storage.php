<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
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

    abstract public function open($path, $mode = 'n');

    abstract public function popen($path, $mode = 'n');

    abstract public function set($key, $value);

    abstract public function get($key);

    abstract public function rm($key);

    abstract public function exists($key);

    abstract public function close();
}
