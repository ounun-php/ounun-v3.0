<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\db;

use Exception;
use ounun;
use PDOStatement;
use Throwable;

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
    protected ?\PDO $_pdo = null;
    /** @var PDOStatement|null  stmt */
    protected ?PDOStatement $_stmt = null;


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
    /** @var array SELECT字段 */
    protected array $_fields = [];
    /** @var array 字段 */
    protected array $_fields_json = [];
    /** @var array 字段类型 */
    protected array $_fields_type = [];

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
    protected array $_bind_fields = [];
    /** @var array 条件参数 */
    protected array $_bind_values = [];

    /** @var array 插入时已存在数据 更新内容(数据) */
    protected array $_duplicate_values = [];
    /** @var array|null 插入时已存在数据 更新内容(操作 = + -) */
    protected ?array $_duplicate_operate = null;
    /** @var string 插入时已存在数据 更新的扩展 */
    protected string $_duplicate_extend_str = '';
    /** @var string 关联表 */
    protected string $_join = '';

    /** @var bool 多条 */
    protected bool $_is_multiple = false;
    /** @var bool 是否替换插入 */
    protected bool $_is_replace = false;

    /** @var self 数据库实例 */
    protected static $_instance;
    /** @var array pdo实例 */
    private static array $_pdo_list = [];

    /**
     * @param string $tag
     * @param array $config
     * @return self|null 返回数据库连接对像
     */
    public static function i(string $tag = '', array $config = []): ?self
    {
        if (empty(static::$_instance)) {
            if (empty($config)) {
                if (empty($tag)) {
                    $tag = ounun::database_default();
                }
                $config = ounun::$database[$tag] ?? null;
            }
            if ($config) {
                static::$_instance = new static($config);
            } else {
                error_php('error db tag:' . $tag . ' default:' . ounun::database_default());
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
     * @return self
     */
    public function table(string $table): self
    {
        if ($this->_table || $table) {
            $this->_clean();
            $this->_table = $table;
        }
        return $this;
    }

    /**
     * 发送一条MySQL查询
     *
     * @param string $sql
     * @param array $bind_params 条件参数
     * @return $this
     */
    public function query(string $sql, array $bind_params = []): self
    {
        if ($bind_params && isset($bind_params['?'])) {
            $sql = $this->quote_array($sql, $bind_params['?']);
            unset($bind_params['?']);
        }
        $this->_prepare($sql)->_execute($bind_params);
        return $this;
    }

    /**
     * 发送一条MySQL查询
     *
     * @param string|null $sql
     * @return $this
     */
    protected function _prepare(?string $sql = null): self
    {
        $this->active();
        if ($sql) {
            if ($this->_table_prefix_replace) {
                $sql = str_replace($this->_table_prefix_search, $this->_table_prefix_replace, $sql);
            }
            $this->_last_sql = $sql;
        }
        $prepare_sql = $this->_fields_parse($this->_last_sql);
        $this->_stmt = $this->_pdo->prepare($prepare_sql);
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
            if (self::$_pdo_list && isset(self::$_pdo_list[$key]) && $pdo = self::$_pdo_list[$key]) {
                $this->_pdo = $pdo;
            } else {
                $options = [];
                if (self::Driver_Mysql == $this->_driver) {
                    $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,];
                }
                $this->_pdo            = new \PDO($dsn, $this->_username, $this->_password, $options);
                self::$_pdo_list[$key] = $this->_pdo;
            }
        }
        return $this;
    }

    /**
     * 插入或替换
     *
     * @param array $insert_data 数据
     * @return int 替换(插入)一條或多条記錄
     */
    public function insert(array $insert_data): int
    {
        $duplicate = '';
        if (($this->_duplicate_values || $this->_duplicate_extend_str) && $this->_is_replace == false) {
            if ($this->_duplicate_values) {
                $update_str = $this->_update_sql($this->_duplicate_operate ?: $this->_duplicate_values);
            } else {
                $update_str = '';
            }
            $duplicate = 'ON DUPLICATE KEY UPDATE ' . $this->_duplicate_extend_str . ' ' . implode(' , ', $update_str);
        }

        $insert_first_data = $this->_is_multiple ? array_shift($insert_data) : $insert_data;
        $insert_fields     = array_keys($insert_first_data);

        // table
        if (str_contains($this->_table, 'as')) {
            $table = strstr($this->_table, 'as', true);
        } else {
            $table = $this->_table;
        }

        $this->_prepare(($this->_is_replace ? 'REPLACE' : 'INSERT') . ' ' . $this->_option . ' INTO ' . $table . ' (`' . implode('`, `', $insert_fields) . '`) VALUES (:' . implode(', :', $insert_fields) . ') ' . $duplicate . ';');
        if ($this->_duplicate_values && $this->_is_replace == false) {
            if ($this->_is_multiple) {
                $this->_execute(array_merge($this->_duplicate_values, $insert_first_data));
                foreach ($insert_data as &$data) {
                    $this->_execute(array_merge($this->_duplicate_values, $data));
                }
            } else {
                $this->_execute(array_merge($this->_duplicate_values, $insert_first_data));
            }
        } else {
            if ($this->_is_multiple) {
                $this->_execute($insert_first_data);
                foreach ($insert_data as &$data) {
                    $this->_execute($data);
                }
            } else {
                $this->_execute($insert_first_data);
            }
        }
        return (int)$this->_pdo->lastInsertId();
    }

    /**
     * @param array $update_data
     * @param array|null $update_operate
     * @param string $where_str
     * @param array $where_params
     * @param int $limit
     * @return int
     */
    public function update(array $update_data, ?array $update_operate = null, string $where_str = '', array $where_params = [], int $limit = 1): int
    {
        $first_data = $this->_is_multiple ? array_shift($update_data) : $update_data;
        $update_str = $this->_update_sql($update_operate ?: $first_data);

        if ($where_str) {
            $this->where($where_str)->limit($limit);
        } else {
            $this->limit($limit);
        }

        // table
        if (str_contains($this->_table, 'as')) {
            $table = strstr($this->_table, 'as', true);
        } else {
            $table = $this->_table;
        }

        $this->_prepare('UPDATE ' . $this->_option . ' ' . $table . ' SET ' . implode(', ', $update_str) . ' ' . $this->_where . ' ' . $this->_limit . ' ;');

        if ($this->_is_multiple) {
            if ($where_params && is_array($where_params)) {
                if (array_keys($where_params) === range(0, count($where_params) - 1)) {
                    $i = 0;
                    $this->_execute(array_merge($this->_bind_values, $first_data, $where_params[$i]));
                    foreach ($update_data as &$data) {
                        $i++;
                        $this->_execute(array_merge($this->_bind_values, $data, $where_params[$i]));
                    }
                } else {
                    $this->_execute(array_merge($this->_bind_values, $first_data, $where_params));
                    foreach ($update_data as &$data) {
                        $this->_execute(array_merge($this->_bind_values, $data, $where_params));
                    }
                }
            } else {
                $this->_execute(array_merge($this->_bind_values, $first_data));
                foreach ($update_data as &$data) {
                    $this->_execute(array_merge($this->_bind_values, $data));
                }
            }
        } else {
            if ($where_params && is_array($where_params)) {
                if (array_keys($where_params) === range(0, count($where_params) - 1)) {
                    // echo __FILE__.':'.__LINE__."\n";
                    foreach ($where_params as &$where_params_v) {
                        $this->_execute(array_merge($this->_bind_values, $first_data, $where_params_v));
                    }
                } else {
                    // echo __FILE__.':'.__LINE__."\n";
                    $this->_execute(array_merge($this->_bind_values, $first_data, $where_params));
                }
            } else {
                $this->_execute(array_merge($this->_bind_values, $first_data));
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
                ->_execute($this->_bind_values);
        }
        return $this->_stmt->columnCount();
    }

    /**
     * @param bool $force_prepare 是否强行 prepare
     * @return array 得到一条数据数组
     */
    public function column_one(bool $force_prepare = false): array
    {
        $this->_prepare_column($force_prepare);
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
        return $this->_stmt->fetch(\PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * 得到多条數椐數組的数组
     *
     * @param bool $force_prepare 是否强行 prepare
     * @return array
     */
    public function column_all(bool $force_prepare = false): array
    {
        $this->_prepare_column($force_prepare);
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
            return $this->_stmt->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        }
    }

    /**
     * @param bool $force_prepare
     */
    protected function _prepare_column(bool $force_prepare)
    {
        if (null == $this->_stmt || $force_prepare) {
            $fields = ($this->_fields && is_array($this->_fields)) ? implode(',', $this->_fields) : '*';
            $this->_prepare('SELECT ' . $this->_distinct . ' ' . $fields . ' FROM ' . $this->_table . ' ' . $this->_join . ' ' . $this->_where . ' ' . $this->_group_get() . ' ' . $this->_having . ' ' . $this->_order_get() . ' ' . $this->_limit . ';')
                ->_execute($this->_bind_values);
        }
    }

    /**
     * @param string $field
     * @param mixed $default_value 默认值
     * @param bool $force_prepare 是否强行 prepare
     * @return mixed  直接返回对应的值
     */
    public function column_value(string $field, mixed $default_value = null, bool $force_prepare = false): mixed
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
        // table
        if (str_contains($this->_table, 'as')) {
            $table = strstr($this->_table, 'as', true);
        } else {
            $table = $this->_table;
        }

        if ($limit === 0) {
            $this->_prepare('DELETE ' . $this->_option . ' FROM ' . $table . ' ' . $this->_where . ';')
                ->_execute($this->_bind_values);
        } else {
            $this->limit($limit)
                ->_prepare('DELETE ' . $this->_option . ' FROM ' . $table . ' ' . $this->_where . ' ' . $this->_limit . ';')
                ->_execute($this->_bind_values);
        }
        return $this->_stmt->rowCount(); // 取得前一次 MySQL 操作所影响的记录行数
    }

    /**
     * 设定插入数据为替换
     *
     * @param bool $is_replace
     * @return static;
     */
    public function replace(bool $is_replace = false): static
    {
        $this->_is_replace = $is_replace;
        return $this;
    }

    /**
     * 多条数据 true:多条数据 false:单条数据
     *
     * @param bool $is_multiple
     * @return static;
     */
    public function multiple(bool $is_multiple = false): static
    {
        $this->_is_multiple = $is_multiple;
        return $this;
    }

    /**
     * 参数 install update replace
     *
     * @param string $option
     * @return static
     */
    public function option(string $option = ''): static
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * 滤掉name和id等字段都重复的记录
     *
     * @param string $distinct
     * @return static
     */
    public function distinct(string $distinct = 'distinct'): static
    {
        $this->_distinct = $distinct;
        return $this;
    }

    /**
     * 插入时已存在数据
     *
     * @param array $duplicate_values 更新内容   [字段=>操作]
     * @param array|null $duplicate_operate
     * @param string $extend_str 更新的扩展
     * @return static
     */
    public function duplicate(array $duplicate_values, ?array $duplicate_operate = null, string $extend_str = ''): static
    {
        $this->_duplicate_values     = $duplicate_values;
        $this->_duplicate_operate    = $duplicate_operate;
        $this->_duplicate_extend_str = $extend_str;
        return $this;
    }

    /**
     * 指定查询数量
     *
     * @param int $length 查询数量
     * @param int $offset 起始位置
     * @return static
     */
    public function limit(int $length, int $offset = 0): static
    {
        if (0 == $offset) {
            $this->_limit = "LIMIT {$length}";
        } else {
            $this->_limit = "LIMIT {$offset},{$length}";
        }
        return $this;
    }

    /**
     * 返回段字 - 查询
     *
     * @param string $field
     * @return static
     */
    public function field(string $field = '*'): static
    {
        $this->_fields[] = $field;
        return $this;
    }

    /**
     * 设定段字类型
     *
     * @param array $field_type
     * @param bool $clean
     * @return static
     */
    public function field_type(array $field_type, bool $clean = false): static
    {
        if ($clean) {
            $this->_fields_type = [];
        }
        foreach ($field_type as $field => $v) {
            if (is_array($v) && $v['type']) {
                $this->_fields_type[$field] = $v;
            }
        }
        return $this;
    }

    /**
     * 返回段字中的json字段 - 查询
     *
     * @param array $fields
     * @return static
     */
    public function json_field(array $fields): static
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
     * 返回关联数据索引字段 - 查询
     *
     * @param string $assoc 设定返回关联数据 assoc
     * @return static
     */
    public function assoc(string $assoc = ''): static
    {
        $this->_assoc = $assoc;
        return $this;
    }

    /**
     * 关连表
     *
     * @param string $inner_join
     * @param string $on
     * @return static
     */
    public function inner_join(string $inner_join, string $on): static
    {
        $this->_join .= 'INNER JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * 全关连
     *
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return static
     */
    public function full_join(string $inner_join, string $on, bool $is_outer = false): static
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join .= 'FULL ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return static
     */
    public function left_join(string $inner_join, string $on, bool $is_outer = false): static
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join .= 'LEFT ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * @param string $inner_join
     * @param string $on
     * @param bool $is_outer
     * @return static
     */
    public function right_join(string $inner_join, string $on, bool $is_outer = false): static
    {
        $outer       = $is_outer ? 'OUTER' : '';
        $this->_join .= 'RIGHT ' . $outer . ' JOIN ' . $inner_join . ' ON ' . $on;
        return $this;
    }

    /**
     * 条件
     *
     * @param string $where_str 条件
     * @param array $where_params 条件参数
     * @return static
     */
    public function where(string $where_str = '', array $where_params = []): static
    {
        if ($where_str) {
            if (isset($where_params['?'])) {
                $where_str = $this->quote_array($where_str, $where_params['?']);
                unset($where_params['?']);
            }
            if ($this->_where) {
                $this->_where = $this->_where . ' ' . $where_str;
            } else {
                $this->_where = 'WHERE ' . $where_str;
            }
            if ($where_params && is_array($where_params)) {
                $this->_bind_values = array_merge($this->_bind_values, $where_params);
            }
        }
        return $this;
    }

    /**
     * having条件
     *
     * @param string $having_str 条件
     * @param array $having_params 条件参数
     * @return static
     */
    public function having(string $having_str = '', array $having_params = []): static
    {
        if ($having_str) {
            if (isset($having_params['?'])) {
                $having_str = $this->quote_array($having_str, $having_params['?']);
                unset($having_params['?']);
            }
            if ($this->_having) {
                $this->_having = $this->_having . ' ' . $having_str;
            } else {
                $this->_having = 'HAVING ' . $having_str;
            }
            if ($having_params && is_array($having_params)) {
                $this->_bind_values = array_merge($this->_bind_values, $having_params);
            }
        }
        return $this;
    }

    /**
     * 指定排序
     *
     * @param string $field 排序字段
     * @param string $order 排序
     * @return static
     */
    public function order(string $field, string $order = self::Order_Desc): static
    {
        $this->_order[] = ['field' => $field, 'order' => $order];
        return $this;
    }

    /**
     * 聚合分组
     *
     * @param string $field
     * @return static
     */
    public function group(string $field): static
    {
        $this->_group[] = $field;
        return $this;
    }

    /**
     * COUNT查询
     *
     * @param string $field 字段名
     * @param string $alias SUM查询别名
     * @return static
     */
    public function count(string $field = '*', string $alias = '`count`'): static
    {
        return $this->field("COUNT({$field}) AS {$alias}");
    }

    /**
     * COUNT查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param int $default_value 默认值
     * @return int
     */
    public function count_value(string $field = '*', string $alias = '`count`', int $default_value = 0): int
    {
        return (int)$this->count($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * SUM查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return static
     */
    public function sum(string $field, string $alias = '`sum`'): static
    {
        return $this->field("SUM({$field}) AS {$alias}");
    }

    /**
     * SUM查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param float $default_value 默认值
     * @return float|int
     */
    public function sum_value(string $field, string $alias = '`sum`', float $default_value = 0): float|int
    {
        return (float)$this->sum($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * MIN查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return static
     */
    public function min(string $field, string $alias = '`min`'): static
    {
        return $this->field("MIN({$field}) AS {$alias}");
    }

    /**
     * MIN查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param float $default_value 默认值
     * @return float|int
     */
    public function min_value(string $field, string $alias = '`min`', float $default_value = 0): float|int
    {
        return $this->min($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * MAX查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return static
     */
    public function max(string $field, string $alias = '`max`'): static
    {
        return $this->field("MAX({$field}) AS {$alias}");
    }

    /**
     * MAX查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param float $default_value 默认值
     * @return float|int
     */
    public function max_value(string $field, string $alias = '`max`', float $default_value = 0): float|int
    {
        return $this->max($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * AVG查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @return static
     */
    public function avg(string $field, string $alias = '`avg`'): static
    {
        return $this->field("AVG({$field}) AS {$alias}");
    }

    /**
     * AVG查询
     *
     * @param string $field 字段名
     * @param string $alias 查询别名
     * @param float $default_value 默认值
     * @return float|int
     */
    public function avg_value(string $field, string $alias = '`avg`', float $default_value = 0): float|int
    {
        return $this->avg($field, $alias)->column_value($alias, $default_value);
    }

    /**
     * 返回查询次数
     *
     * @return int
     */
    public function query_times(): int
    {
        return $this->_query_times;
    }

    /**
     * 最后一次插入的自增ID
     *
     * @return int
     */
    public function insert_id(): int
    {
        return (int)$this->_pdo->lastInsertId();
    }

    /**
     * 最后一次更新影响的行数
     *
     * @return int
     */
    public function affected(): int
    {
        return $this->_stmt->rowCount();
    }

    /**
     * 得到最后一次查询的sql
     *
     * Dump an SQL prepared command
     */
    public function dump()
    {
        if ($this->_stmt) {
            $this->_stmt->debugDumpParams();
        }
    }

    /**
     * 返回PDO
     *
     * @return \PDO 返回PDO
     */
    public function pdo(): \PDO
    {
        return $this->_pdo;
    }

    /**
     * 执行数据库事务
     *
     * @param callable $callback 数据操作方法回调
     * @return mixed
     * @throws Exception
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->trans_begin();
        try {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback($this);
            }
            $this->trans_commit();
            return $result;
        } catch (Exception | Throwable $e) {
            $this->trans_rollback();
            throw $e;
        }
    }

    /**
     * 开启事务
     *
     * @return bool
     */
    public function trans_begin(): bool
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function trans_commit(): bool
    {
        return $this->_pdo->commit();
    }

    /**
     * 关闭事务
     *
     * @return bool
     */
    public function trans_rollback(): bool
    {
        return $this->_pdo->rollBack();
    }

    /**
     * 根据结果提交滚回事务
     *
     * @param bool $res
     * @return bool
     */
    public function trans_check(bool $res): bool
    {
        if ($res) {
            return $this->trans_commit();
        } else {
            return $this->trans_rollback();
        }
    }

    /**
     * 返回PDO
     *
     * @return PDOStatement 返回PDOStatement
     */
    public function stmt(): PDOStatement
    {
        return $this->_stmt;
    }

    /**
     * 是否连接成功
     *
     * @return bool
     */
    public function is_connect(): bool
    {
        return (bool)$this->_pdo;
    }


    /**
     * 捡查指定字段数据是否存在
     *
     * @param string $field 字段
     * @param mixed $value 值
     * @param int $param 值数据类型 PDO::PARAM_INT
     * @return bool
     */
    public function exists(string $field, mixed $value, int $param = \PDO::PARAM_STR): bool
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
     *
     * @param array|string $data
     * @param int $type
     * @return string
     */
    public function quote(array|string $data, int $type = \PDO::PARAM_STR): string
    {
        $this->active();
        if (is_array($data)) {
            $rs = [];
            foreach ($data as $value) {
                $rs[] = $this->_pdo->quote((string)$value, $type);
            }
            return implode(',', $rs);
        } else {
            return $this->_pdo->quote($data, $type);
        }
    }

    /**
     * 为 SQL 查询里的字符串添加引号(数组)
     *
     * @param string $sql
     * @param array|string $data
     * @return string|null
     */
    public function quote_array(string $sql, array|string $data = []): string|null
    {
        if (str_contains($sql, 'i:?')) {
            $data2 = [];
            foreach ($data as $value) {
                $value         = (float)$value;
                $data2[$value] = $value;
            }
            return str_replace('i:?', implode(',', array_values($data2)), $sql);
        } elseif (str_contains($sql, '?')) {
            return str_replace(['s:?', ':?', '?'], $this->quote($data), $sql);
        }
        return $sql;
    }

    /** order */
    protected function _order_get(): string
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
    protected function _group_get(): string
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
    protected function _types2param(string $types = ''): int
    {
        return match ($types) {
            db::Int, 'i' => \PDO::PARAM_INT,
            db::Bool, 'bool' => \PDO::PARAM_BOOL,
            'b' => \PDO::PARAM_LOB,
            'null' => \PDO::PARAM_NULL,
            // db::Float => \PDO::PARAM_STR,
            default => \PDO::PARAM_STR,
        };
    }

    /**
     * @param int $param
     * @return string
     */
    protected function _param2types(int $param = \PDO::PARAM_STR): string
    {
        return match ($param) {
            \PDO::PARAM_INT => 'i',
            // \PDO::PARAM_STR => 's',
            \PDO::PARAM_LOB => 'b',
            \PDO::PARAM_NULL => 'null',
            \PDO::PARAM_BOOL => 'bool',
            default => '',
        };
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function _fields_parse(string $sql): string
    {
        $splits  = preg_split('/((bool)?f?i?s?\-?\d*:[A-Za-z0-9_]+)/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $result  = [];
        $search  = [];
        $replace = [];
        foreach ($splits as $v) {
            if (str_contains($v, ':')) {
                list($type, $field) = explode(':', $v);
                if ($field && empty($result[$field])) {
                    if (str_contains($type, '-')) {
                        list($type2, $length) = explode('-', $type);
                        $type2       = $type2 ?: ($this->_fields_type[$field]['type'] ?? '');
                        $type_param2 = $this->_types2param($type2);
                        if (\PDO::PARAM_STR == $type_param2) {
                            $result[$field] = [
                                'type'       => $type2,
                                'type_param' => $type_param2,
                                'length'     => (int)$length
                            ];
                        }
                    } else {
                        $type           = $type ?: ($this->_fields_type[$field]['type'] ?? '');
                        $result[$field] = [
                            'type'       => $type,
                            'type_param' => $this->_types2param($type)
                        ];
                    }
                    $search[]  = $v;
                    $replace[] = ":{$field}";
                }
            }
        }
        foreach ($result as $field => $value) {
            if (isset($this->_fields_type[$field])) {
                $value2 = $this->_fields_type[$field];
                if ($value2 && is_array($value2)) {
                    $type_param2 = $this->_types2param($value2['type']);
                    if ($type_param2 == $value['type_param']) {
                        $this->_bind_fields[] = $field;
                    } elseif (empty($value['type'])) {
                        $this->_bind_fields[] = $field;
                    } elseif (empty($value2['type'])) {
                        $this->_fields_type[$field] = array_merge($value2, $value);
                        $this->_bind_fields[]       = $field;
                    } else {
                        error_php("SQL:Type error Table:{$this->_table} \$value2:" . json_encode_unescaped($value2) . " \$fields[{$field}] {$type_param2}!={$value['type_param']}", '', 'mysql');
                    }
                }
            } else {
                $this->_fields_type[$field] = $value;
                $this->_bind_fields[]       = $field;
            }
        }
        return str_replace($search, $replace, $sql);
    }

    /**
     * @param array $operate
     * @return array
     */
    protected function _update_sql(array $operate = []): array
    {
        $update = [];
        foreach ($operate as $field => $val) {
            if ($val === self::Update_Add) {
                $update[] = "`$field` = `{$field}` + :{$field} ";
            } elseif ($val === self::Update_Cut) {
                $update[] = "`$field` = `{$field}` - :{$field} ";
            } else {
                $update[] = "`{$field}` = :{$field} ";
            }
        }
        return $update;
    }

    /**
     * 执行
     *
     * @param array|null $data
     */
    protected function _execute(?array $data = null)
    {
        $data = $data ?? $this->_bind_values;
        if ($data) {
            foreach ($this->_bind_fields as $field) {
                $types = $this->_fields_type[$field];
                if ($types) {
                    $type_param = $types['type_param'] ?? $this->_types2param($types['type']);
                    if (null === $data[$field] ?? null) {
                        if (\PDO::PARAM_BOOL === $type_param) {
                            $this->_stmt->bindValue(':' . $field, false, \PDO::PARAM_BOOL);
                        } elseif (\PDO::PARAM_INT === $type_param) {
                            $this->_stmt->bindValue(':' . $field, 0, \PDO::PARAM_INT);
                        } else {
                            $this->_stmt->bindValue(':' . $field, null, \PDO::PARAM_NULL);
                        }
                    } else if (\PDO::PARAM_STR == $type_param && isset($types['length']) && is_integer($types['length']) && $types['length'] > 1) {
                        $this->_stmt->bindParam(':' . $field, $data[$field], \PDO::PARAM_STR, $types['length']);
                    } else {
                        $this->_stmt->bindValue(':' . $field, $data[$field], $type_param);
                    }
                } else {
                    $this->_stmt->debugDumpParams();
                    error_php("SQL:Can't find Table:{$this->_table} fields:{$field}  type:null ", '', 'mysql');
                }
            }
        }

        try {
            $this->_stmt->execute();
        } catch (Exception $e) {
            $this->_stmt->debugDumpParams();
            error_php("Sql Error:" . $e->getMessage() .
                "\n\tlast_sql :" . $this->_last_sql . "\n" .
                "\tbind_values:" . json_encode_unescaped($data) . "\n" .
                "\tbind_fields:" . json_encode_unescaped($this->_bind_fields) . "\n", '', 'mysql');
        }
    }

    /**
     * 清理
     */
    protected function _clean()
    {
        $this->_stmt = null;

        $this->_option      = '';
        $this->_distinct    = '';
        $this->_fields      = [];
        $this->_fields_json = [];
        //$this->_fields_type = [];
        $this->_order       = [];
        $this->_group       = [];
        $this->_limit       = '';
        $this->_where       = '';
        $this->_having      = '';
        $this->_assoc       = '';
        $this->_bind_fields = [];
        $this->_bind_values = [];

        $this->_duplicate_values     = [];
        $this->_duplicate_operate    = [];
        $this->_duplicate_extend_str = '';
        $this->_join                 = '';

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
