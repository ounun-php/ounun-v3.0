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
    protected static $_instance;

    /**
     * @param \ounun\db\pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(\ounun\db\pdo $db = null): self
    {
        if (empty(static::$_instance)) {
            if (empty($db)) {
                $db = \v::db_v_get();
            }
            static::$_instance = new static($db);
        }
        return static::$_instance;
    }

    /** @var array 数据 */
    protected $_data = [];

    /** @var \ounun\db\pdo */
    protected $_db;
    /** @var string  */
    protected $_table        = '';

    /**
     * cms constructor.
     * @param \ounun\db\pdo $db
     */
    public function __construct(\ounun\db\pdo $db = null)
    {
        if ($db) {
            $this->_db = $db;
        }
        static::$_instance = $this;
        // 控制器初始化
        $this->_initialize();
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

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
