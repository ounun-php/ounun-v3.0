<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

declare (strict_types = 1);
namespace ounun\addons\command;

use ounun\addons\command;
use ounun\addons\console;

class test extends command
{
    /** @var self logic */
    public static $logic;

    /** @inheritDoc */
    protected function _initialize(){}

    /** @inheritDoc */
    public function configure()
    {
        // 命令的名字（"./ounun" 后面的部分）
        $this->name = 'test';
        // 运行 "php think list" 时的简短描述
        $this->description = '单元测试 Test phpunit';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Test phpunit instructions";
        // parent
        parent::configure();
    }

    /** @inheritDoc */
    public function _execute_inside(array $argc_input): int
    {
        // echo
        console::echo("---> " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' \$argc_input:' . json_encode_unescaped($argc_input), command_c::Color_Red);
        return 0;
    }
}
