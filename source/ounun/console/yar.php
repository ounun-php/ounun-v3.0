<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\console;

/**
 * Yar控制器类
 * Class yar
 * @package ounun\controller
 */
abstract class yar
{
    /**
     * 构造函数
     * Yar constructor.
     * @throws
     */
    public function __construct()
    {
        //控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }

        //判断扩展是否存在
        if (!extension_loaded('yar')) {
            throw new \Exception('not support yar');
        }

        //实例化Yar_Server
        $server = new \Yar_Server($this);
        // 启动server
        $server->handle();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @param string $method 方法名
     * @param array  $args   参数
     */
    public function __call($method, $args)
    {

    }
}
