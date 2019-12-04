<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\apps;


abstract class logic
{
    /** @var self 实例 */
    protected static $instance;

    /**
     * @param \ounun\db\pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(\ounun\db\pdo $db = null): self
    {
        if (empty(static::$instance)) {
            if (empty($db)) {
                $db = \v::db_v_get();
            }
            static::$instance = new static($db);
        }
        return static::$instance;
    }

    /** @var \ounun\db\pdo */
    public $db;

    /** @var string  */
    public $table = '';
    /** @var array 数据表结构 */
    public $table_options = [
//        $_tablefields = [],
//        $_primary = null,
//        $_fields = [],
//        $_readonly = [],
//        $_create_autofill = [],
//        $_update_autofill = [],
//        $_filters_input = [],
//        $_filters_output = [],
//        $_validators = [],
//        $_options = [],
//        $_fetch_style = self::FETCH_ASSOC;
    ];
    /** @var array 数据 */
    protected $_data = [];

    /**
     * cms constructor.
     * @param \ounun\db\pdo $db
     */
    public function __construct(\ounun\db\pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        static::$instance = $this;
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
