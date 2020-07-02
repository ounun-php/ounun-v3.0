<?php


namespace ounun\apps;

use \ounun\db\pdo;

abstract class model
{
    /** @var self 实例 */
    protected static $_instance;

    /**
     * @param pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(?pdo $db = null): self
    {
        if (empty(static::$_instance)) {
            if (empty($db)) {
                $db = \v::db_v_get();
            }
            static::$_instance = new static($db);
        }
        return static::$_instance;
    }

    /** @var array 数据 */
    protected array $_data = [];

    /** @var pdo */
    public pdo $db;

    /** @var string */
    public string $table = '';

    /** @var array 数据表结构 */
    public array $options = [
        'fields'          => [],
        'primary'         => '',
        'readonly'        => [],
        'create_autofill' => [],
        'update_autofill' => [],
        'filters_input'   => [],
        'filters_output'  => [],
        'validators'      => [],
        'options'         => []
    ];


    /**
     * cms constructor.
     * @param pdo $db
     */
    public function __construct(?pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        // 控制器初始化
        $this->_initialize();
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
