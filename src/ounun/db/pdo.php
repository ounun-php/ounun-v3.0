<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\db;

class pdo
{
    /** @var string 倒序大在前 - 排序 */
    const Order_Desc = 'desc';
    /** @var string 倒序大在前 - 排序 */
    const Order_Asc = 'asc';

    //（INSERT、REPLACE、UPDATE、DELETE）
    /** @var string 低优先级 */
    const Option_Low_Priority = 'LOW_PRIORITY';
    /** @var string 高优先级 */
    const Option_High_Priority = 'HIGH_PRIORITY';
    /** @var string 延时 (仅适用于MyISAM, MEMORY和ARCHIVE表) */
    const Option_Delayed = 'DELAYED';
    /** @var string 快的 */
    const Option_Quick = 'QUICK';
    /** @var string 出错时忽视 */
    const Option_Ignore = 'IGNORE';

    /** @var string utf8 */
    const Charset_Utf8 = 'utf8';
    /** @var string utf8mb4 */
    const Charset_Utf8mb4 = 'utf8mb4';
    /** @var string gbk */
    const Charset_Gbk = 'gbk';
    /** @var string latin1 */
    const Charset_Latin1 = 'latin1';

    /** @var string Mysql */
    const Driver_Mysql = 'mysql';

    /** @var string 更新操作Update */
    const Update_Update = 'update';
    /** @var string 更新操作Cut */
    const Update_Cut = 'cut';
    /** @var string 更新操作Add */
    const Update_Add = 'add';

    /** @var \PDO|null  pdo */
    protected ?\PDO $_pdo = null; // pdo
    /** @var \PDOStatement|null  stmt */
    protected ?\PDOStatement $_stmt = null; // stmt


    /** @var string */
    protected string $_last_sql = '';
    /** @var int */
    protected int $_query_times = 0;

    /** @var string 数据库名称 */
    protected string $_database = '';
    /** @var string 用户名 */
    protected string $_username = '';
    /** @var string 用户密码 */
    protected string $_password = '';
    /** @var string 数据库主机名称 */
    protected string $_host = '';
    /** @var int 数据库主机端口 */
    protected int $_port = 3306;
    /** @var string 数据库charset */
    protected string $_charset = 'utf8'; //'utf8mb4','utf8','gbk',latin1;
    /** @var string pdo驱动默认为mysql */
    protected string $_driver = 'mysql';
    /** @var string table前缀 - 替换成的前缀 */
    protected string $_table_prefix_replace = 'v2';
    /** @var string table前缀 - 被替换的常量 */
    protected string $_table_prefix_search = '#@_';

    /** @var string 当前table */
    protected string $_table = '';
    /** @var string 参数 */
    protected string $_option = '';
    /** @var array 字段 */
    protected array $_fields = [];
    /** @var array 字段 */
    protected array $_fields_json = [];
    /** @var array 排序字段 */
    protected array $_order = [];
    /** @var array 分组字段 */
    protected array $_group = [];
    /** @var string limit条件 */
    protected string $_limit = '';
    /** @var string 滤掉name和id两个字段都重复的记录 */
    protected string $_distinct = '';
    /** @var string where条件 */
    protected string $_where = '';
    /** @var string having条件 */
    protected string $_having = '';
    /** @var string 返回关联数据 assoc */
    protected string $_assoc = '';
    /** @var array 条件参数keys */
    protected array $_bind_keys = [];
    /** @var array 条件参数 */
    protected array $_bind_param = [];
    /** @var array 插入时已存在数据 更新内容 */
    protected array $_duplicate = [];
    /** @var string 插入时已存在数据 更新的扩展 */
    protected string $_duplicate_ext = '';
    /** @var string 关联表 */
    protected string $_join = '';

    /** @var bool 多条 */
    protected bool $_is_multiple = false;
    /** @var bool 是否替换插入 */
    protected bool $_is_replace = false;

    /** @var self 数据库实例 */
    protected static $_instance;
    /** @var array pdo实例 */
    private static array $_pdos = [];

    /**
     * @param string $tag
     * @param array $config
     * @return pdo|null 返回数据库连接对像
     */
    public static function i(string $tag = '', array $config = []): ?self
    {
        if (empty(static::$_instance)) {
            if (empty($config)) {
                if (empty($tag)) {
                    $tag = \ounun::database_default_get();
                }
                $config = \ounun::$database[$tag];
            }
            if ($config) {
                static::$_instance = new static($config);
            } else {
                return null;
            }
        }
        return static::$_instance;
    }

