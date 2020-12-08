<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;

use \ounun\db\pdo;
use ounun\page\base;



/**
 * 数据库模型
 * Class database_model
 * @property database_model $_instance static protected
 *
 * @method static database_model i(string $tag = '', array $config = []);
 *
 * @package ounun\addons
 */
class database_model extends pdo
{
    /** @var self 数据库实例 */
    protected static $_instance;

    /** @var string */
    public string $table;

    /** @var array 数据 */
    protected array $_data = [];

    /**
     * 创建MYSQL类
     *
     * pdo constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // 父级构建
        parent::__construct($config);
        // 控制器初始化
        $this->_initialize();
    }

    /** 初始化 */
    protected function _initialize(){}
    //abstract protected function _initialize();

    /**
     * @param string $table 表名
     * @return pdo
     */
    public function table(string $table = ''): pdo
    {
        if (empty($table) && $this->table) {
            $table = $this->table;
        }
        return parent::table($table);
    }

    /**
     * @param string $field
     * @param mixed $default_value 默认值
     * @param bool $force_prepare 是否强行 prepare
     * @return mixed|null  直接返回对应的值
     */
    public function column_value_json(string $field, $default_value = null, bool $force_prepare = false)
    {
        return $this->json_field([$field])->column_value($field, $default_value, $force_prepare);
    }

    /**
     * 分页
     *
     * @param string $where_str
     * @param array $where_bind
     * @param string $fields
     * @param array $orders
     * @param array $page_gets
     * @param array $page_config
     * @param string|null $table
     * @return array
     */
    public function pagination(string $where_str, array $where_bind, string $fields = '', array $orders = [], array $page_gets = [], array $page_config = [], ?string $table = null): array
    {
        $table ??= $this->table;
        $page  = (isset($page_gets['page']) && (int)$page_gets['page'] > 1) ? (int)$page_gets['page'] : 1;
        $url   = url_build_query(url_original(), $page_gets, ['page' => '{page}']);

        $where = ['str' => $where_str, 'bind' => $where_bind,];
        $pg    = new base($this, $table, $url, $where, $page_config);
        $ps    = $pg->initialize($page);

        $this->table($table)
            ->field($fields)
            ->where($where_str, $where_bind)
            ->limit($pg->limit_length(), $pg->limit_offset());
        if ($orders && is_array($orders)) {
            foreach ($orders as $field => $order) {
                $this->order($field, $order);
            }
        }
        return [$ps, $this->column_all()];
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed $value
     */
    public function data_set(string $name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * get
     *
     * @param string $name
     * @param mixed $default_value
     * @return mixed
     */
    public function data_get(string $name, $default_value = null)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : $default_value;
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->data_set($name, $value);
    }

    /**
     * get
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->data_get($name, null);
    }

    /**
     * isset
     *
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->_data[$name]);
    }

    /**
     * unset
     *
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
