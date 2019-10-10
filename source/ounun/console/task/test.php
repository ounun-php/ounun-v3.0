<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\cmd\task;

use ounun\console\task;

class test extends task
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name = 'test';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Test phpuint';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Test phpuint instructions";
    }

    public function execute(array $argc_input)
    {
        // 打包下载
        echo "\n ---> " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' ok' . "\n";
    }
}
