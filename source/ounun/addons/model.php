<?php


namespace ounun\addons;

use \ounun\db\pdo;

abstract class model
{
    /** @var self 实例 */
    protected static $_instance;
    /** @var mixed 逻辑类 */
    protected $_logic;

    /**
     * @param pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(?pdo $db = null): self
    {
        if (empty(static::$_instance)) {
            if (empty($db)) {
                $db = pdo::i();
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
    public string $table;

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

    /** 初始化 */
    abstract protected function _initialize();

    /**
     * 更新数据
     *
     * @param int $id
     * @param array $data
     * @param bool $is_update_force
     * @param bool $is_update_default
     */
    public function update(int $id, array $data, bool $is_update_force = false, bool $is_update_default = false)
    {

    }

    /**
     * 插入数据
     *
     * @param array $data
     */
    public function insert(array $data)
    {

    }

    /**
     * 删除
     *
     * @param string $where_str
     * @param array $where_bind
     * @param int $limit
     */
    public function delete(string $where_str, array $where_bind, int $limit = 1)
    {

    }

    /** 逻辑类get */
    public function logic_get()
    {
        return $this->_logic;
    }

    /** 逻辑类set */
    public function logic_set($logic)
    {
        $this->_logic = $logic;
    }

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
