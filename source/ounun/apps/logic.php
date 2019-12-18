<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\apps;


abstract class logic
{
    /** @var array 错误提示信息  */
    const Error_Msg  = [];

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

    /** @var array 数据 */
    protected $_data = [];

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

    protected   $_table_fields = [];
    protected   $_primary = '';
    protected   $_fields = [];
    protected   $_readonly = [];
    protected   $_create_auto_fill = [];
    protected   $_update_auto_fill = [];
    protected   $_filters_input = [];
    protected   $_filters_output = [];
    protected   $_validators = [];
    protected   $_options = [];

    protected   $_fetch_style = \PDO::FETCH_ASSOC;

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
     * @return mixed|null
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


    function select($where = null, $fields = '*', $order = null, $limit = null, $offset = null, $data = [], $multiple = true)
    {
        if(!empty($this->_options))
        {
            $fields = isset($this->_options['distinct']) ? "distinct ".$this->_options['distinct'] : isset($this->_options['field']) ? $this->_options['field'] : $fields;
            $where = isset($this->_options['where']) ? $this->_options['where'] : $where;
            $having = isset($this->_options['having']) ? $this->_options['having'] : null;
            $order = isset($this->_options['order']) ? $this->_options['order'] : $order;
            $group = isset($this->_options['group']) ? $this->_options['group'] : null;
            $limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
            $offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
            $this->_options = [];
        }

        if (is_array($fields)) {
            $fields = '`'.implode('`,`', $fields).'`';
        }

        $this->_where($where);

        if(!$this->_before_select($where)) {
            return false;
        }

        $sql = "SELECT $fields FROM `$this->_table` ";
        if ($where) $sql .= " WHERE $where ";
        if ($order) $sql .= " ORDER BY $order ";
        if ($group) $sql .= " GROUP BY $group ";
        if ($having) $sql .= " HAVING $having ";
        if (is_null($limit) && !$multiple) $sql .= " LIMIT 1 ";

        $method = $multiple ? 'select' : 'get';

        $result = is_null($limit) ? $this->db->$method($sql, $data, $this->_fetch_style) : $this->db->limit($sql, $limit, $offset, $data, $this->_fetch_style);
        if ($result === false) {
            $this->error = $this->db->error();
            return false;
        }
        else {
            if ($multiple) {
                array_map(array(&$this, '_data_output'), $result);
            }
            else {
                $this->_data_output($result);
                $this->_data = $result;
            }
            $this->_after_select($result, $multiple);
            return $result;
        }
    }

    protected function _before_select(&$where) { return true; }
    protected function _after_select(&$result, $multiple = true) {}

    public function page($where = null, $fields = '*', $order = null, $page = 1, $size = 20, $data = [])
    {
        $offset = ($page-1)*$size;
        return $this->select($where, $fields, $order, $size, $offset, $data, true);
    }


    function insert($data = [])
    {
        $this->_data($data);

        if(!$this->_before_insert($data)) {
            return false;
        }

        $this->_create_autofill($data);

        if (!$this->_validate($data)) return false;

        $this->_data_input($data);

        $id = $this->db->insert("INSERT INTO `$this->_table` (`".implode('`,`', array_keys($data))."`) VALUES(".implode(',', array_fill(0, count($data), '?')).")", array_values($data));
        if ($id === false)
        {
            $this->error = $this->db->error();
            return false;
        }
        else
        {
            $this->_after_insert($data);
            return $id;
        }
    }

    protected function _before_insert(&$data) {return true;}
    protected function _after_insert(&$data) {}


    function update($data = [], $where = null, $limit = null, $order = null)
    {
        if(!empty($this->_options))
        {
            $where = isset($this->_options['where']) ? $this->_options['where'] : $where;
            $order = isset($this->_options['order']) ? $this->_options['order'] : $order;
            $limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
            $offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
            $this->_options = [];
        }

        $this->_data($data);

        $this->_where($where);

        if(!$this->_before_update($data, $where)) return false;

        $this->_update_autofill($data);

        $this->_readonly($data);

        if (!$this->_validate($data)) return false;

        $this->_data_input($data);

        $sql = "UPDATE `$this->_table` SET `".implode('`=?,`', array_keys($data))."`=?";
        if ($where) $sql .= " WHERE $where ";
        if ($order) $sql .= " ORDER BY $order ";
        if ($limit) $sql .= " LIMIT $limit ";
        $result = $this->db->update($sql, array_values($data));

        if ($result === FALSE)
        {
            $this->error = $this->db->error();
            return false;
        }
        else
        {
            $this->_after_update($data, $where);
            return $result;
        }
    }

    protected function _before_update(&$data, $where) {return true;}
    protected function _after_update(&$data, $where) {}

    public function set_field($field, $value, $where = null)
    {
        return $this->update(array($field=>$value), $where);
    }

