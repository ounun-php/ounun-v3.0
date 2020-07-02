<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\apps;

use \ounun\db\pdo;

abstract class logic
{
    /** @var array 错误提示信息 */
    const Error_Msg = [];

    /** @var self 实例 */
    protected static $_instance;

    /** @var model 数据模型 */
    protected model $_model;

    /**
     * @param pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(?model $model = null): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static($model);
        }
        return static::$_instance;
    }
    
    /**
     * cms constructor.
     * @param pdo $db
     */
    public function __construct(?model $model = null)
    {
        if ($model) {
            $this->_model = $model;
        }
        // 控制器初始化
        $this->_initialize();
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

    /**
     * 错误代码
     * @param int $error_code
     * @param null $data
     * @param array $extend
     * @return array
     */
    protected function error($error_code = 1, $data = null, $extend = [])
    {
        if (static::Error_Msg && isset(static::Error_Msg[$error_code])) {
            $msg = static::Error_Msg[$error_code] . "(code:{$error_code})";
        } else {
            $msg = "错误代码暂没定义(code:{$error_code})";
        }
        return error($msg, $error_code, $data, $extend);
    }
}