    /**
     * 创建MYSQL类
     * pdo constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $host            = explode(':', $config['host']);
        $this->_port     = (int)$host[1];
        $this->_host     = $host[0];
        $this->_database = $config['database'];
        $this->_username = $config['username'];
        $this->_password = $config['password'];

        if ($config['charset']) {
            $this->_charset = $config['charset'];
        }
        if ($config['driver']) {
            $this->_driver = $config['driver'];
        }
        if ($config['prefix_replace']) {
            $this->_table_prefix_replace = $config['prefix_replace'];
        }
        if ($config['prefix_search']) {
            $this->_table_prefix_search = $config['prefix_search'];
        }
    }

    /**
     * @param string $table 表名
     * @return pdo
     */
    public function table(string $table = ''): pdo
    {
        if ($this->_table || $table) {
            $this->_clean();
        }
        $this->_table = $table;
        return $this;
    }

    /**
     * 发送一条MySQL查询
     * @param string $sql
     * @param array|string $bind_params 条件参数
     * @param bool $check_active
     * @return $this
     */
    public function query(string $sql = '', $bind_params = [], bool $check_active = true)
    {
        if (strpos($sql, '?') !== false) {
            if ($check_active) {
                $this->active();
            }
            $sql = str_replace('?', $this->quote($bind_params), $sql);
            $this->_prepare($sql, false);
            $this->_stmt->execute();
        } else {
            $this->_prepare($sql, $check_active);
            if ($bind_params && is_array($bind_params)) {
                $bind_params = $this->_values_parse($bind_params);
            } else {
                $bind_params = [];
            }
            $this->_execute($bind_params);
        }
        return $this;
    }

    /**
     * 发送一条MySQL查询
     * @param string $sql
     * @param bool $check_active
     * @return $this
     */
    protected function _prepare(string $sql = '', bool $check_active = true): self
    {
        if ($check_active) {
            $this->active();
        }
        if ($sql) {
            if ($this->_table_prefix_replace) {
                $sql = str_replace($this->_table_prefix_search, $this->_table_prefix_replace, $sql);
            }
            $this->_last_sql = $sql;
        }
        $this->_bind_keys = $this->_keys_parse($this->_last_sql);
        $this->_stmt      = $this->_pdo->prepare($this->_last_sql);
        $this->_query_times++;
        return $this;
    }

