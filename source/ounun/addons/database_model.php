<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;

use \ounun\db\pdo;
use ounun\page\simple;

/**
 * 数据库模型
 * Class database_model
 * @property database_model $_instance static protected
 *
 * @method static database_model i(string $tag = '', array $config = []);
 *
 * @package ounun\addons
 */
abstract class database_model extends pdo
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
    abstract protected function _initialize();

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
     * @param callable|null $fn_ret
     * @param callable|null $fn_total
     * @param array $page_gets
     * @param array $page_config
     * @param bool $end_index
     * @param string $page_title
     * @return array
     */
    public function paging(callable $fn_ret, callable $fn_total, array $page_gets = [], array $page_config = [], bool $end_index = true, string $page_title = ''): array
    {
        $url = url_build_query(url_original(), $page_gets, ['page' => '{page}']);
        $pg  = new simple($url, $page_config);
        $ps  = $pg->fn_total_set($fn_total)->initialize((int)$page_gets['page'], $page_title, $end_index);
        return [$ps, $fn_ret($pg)];
    }


    /**
     * 简单 分页
     *
     * @param string $where_str
     * @param array $where_paras
     * @param array $orders
     * @param string|null $table
     * @param array $page_gets
     * @param array $page_config
     * @param bool $end_index
     * @param string $page_title
     * @return array
     */
    public function paging_simple(string $where_str = '', array $where_paras = [], array $orders = [], ?string $table = null, array $page_gets = [], array $page_config = [], bool $end_index = true, string $page_title = ''): array
    {
        $table    ??= $this->table;
        $fn_total = function () use ($table, $where_str, $where_paras) {
            return $this->table($table)->where($where_str, $where_paras)->count_value();
        };
        $fn_ret   = function (simple $pg) use ($orders, $table, $where_str, $where_paras) {
            $this->table($table)->where($where_str, $where_paras)
                ->limit($pg->limit_length(), $pg->limit_offset());
            foreach ($orders as $field => $order) {
                $this->order($field, $order);
            }
            return $this->column_all();
        };
        return $this->paging($fn_ret, $fn_total, $page_gets, $page_config, $end_index, $page_title);
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
