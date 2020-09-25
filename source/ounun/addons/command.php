<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


class command
{
    /** @var string 命令的名字（"ounun" 后面的部分） */
    public string $name = '';
    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public string $help = '';
    /** @var string 运行 "php ./ounun list" 时的简短描述 */
    public string $description = '';
    /** @var string  脚本版本 */
    public string $script_version = '';

    /** @var bool 是否有效 */
    protected bool $_is_enabled = true;
    /** @var bool 是否正在运行 */
    protected bool $_is_run = false;
    /** @var float 执行时间 */
    protected float $_time_run = 0;

    /**
     * task constructor.
     */
    public function __construct()
    {
        $this->configure();
    }

    /**
     * 有效状态 true:有效   false:关闭
     * @return bool
     */
    public function is_enabled()
    {
        return $this->_is_enabled;
    }

    /**
     * 运行状态 true:运行中  false:关闭
     * @return bool
     */
    public function is_run()
    {
        return $this->_is_run;
    }

    /**
     * 帮
     * @param array $argv
     */
    public function help(array $argv)
    {
        console::echo("命令:", command_c::Color_Purple, '', 0, 0, '');
        console::echo("({$this->description})");
        console::echo('./ounun ' . $this->name . ' [参数...]', command_c::Color_Blue);
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

        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "命令\n" .
            "./ounun {$this->name} [间隔(秒,默认5秒)] [寿命(秒,默认300秒)] [任务ID] [网站tag]\n";
    }

    /**
     * 执行入口
     * @param array $argc_input
     * @return int
     */
    public function execute(array $argc_input)
    {
        $start         = microtime(true);
        $this->_is_run = true;
        $this->_execute_inside($argc_input);
        $this->_is_run   = false;
        $this->_time_run = microtime(true) - $start;

        echo "time run: " . $this->_time_run . " \n";
        return 0;
    }


    /**
     * @param array $argc_input
     * @return int
     */
    protected function _execute_inside(array $argc_input)
    {
        usleep(100);
        echo __METHOD__ . " \$input:" . json_encode_unescaped($argc_input) . "\n";
        return 0;
    }
}