    /**
     * 激活当前连接
     *   主要是解决如果多个库在同一个MYSQL实例时会出现不能自动切换
     * @return $this
     */
    public function active(): self
    {
        if (null == $this->_pdo) {
            $dsn = "{$this->_driver}:host={$this->_host};port={$this->_port};dbname={$this->_database};charset={$this->_charset}";
            $key = md5($dsn . $this->_username . $this->_password);
            if (self::$_pdos && isset(self::$_pdos[$key]) && $pdo = self::$_pdos[$key]) {
                $this->_pdo = $pdo;
            } else {
                $options = [];
                if (self::Driver_Mysql == $this->_driver) {
                    $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,];
                }
                $this->_pdo        = new \PDO($dsn, $this->_username, $this->_password, $options);
                self::$_pdos[$key] = $this->_pdo;
            }
        }
        return $this;
    }

    /**
     * @param array $data 数据
     * @return int 替换(插入)一條或多条記錄
     */
    public function insert(array $data): int
    {
        $duplicate = '';
        if (($this->_duplicate || $this->_duplicate_ext) && $this->_is_replace == false) {
            $update    = $this->_fields_update($this->_duplicate, $this->_duplicate);
            $duplicate = 'ON DUPLICATE KEY UPDATE ' . $this->_duplicate_ext . ' ' . implode(' , ', $update);
        }

        $fields = $this->_values_parse($this->_is_multiple ? array_shift($data) : $data);
        $cols   = array_keys($fields);

        $this->_prepare(($this->_is_replace ? 'REPLACE' : 'INSERT') . ' ' . $this->_option . ' INTO ' . $this->_table . ' (`' . implode('`, `', $cols) . '`) VALUES (:' . implode(', :', $cols) . ') ' . $duplicate . ';');
        if ($this->_is_multiple) {
            $this->_execute($fields);
            foreach ($data as &$v) {
                $fields = $this->_values_parse($v);
                $this->_execute($fields);
            }
        } else {
            $this->_execute($fields);
        }
        return $this->_pdo->lastInsertId();
    }

    /**
     * @param array $update_data
     * @param array $update_operate
     * @param string $where_str
     * @param array $where_bind
     * @param int $limit
     * @return int
     */
    public function update(array $update_data, array $update_operate = [], string $where_str = '', array $where_bind = [], int $limit = 1): int
    {
        $fields = $this->_values_parse($this->_is_multiple ? array_shift($update_data) : $update_data);
        $update = $this->_fields_update($fields, $update_operate);

        if ($where_str) {
            $this->where($where_str)->limit($limit);
        } else {
            $this->limit($limit);
        }

        $this->_prepare('UPDATE ' . $this->_option . ' ' . $this->_table . ' SET ' . implode(', ', $update) . ' ' . $this->_where . ' ' . $this->_limit . ' ;');

        if ($this->_is_multiple) {
            if ($where_bind && is_array($where_bind)) {
                if (array_keys($where_bind) === range(0, count($where_bind) - 1)) {
                    // echo __FILE__.':'.__LINE__."\n";
                    $i                 = 0;
                    $where_bind_fields = $this->_values_parse($where_bind[$i]);
                    $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                    foreach ($update_data as &$v) {
                        $i++;
                        $where_bind_fields = $this->_values_parse($where_bind[$i]);
                        $fields            = $this->_values_parse($v);
                        $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                    }
                } else {
                    // echo __FILE__.':'.__LINE__."\n";
                    $where_bind_fields = $this->_values_parse($where_bind);
                    $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                    foreach ($update_data as &$v) {
                        $where_bind_fields = $this->_values_parse($where_bind);
                        $fields            = $this->_values_parse($v);
                        $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                    }
                }
            } else {
                // echo __FILE__.':'.__LINE__."\n";
                $this->_execute(array_merge($this->_bind_param, $fields));
                foreach ($update_data as &$v) {
                    $fields = $this->_values_parse($v);
                    $this->_execute(array_merge($this->_bind_param, $fields));
                }
            }
        } else {
            if ($where_bind && is_array($where_bind)) {
                if (array_keys($where_bind) === range(0, count($where_bind) - 1)) {
                    // echo __FILE__.':'.__LINE__."\n";
                    foreach ($where_bind as $where_bind_v) {
                        $where_bind_fields = $this->_values_parse($where_bind_v);
                        $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                    }
                } else {
                    // echo __FILE__.':'.__LINE__."\n";
                    $where_bind_fields = $this->_values_parse($where_bind);
                    $this->_execute(array_merge($this->_bind_param, $fields, $where_bind_fields));
                }
            } else {
                // echo __FILE__.':'.__LINE__."\n";
                $this->_execute(array_merge($this->_bind_param, $fields));
            }
        }
        return $this->_stmt->rowCount();
    }

    /**
     * @param bool $force_prepare 是否强行 prepare
     * @return int
     */
    public function column_count(bool $force_prepare = false): int
    {
        if (null == $this->_stmt || $force_prepare) {
            $fields = ($this->_fields && is_array($this->_fields)) ? implode(',', $this->_fields) : '*';
            $this->_prepare('SELECT ' . $this->_distinct . ' ' . $fields . ' FROM ' . $this->_table . ' ' . $this->_join . ' ' . $this->_where . ' ' . $this->_group_get() . ' ' . $this->_having . ' ;')
                ->_execute($this->_bind_param);
        }
        return $this->_stmt->columnCount();
    }

    /**
     * @param bool $force_prepare 是否强行 prepare
     * @return array 得到一条数据数组
     */
    public function column_one(bool $force_prepare = false)
    {
        if (null == $this->_stmt || $force_prepare) {
            $fields = ($this->_fields && is_array($this->_fields)) ? implode(',', $this->_fields) : '*';
            $this->_prepare('SELECT ' . $this->_distinct . ' ' . $fields . ' FROM ' . $this->_table . ' ' . $this->_join . ' ' . $this->_where . ' ' . $this->_group_get() . ' ' . $this->_having . ' ' . $this->_order_get() . ' ' . $this->_limit . ';')
                ->_execute($this->_bind_param);
        }
        if ($this->_fields_json) {
            $rs = $this->_stmt->fetch(\PDO::FETCH_ASSOC);
            if ($rs && is_array($rs)) {
                foreach ($this->_fields_json as $field) {
                    if (isset($rs[$field])) {
                        $rs[$field] = json_decode_array($rs[$field]);
                    }
                }
            }
            return $rs;
        }
        return $this->_stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 得到多条數椐數組的数组
     * @param bool $force_prepare 是否强行 prepare
     * @return array
     */
    public function column_all(bool $force_prepare = false)
    {
        if (null == $this->_stmt || $force_prepare) {
            $fields = ($this->_fields && is_array($this->_fields)) ? implode(',', $this->_fields) : '*';
            $this->_prepare('SELECT ' . $this->_distinct . ' ' . $fields . ' FROM ' . $this->_table . ' ' . $this->_join . ' ' . $this->_where . ' ' . $this->_group_get() . ' ' . $this->_having . ' ' . $this->_order_get() . ' ' . $this->_limit . ';')
                ->_execute($this->_bind_param);
        }
        if ($this->_assoc || $this->_fields_json) {
            $rs  = [];
            $rs0 = $this->_stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($this->_assoc && $this->_fields_json) {
                if ($rs0 && is_array($rs0)) {
                    foreach ($rs0 as $v) {
                        foreach ($this->_fields_json as $field) {
                            if (isset($v[$field])) {
                                $v[$field] = json_decode_array($v[$field]);
                            }
                        }
                        $rs[$v[$this->_assoc]] = $v;
                    }
                }
            } elseif ($this->_fields_json) {
                if ($rs0 && is_array($rs0)) {
                    foreach ($rs0 as $k => $v) {
                        foreach ($this->_fields_json as $field) {
                            if (isset($v[$field])) {
                                $v[$field] = json_decode_array($v[$field]);
                            }
                        }
                        $rs[$k] = $v;
                    }
                }
            } else { // elseif($this->_assoc){
                if ($rs0 && is_array($rs0)) {
                    foreach ($rs0 as $v) {
                        $rs[$v[$this->_assoc]] = $v;
                    }
                }
            }
            return $rs;
        } else {
            return $this->_stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param string $field
     * @param mixed $default_value 默认值
     * @param bool $force_prepare 是否强行 prepare
     * @return mixed|null  直接返回对应的值
     */
    public function column_value(string $field, $default_value = null, bool $force_prepare = false)
    {
        $rs    = $this->column_one($force_prepare);
        $field = trim(str_replace('`', '', $field));
        if ($rs && $rs[$field]) {
            return $rs[$field];
        } else {
            return $default_value;
        }
    }

    /**
     * 删除
     * @param int $limit 删除limit默认为1
     * @return int
     */
    public function delete(int $limit = 1): int
    {
        if ($limit === 0) {
            $this->_prepare('DELETE ' . $this->_option . ' FROM ' . $this->_table . ' ' . $this->_where . ';')
                ->_execute($this->_bind_param);
        } else {
            $this->limit($limit)
                ->_prepare('DELETE ' . $this->_option . ' FROM ' . $this->_table . ' ' . $this->_where . ' ' . $this->_limit . ';')
                ->_execute($this->_bind_param);
        }
        return $this->_stmt->rowCount(); // 取得前一次 MySQL 操作所影响的记录行数
    }

    /**
     * 设定插入数据为替换
     * @param bool $is_replace
     * @return $this;
     */
    public function replace(bool $is_replace = false): self
    {
        $this->_is_replace = $is_replace;
        return $this;
    }

    /**
     * 多条数据 true:多条数据 false:单条数据
     * @param bool $is_multiple
     * @return $this;
     */
    public function multiple(bool $is_multiple = false): self
    {
        $this->_is_multiple = $is_multiple;
        return $this;
    }

    /**
     * 参数 install update replace
     * @param string $option
     * @return $this
     */
    public function option(string $option = ''): self
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * 滤掉name和id等字段都重复的记录
     * @param string $distinct
     * @return $this
     */
    public function distinct(string $distinct = 'distinct'): self
    {
        $this->_distinct = $distinct;
        return $this;
    }

    /**
     * 插入时已存在数据
     * @param array $duplicate 更新内容   [字段=>操作]
     * @param string $duplicate_ext 更新的扩展
     * @return $this
     */
    public function duplicate(array $duplicate, string $duplicate_ext = ''): self
    {
        $this->_duplicate     = $duplicate;
        $this->_duplicate_ext = $duplicate_ext;
        return $this;
    }

    /**
     * 指定查询数量
     * @param int $length 查询数量
     * @param int $offset 起始位置
     * @return $this
     */
    public function limit(int $length, int $offset = 0): self
    {
        if (0 == $offset) {
            $this->_limit = "LIMIT {$length}";
        } else {
            $this->_limit = "LIMIT {$offset},{$length}";
        }
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function field(string $field = '*'): self
    {
        $this->_fields[] = $field;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function json_field(array $fields): self
    {
        foreach ($fields as $field) {
            $field = trim(str_replace('`', '', $field));
            if ($field && !in_array($field, $this->_fields_json)) {
                $this->_fields_json[] = $field;
            }
        }
        return $this;
    }

    /**
     * @param string $assoc 设定返回关联数据 assoc
     * @return $this
     */
    public function assoc(string $assoc = ''): self
    {
        $this->_assoc = $assoc;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @return $this
     */
    public function inner_join(string $inner_join, string $on): self
    {
        $this->_join = 'INNER JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return $this
     */
    public function full_join(string $inner_join, string $on, bool $is_outer = false): self
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join = 'FULL ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return $this
     */
    public function left_join(string $inner_join, string $on, bool $is_outer = false): self
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join = 'LEFT ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return $this
     */
    public function right_join(string $inner_join, string $on, bool $is_outer = false): self
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join = 'RIGHT ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * 条件
     * @param string $where_str 条件
     * @param array $bind_params 条件参数
     * @return $this
     */
    public function where(string $where_str = '', array $bind_params = []): self
    {
        if ($where_str) {
            if ($this->_where) {
                $this->_where = $this->_where . ' ' . $where_str;
            } else {
                $this->_where = 'WHERE ' . $where_str;
            }
            if ($bind_params && is_array($bind_params)) {
                $bind_params       = $this->_values_parse($bind_params);
                $this->_bind_param = array_merge($this->_bind_param, $bind_params);
            }
        }
        return $this;
    }

    /**
     * having条件
     * @param string $having_str 条件
     * @param array $bind_params 条件参数
     * @return $this
     */
    public function having(string $having_str = '', array $bind_params = []): self
    {
        if ($having_str) {
            if ($this->_having) {
                $this->_having = $this->_having . ' ' . $having_str;
            } else {
                $this->_having = 'HAVING ' . $having_str;
            }
            if ($bind_params && is_array($bind_params)) {
                $bind_params       = $this->_values_parse($bind_params);
                $this->_bind_param = array_merge($this->_bind_param, $bind_params);
            }
        }
        return $this;
    }

    /**
     * 指定排序
     * @param string $field 排序字段
     * @param string $order 排序
     * @return $this
     */
    public function order(string $field, string $order = self::Order_Desc): self
    {
        $this->_order[] = ['field' => $field, 'order' => $order];
        return $this;
    }

    /**
     * 聚合分组
     * @param string $field
     * @return $this
     */
    public function group(string $field): self
    {
        $this->_group[] = $field;
        return $this;
    }

    /**
     * COUNT查询
     * @param string $field 字段名
     * @param string $alias SUM查询别名
     * @return $this
     */
    public function count(string $field = '*', string $alias = '`count`'): self
    {
        return $this->field("COUNT({$field}) AS {$alias}");
    }

    /**
     * COUNT查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return int
     */
    public function count_value(string $field = '*', string $alias = '`count`', $default_value = 0)
    {
        return $this->count($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * SUM查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return $this
     */
    public function sum(string $field, string $alias = '`sum`'): self
    {
        return $this->field("SUM({$field}) AS {$alias}");
    }

    /**
     * SUM查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return float
     */
    public function sum_value(string $field, string $alias = '`sum`', $default_value = 0)
    {
        return $this->sum($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * MIN查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return $this
     */
    public function min(string $field, string $alias = '`min`'): self
    {
        return $this->field("MIN({$field}) AS {$alias}");
    }

    /**
     * MIN查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return float
     */
    public function min_value(string $field, string $alias = '`min`', $default_value = 0)
    {
        return $this->min($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * MAX查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return $this
     */
    public function max(string $field, string $alias = '`max`'): self
    {
        return $this->field("MAX({$field}) AS {$alias}");
    }

    /**
     * MAX查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return float
     */
    public function max_value(string $field, string $alias = '`max`', $default_value = 0)
    {
        return $this->max($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * AVG查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return $this
     */
    public function avg(string $field, string $alias = '`avg`'): self
    {
        return $this->field("AVG({$field}) AS {$alias}");
    }

    /**
     * AVG查询
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return float
     */
    public function avg_value(string $field, string $alias = '`avg`', $default_value = 0)
    {
        return $this->avg($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * 返回查询次数
     * @return int
     */
    public function query_times(): int
    {
        return $this->_query_times;
    }

    /**
     * 最后一次插入的自增ID
     * @return int
     */
    public function insert_id(): int
    {
        return $this->_pdo->lastInsertId();
    }

    /**
     * 最后一次更新影响的行数
     * @return int
     */
    public function affected(): int
    {
        return $this->_stmt->rowCount();
    }

    /**
     * 得到最后一次查询的sql
     * @return string
     */
    public function last_sql(): string
    {
        return $this->_last_sql;
    }

    /**
     * 返回PDO
     * @return \PDO 返回PDO
     */
    public function pdo(): \PDO
    {
        return $this->_pdo;
    }

    /**
     * 开启事务
     * @return bool
     */
    public function trans_begin()
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function trans_commit()
    {
        return $this->_pdo->commit();
    }

    /**
     * 关闭事务
     * @return bool
     */
    public function trans_rollback()
    {
        return $this->_pdo->rollBack();
    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     * @return bool
     */
    public function trans_check($res)
    {
        if ($res) {
            return $this->trans_commit();
        } else {
            return $this->trans_rollback();
        }
    }

    /**
     * 返回PDO
     * @return \PDOStatement 返回PDOStatement
     */
    public function stmt(): \PDOStatement
    {
        return $this->_stmt;
    }

    /**
     * 是否连接成功
     * @return bool
     */
    public function is_connect(): bool
    {
        return $this->_pdo ? true : false;
    }


    /**
     * 捡查指定字段数据是否存在
     * @param string $field 字段
     * @param mixed $value 值
     * @param int $param 值数据类型 PDO::PARAM_INT
     * @return bool
     */
    public function exists(string $field, $value, int $param = \PDO::PARAM_STR): bool
    {
        if ($field) {
            $k  = $this->_param2types($param);
            $rs = $this->where(" {$field} = :field ", [$k . ':field' => $value])->count()->column_one();
            if ($rs && $rs['count']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 为 SQL 查询里的字符串添加引号(特殊情况时才用)
     * @param mixed $data
     * @param int $type
     * @return string
     */
    public function quote($data, int $type = \PDO::PARAM_INT)
    {
        $rs = [];
        if (is_array($data)) {
            foreach ($data as $value) {
                $rs[] = $this->_pdo->quote($value, $type);
            }
        } else {
            $rs[] = $this->_pdo->quote($data, $type);
        }
        return implode(',', $rs);
    }

    /** order */
    protected function _order_get()
    {
        $rs = '';
        if ($this->_order && is_array($this->_order)) {
            $rs2 = [];
            foreach ($this->_order as $v) {
                $rs2[] = $v['field'] . ' ' . $v['order'];
            }
            $rs = ' order by ' . implode(',', $rs2);
        }
        return $rs;
    }

    /** group */
    protected function _group_get()
    {
        $rs = '';
        if ($this->_group && is_array($this->_group)) {
            $rs = ' group by ' . implode(',', $this->_group);
        }
        return $rs;
    }

    /**
     * @param string $types
     * @return int
     */
    protected function _types2param(string $types = '')
    {
        switch ($types) {
            case 'i':
                return \PDO::PARAM_INT;
            //break;
            case 's': // -> default
            case 'd': // -> default
                return \PDO::PARAM_STR;
            //break;
            case 'b':
                return \PDO::PARAM_LOB;
            //break;
            case 'null':
                return \PDO::PARAM_NULL;
            case 'bool':
                return \PDO::PARAM_BOOL;
            default:
                return \PDO::PARAM_STR;
        }
    }

    /**
     * @param int $param
     * @return string
     */
    protected function _param2types(int $param = \PDO::PARAM_STR)
    {
        switch ($param) {
            case \PDO::PARAM_INT:
                return 'i';
            // break;
            case \PDO::PARAM_STR:
                return 's';
            // break;
            case \PDO::PARAM_LOB:
                return 'b';
            // break;
            case \PDO::PARAM_NULL:
                return 'null';
            // break;
            case \PDO::PARAM_BOOL:
                return 'bool';
            // break;
            default:
                return '';
        }
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function _keys_parse(string $sql)
    {
        $splits = preg_split('/(:[A-Za-z0-9_]+)\b/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $result = [];
        foreach ($splits as $v) {
            if ($v[0] == ':') {
                $key          = substr($v, 1);
                $result[$key] = $key;
            }
        }
        return array_values($result);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _values_parse(array $data)
    {
        $fields = [];
        if ($data && is_array($data)) {
            foreach ($data as $col => &$val) {
                if ('-' == $col[1]) {
                    list($type_length, $field) = explode(':', $col);
                    list($type, $length) = explode('-', $type_length);
                    $fields[$field] = [
                        'field'  => ':' . $field,
                        'value'  => $val,
                        'type'   => $this->_types2param($type),
                        'length' => $length
                    ];
                } elseif (':' == $col[1]) {
                    list($type, $field) = explode(':', $col);
                    $fields[$field] = [
                        'field' => ':' . $field,
                        'value' => $val,
                        'type'  => $this->_types2param($type),
                    ];
                } elseif (':' == $col[0]) {
                    list($type, $field) = explode(':', $col);
                    $fields[$field] = [
                        'field' => ':' . $field,
                        'value' => $val,
                        'type'  => $this->_types2param($type),
                    ];
                } else {
                    $fields[$col] = [
                        'field' => ':' . $col,
                        'value' => $val,
                        'type'  => $this->_types2param(''),
                    ];
                }
            }
        }
        return $fields;
    }

    /**
     * @param array $fields_data
     * @param array $operate
     * @return array
     */
    protected function _fields_update(array &$fields_data, array &$operate = [])
    {
        $update = [];
        foreach ($fields_data as $col => $val) {
            if ($operate[$col] == self::Update_Add) {
                $update[] = "`$col` = `{$col}` + :{$col} ";
            } elseif ($operate[$col] == self::Update_Cut) {
                $update[] = "`$col` = `{$col}` - :{$col} ";
            } else {
                $update[] = "`{$col}` = :{$col} ";
            }
        }
        return $update;
    }

    /**
     * @param array $fields
     */
    protected function _execute(array $fields)
    {
        // $i = 0; echo "\n\n";
        foreach ($this->_bind_keys as $key) {
            $v = $fields[$key];
            if ($v) {
                // $i++; echo "{$i} -> field:{$v['field']}, value:{$v['value']}, type:{$v['type']}\n";
                if (\PDO::PARAM_STR == $v['type'] && isset($v['length'])) {
                    $this->_stmt->bindParam($v['field'], $v['value'], $v['type'], $v['length']);
                } else {
                    $this->_stmt->bindParam($v['field'], $v['value'], $v['type']);
                }
            } else {
                $this->_stmt->debugDumpParams();
                error_php("SQL:Can't find \$fields[{$key}] ", '', 'mysql');
            }
        }

        try {
            $this->_stmt->execute();
        } catch (\Exception $e) {
            $this->_stmt->debugDumpParams();
            error_php("Sql Error:" . $e->getMessage() .
                "\n\tlast_sql:" . $this->_last_sql . "\n" .
                "\tbind_param:" . json_encode_unescaped($this->_bind_param) . "\n" .
                "\tfields:" . json_encode_unescaped($fields) . "\n", '', 'mysql');
        }
    }

    /**
     * 清理
     */
    protected function _clean()
    {
        $this->_stmt = null;

        $this->_option        = '';
        $this->_distinct      = '';
        $this->_fields        = [];
        $this->_fields_json   = [];
        $this->_order         = [];
        $this->_group         = [];
        $this->_limit         = '';
        $this->_where         = '';
        $this->_having        = '';
        $this->_assoc         = '';
        $this->_bind_keys     = [];
        $this->_bind_param    = [];
        $this->_duplicate     = [];
        $this->_duplicate_ext = '';
        $this->_join          = '';

        $this->_is_multiple = false;
        $this->_is_replace  = false;
    }

    /**
     * 当类需要被删除或者销毁这个类的时候自动加载__destruct这个方法
     */
    public function __destruct()
    {
        $this->_clean();
        $this->_pdo = null;
    }
}
