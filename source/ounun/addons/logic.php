<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


abstract class logic
{
    /** @var array 错误提示信息 */
    const Error_Msg = [];

    /** @var self 实例 */
    protected static $_instance;

    /** @var model 数据模型 */
    protected $_model;

    /**
     * 业务逻辑
     *
     * @param model|null $model
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
     * @param model|null $model
     */
    public function __construct(?model $model = null)
    {
        $this->model_set($model);
        $this->_initialize(); // 控制器初始化
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

    /**
     * 数据模型set
     *
     * @param $model
     */
    public function model_set($model)
    {
        if ($model && is_subclass_of($model, model::class)) {
            $this->_model = $model;
            $this->_model->logic_set($this);
        }
    }

    /**
     * get数据模型
     *
     * @return model
     */
    public function model_get()
    {
        return $this->_model;
    }

    /**
     * 错误代码
     *
     * @param int $error_code
     * @param mixed $data
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