    public function set_inc($field, $where = null, $step = 1, $data = [])
    {
        $this->_where($where);
        return $this->db->update("UPDATE `$this->_table` SET `$field`=`$field`+$step WHERE $where", $data);
    }

    public function set_dec($field, $where = null, $step = 1, $data = [])
    {
        $this->_where($where);
        return $this->db->update("UPDATE `$this->_table` SET `$field`=`$field`-$step WHERE $where", $data);
    }

    function delete($where = null, $limit = null, $order = null, $data = [])
    {
        if(!empty($this->_options)) {
            $where = isset($this->_options['where']) ? $this->_options['where'] : $where;
            $order = isset($this->_options['order']) ? $this->_options['order'] : $order;
            $limit = isset($this->_options['limit']) ? $this->_options['limit'] : $limit;
            $offset = isset($this->_options['offset']) ? $this->_options['offset'] : $offset;
            $this->_options = [];
        }

        $this->_where($where);

        if(!$this->_before_delete($where)) {
            return false;
        }

        $sql = "DELETE FROM `$this->_table`";
        if ($where) $sql .= " WHERE $where ";
        if ($order) $sql .= " ORDER BY $order ";
        if ($limit) $sql .= " LIMIT $limit ";

        $result = $this->db->delete($sql, $data);
        if ($result === FALSE) {
            $this->error = $this->db->error();
            return false;
        } else {
            $this->_after_delete($where);
            return $result;
        }
    }

    protected function _before_delete(&$where) {return true;}
    protected function _after_delete(&$where) {}


    private function _validate(& $data)
    {
        if (empty($this->_validators)) return true;
        $validator = & factory::validator();
        foreach ($this->_validators as $field=>$v)
        {
            if (!isset($data[$field]) || ($data[$field]['required'] == false && $v == '')) continue;
            if (!$validator->execute($data[$field], $v))
            {
                $this->error = $validator->error();
                return false;
            }
        }
        return true;
    }

    private function _create_autofill(& $data)
    {
        if (empty($this->_create_autofill)) {
            return true;
        }
        foreach ($this->_create_autofill as $field=>$val) {
            if (!isset($data[$field])) $data[$field] = $val;
        }
    }

    private function _update_autofill(& $data)
    {
        if (empty($this->_update_autofill)) {
            return true;
        }
        foreach ($this->_update_autofill as $field=>$val) {
            if (!isset($data[$field])) {
                $data[$field] = $val;
            }
        }
    }

    private function _readonly(& $data)
    {
        if (empty($this->_readonly)) {
            return true;
        }
        foreach ($this->_readonly as $field=>$val) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
    }

    private function _where(& $where)
    {
        if (empty($where) && isset($this->_data[$this->_primary])) {
            $where = $this->_data[$this->_primary];
        }

        if (is_numeric($where)) {
            $where = "`$this->_primary`=$where";
        }
        elseif (is_array($where)) {
            $where = array_map('addslashes', $where);

            if (isset($where[0])) {
                $ids = is_numeric($where[0]) ? implode_ids($where, ',') : "'".implode_ids($where, "','")."'";
                $where = "`$this->_primary` IN($ids)";
            }
            else {
                $condition = [];
                foreach ($where as $field=>$value) {
                    $condition[] = "`$field`='$value'";
                }
                $where = implode(' AND ', $condition);
            }
        }
        elseif (preg_match("/^[0-9a-z\'\"\,\s]+$/i", $where)) {
            $where = strpos($where, ',') === false ? "`$this->_primary`='$where'" : "`$this->_primary` IN($where)";
        }
    }

    /**
     * @param $data
     * @param $keys
     * @return mixed
     */
    protected function filter_array($data, $keys)
    {
        foreach ($data as $field=>$v) {
            if (!in_array($field, $keys)) {
                unset($data[$field]);
            }
        }
        return $data;
    }


    /**
     * 捡查指定字段数据是否存在
     * @param string $field 字段
     * @param mixed $value 值
     * @param int $param 值数据类型 PDO::PARAM_INT
     * @return bool
     */
    public function exists($field, $value, int $param = \PDO::PARAM_STR)
    {
        return $this->db->table($this->table)->exists($field, $value,$param);
    }

    /** PDO::FETCH_* */
    public function fetch_style_set($style)
    {
        $this->_fetch_style = $style;
    }

    /**
     * 错误代码
     * @param int $error_code
     * @param null $data
     * @param array $extend
     * @return array
     */
    protected function error($error_code = 1, $data = null, $extend = [])
    {
        if(static::Error_Msg && isset(static::Error_Msg[$error_code])){
            $msg = static::Error_Msg[$error_code]."(code:{$error_code})";
        }else{
            $msg = "错误代码暂没定义(code:{$error_code})";
        }
        return error($msg,$error_code,$data,$extend);
    }
}
