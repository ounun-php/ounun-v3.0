<?php


namespace ounun\addons;

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
                $db = pdo::i();
            }
            static::$_instance = new static($db);
        }
        return static::$_instance;
    }

    /** @var array 数据 */
    protected array $_data = [];

    /** @var mixed 逻辑类 */
    protected $_logic;

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
     * @param string $where_str
     * @param array $where_bind
     * @param array $data
     * @param string|null $table
     * @return int
     */
    public function update(string $where_str, array $where_bind,array $data,?string $table = null)
    {
        $table ??= $this->table;
        return  $this->db->table($table)->where($where_str, $where_bind)->update($data);
    }

    /**
     * 插入数据
     *
     * @param array $data
     * @param string|null $table
     * @return int
     */
    public function insert(array $data, ?string $table = null)
    {
        $table ??= $this->table;
        return $this->db->table($table)->insert($data);
    }

    /**
     * 删除
     * @param string $where_str
     * @param array $where_bind
     * @param int $limit 删除limit默认为1
     * @param string|null $table
     * @return int
     */
    public function delete(string $where_str, array $where_bind, int $limit = 1, ?string $table = null)
    {
        $table ??= $this->table;
        return $this->db->table($table)->where($where_str, $where_bind)->delete($limit);
    }

    /**
     * @param string $where_str
     * @param array $where_bind
     * @param string|null $table
     * @param string $field
     * @return array 得到一条数据数组
     */
    public function column_one(string $where_str, array $where_bind, ?string $table = null, string $field = '*')
    {
        $table ??= $this->table;
        return $this->db
            ->table($table)
            ->field($field)
            ->where($where_str, $where_bind)
            ->limit(1)
            ->column_one();
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
