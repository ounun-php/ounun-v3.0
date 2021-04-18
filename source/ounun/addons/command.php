<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


abstract class command
{
    /** @var logic logic */
    public static $logic;

    /** @var string 插件唯一标识 */
    public static string $addon_tag = '';

    /** @var string 插件展示子类 */
    public static string $addon_view = '';

    /** @var string 命令的名字（"ounun" 后面的部分） */
    public string $name;

    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public string $help;

    /** @var string 运行命令时使用 "php ./ounun" 参数... */
    public string $help_paras;

    /** @var string 运行 "php ./ounun --help" 时的简短描述 */
    public string $description;

    /** @var string  脚本版本 */
    public string $script_version;


    /** @var bool 是否正在运行 */
    protected bool $_state = false;

    /** @var float 执行时间 */
    protected float $_time = 0;

    /**
     * task constructor.
     */
    public function __construct()
    {
        $this->_initialize();
        $this->configure();
    }

    /**
     * 控制器初始化
     */
    abstract protected function _initialize();

    /**
     * 运行状态 true:运行中  false:关闭
     *
     * @return bool
     */
    public function state(): bool
    {
        return $this->_state;
    }

    /**
     * 执行时间
     *
     * @return float|int
     */
    public function time()
    {
        return $this->_time;
    }

    /**
     * 开始
     */
    public function start()
    {
        $this->_time  = 0 - microtime(true);
        $this->_state = true;
    }

    /**
     * 停止
     */
    public function stop()
    {
        $this->_state = false;
        $this->_time  += microtime(true);
    }

    /**
     * 帮助
     *
     * @param array $argv  参数
     */
    public function help(array $argv)
    {
        console::echo("命令:", command_c::Color_Purple, '', 0, 0, '');
        console::echo("({$this->description})");
        console::echo('./ounun ' . $this->name . $this->help_paras, command_c::Color_Blue);
        console::echo($this->help, command_c::Color_Purple);
    }

    /**
     * 配置指令
     */
    public function configure()
    {
        // 任务名称
        if (empty($this->name)) {
            $this->name = 'command';
        }
        // 脚本版本
        if (empty($this->script_version)) {
            $this->script_version = '1.0.1';
        }
        // 运行 "php think list" 时的简短描述
        if (empty($this->description)) {
            $this->description = '命令进程';
        }

        // [参数...]
        if (empty($this->help_paras)) {
            $this->help_paras = ' [参数...]';
        }
        // 运行命令时使用 "--help" 选项时的完整命令描述
        if (empty($this->help)) {
            $this->help = "命令\n" .
                "./ounun {$this->name} ".$this->help_paras."\n";
        }
    }

    /**
     * 执行入口
     *
     * @param array $argc_input
     * @return int
     */
    public function execute(array $argc_input): int
    {
        $this->_execute_inside($argc_input);
        $this->stop();
        console::echo("运行时间: " . $this->time(), command_c::Color_Green);
        return 0;
    }

    /**
     * 内部执行入口
     *
     * @param array $argc_input
     * @return int
     */
    protected function _execute_inside(array $argc_input): int
    {
        usleep(100);
        console::echo(__METHOD__ . " \$input:" . json_encode_unescaped($argc_input), command_c::Color_Cyan);
        return 0;
    }
}
