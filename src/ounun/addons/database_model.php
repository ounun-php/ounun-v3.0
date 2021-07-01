<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\addons;

use ounun\db\pdo;
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
    /** @var string 表名 */
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
     * @param string|null $table 表名
     * @return self
     */
    public function table(?string $table = null): self
    {
        if (empty($table)) {
            if (empty($this->table)) {
                error_php(get_class($this) . '::$this->table is empty');
            }
            $table = $this->table;
        }
        return parent::table($table);
    }

    /**
     * @param string $field
     * @param mixed $default_value 默认值
     * @param bool $force_prepare 是否强行 prepare
     * @return mixed  直接返回对应的值
     */
    public function column_value_json(string $field, mixed $default_value = null, bool $force_prepare = false): mixed
    {
        return $this->json_field([$field])->column_value($field, $default_value, $force_prepare);
    }

    /**
     * 分页
     *
     * @param callable|null $fn_data_list 获取数据列表
     * @param callable|null $fn_total 获取总数
     * @param array|null $http_request_gets 请求如$_GET参数
     * @param array|null $paging_config 分页参数
     * @param bool $is_end_index 是否倒序 false:正序 true:倒序
     * @param string $page_title 分页标题
     * @return array
     */
    public function paging(?callable $fn_data_list, ?callable $fn_total, ?array $http_request_gets = null, ?array $paging_config = null, bool $is_end_index = true, string $page_title = ''): array
    {
        $http_request_gets = $http_request_gets ?? $_GET;
        $paging_config     = $paging_config ?? [];
        $url               = url_build_query(url_original(), $http_request_gets, ['page' => '{page}']);
        $pg                = new simple($url, $paging_config);
        $ps                = $pg->fn_total_set($fn_total)->initialize((int)($http_request_gets['page'] ?? 0), $page_title, $is_end_index);
        return [$ps, $fn_data_list($pg)];
    }

    /**
     * 简单 分页
     *
     * @param string $where_str 查询条件
     * @param array $where_paras 查询条件参数
     * @param array $orders 排序orders
     * @param string|null $table 表名
     * @param array $http_request_gets 请求如$_GET参数
     * @param array $paging_config 分页参数
     * @param bool $is_end_index 是否倒序 false:正序 true:倒序
     * @param string $page_title 分页标题
     * @return array
     */
    public function paging_simple(string $where_str = '', array $where_paras = [], array $orders = [], ?string $table = null, ?array $http_request_gets = null, ?array $paging_config = null, bool $is_end_index = true, string $page_title = ''): array
    {
        $table        ??= $this->table;
        $fn_total     = function () use ($table, $where_str, $where_paras) {
            return $this->table($table)->where($where_str, $where_paras)->count_value();
        };
        $fn_data_list = function (simple $pg) use ($orders, $table, $where_str, $where_paras) {
            $this->table($table)->where($where_str, $where_paras)->limit($pg->limit_length(), $pg->limit_offset());
            foreach ($orders as $field => $order) {
                $this->order($field, $order);
            }
            return $this->column_all();
        };
        return $this->paging($fn_data_list, $fn_total, $http_request_gets, $paging_config, $is_end_index, $page_title);
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
    public function data_get(string $name, mixed $default_value = null): mixed
    {
        return $this->_data[$name]??$default_value;
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
