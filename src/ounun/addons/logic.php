<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


/**
 * Class logic
 *
 * @package ounun\addons
 */
abstract class logic
{
    /** @var array 错误提示信息 */
    const Error_Msg = [];

    /** @var self 实例 */
    protected static $_instance;

    /** @var database_model 数据模型 */
    protected $_db;

    /**
     * 业务逻辑
     *
     * @param database_model|null $db
     * @return self 返回数据库连接对像
     */
    public static function i(?database_model $db = null): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static($db);
        }
        return static::$_instance;
    }

    /**
     * cms constructor.
     * @param database_model|null $db
     */
    public function __construct(?database_model $db = null)
    {
        if ($db) {
            $this->db_set($db);
        }
        $this->_initialize(); // 控制器初始化
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

    /**
     * 数据模型set
     *
     * @param database_model $db
     */
    public function db_set(?database_model $db = null)
    {
        if (empty($db)) {
            return;
        }
        if (is_subclass_of($db, database_model::class)) {
            $this->_db = $db;
        } else {
            error_php("error \ounun::database_default_get():" . \ounun::database_default_get() . "  \$db:" . var_export($db, true));
        }
    }

    /**
     * get数据模型
     *
     * @return database_model
     */
    public function db_get(): database_model
    {
        return $this->_db;
    }

    /**
     * 删除 - 操作动作
     *
     * @param array $data 数据
     * @param string|null $type 类型
     * @return array
     */
    public function data_del(array $data = [], ?string $type = null): array
    {
        return error('TODO: Implement data_del() method.');
    }

    /**
     * 添加数据 - 操作动作
     *
     * @param array $data
     * @param string|null $type 类型
     * @return array
     */
    public function data_set(array $data = [], ?string $type = null): array
    {
        return error('TODO: Implement data_set() method.');
    }

    /**
     * 获取数据 - 操作动作
     *
     * @param array $data
     * @param string|null $type 类型
     * @return array
     */
    public function data_get(array $data = [], ?string $type = null): array
    {
        return error('TODO: Implement data_get() method.');
    }

    /**
     * 编辑 - 操作动作
     *
     * @param array $data
     * @param string|null $type 类型
     * @return array
     */
//    public function data_editor(array $data = [], ?string $type = null): array
//    {
//        return error('TODO: Implement data_editor() method.');
//    }

    /**
     * 错误代码
     *
     * @param int $error_code
     * @param mixed $data
     * @param array $extend
     * @return array
     */
    protected function error($error_code = 1, $data = null, $extend = []): array
    {
        if (static::Error_Msg && isset(static::Error_Msg[$error_code])) {
            $msg = static::Error_Msg[$error_code] . "(code:{$error_code})";
        } else {
            $msg = "错误代码暂没定义(code:{$error_code})";
        }
        return error($msg, $error_code, $data, $extend);
    }
}
