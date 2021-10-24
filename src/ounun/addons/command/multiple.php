<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

declare (strict_types = 1);
namespace ounun\addons\command;


use ounun\addons\command;
use ounun\addons\console;

abstract class multiple extends command
{
    /** @var self logic */
    public static $logic;

    /** @var int 间隔(秒,默认5秒) */
    protected int $_time_argc_sleep = 5;
    /** @var int 寿命(秒,默认300秒) */
    protected int $_time_argc_lifecycle = 59;

    /**
     * 配置指令
     */
    public function configure()
    {
        $this->help_paras = ' [间隔(秒,默认5秒)] [寿命(秒,默认59秒)] [参数...]';

        $this->description = '批量执行任务';
        $this->help        = "批量执行任务，把数据库现有任务循环检查，并执行";  // 运行命令时使用 "--help" 选项时的完整命令描述
        // echo "\$this->help_paras:".$this->help_paras."\n";
        parent::configure();
    }

    /**
     * 执行入口
     * @param array $argc_input
     * @return int
     */
    public function execute(array $argc_input): int
    {
        // 设定参数
        $input_len = 0;
        if ($argc_input) {
            $input_len = count($argc_input);
        }
        $this->_time_argc_sleep     = ($input_len >= 1) ? ((int)array_shift($argc_input)) : 5;  // 间隔(秒,默认5秒)
        $this->_time_argc_lifecycle = ($input_len >= 2) ? ((int)array_shift($argc_input)) : 59; // 寿命(秒,默认300秒)

        // 运行次数
        $run_number = 0;

        // 每次只执行一次任务
        do {
            $run_number++;
            $run_time_start_do = microtime(true);
            $run_time          = $this->_time + $run_time_start_do;
            console::echo("Execute multiple  间隔:" . str_pad($this->_time_argc_sleep . "秒", 8) . "  " .
                "已运行:" . str_pad($run_time . "秒", 32) . "  " .
                "已执行:" . str_pad($run_number . "次", 8) . "  " .
                "存活总时长:" . str_pad($this->_time_argc_lifecycle . "秒", 8) . ' ---------- ',
                command_c::Color_Light_Red, __FILE__, __LINE__);

            $do = $this->_execute_inside($argc_input);
            if ($do) {
                return $do;
            }
            if (0 == $do) {
                $sleep_u = $this->_time_argc_sleep - (microtime(true) - $run_time_start_do);
                if ($sleep_u > 0) {
                    usleep($sleep_u * 1000000);
                }
            }
            $run_time = $this->_time + microtime(true);
        } while ($run_time < $this->_time_argc_lifecycle && 0 == $do);

        // 时间统计
        $this->stop();
        console::echo("运行时间: " . $this->time(), command_c::Color_Green);
        return 0;
    }

    /**
     * @param array $argc_input
     * @return int
     */
    protected function _execute_inside(array $argc_input): int
    {
        console::echo(__METHOD__ . " \$input:" . json_encode_unescaped($argc_input) . "\n", command_c::Color_Blue);
        return 0;
    }
}
