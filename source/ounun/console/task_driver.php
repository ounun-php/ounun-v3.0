<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\console;

use ounun\console;
use ounun\db\pdo;

abstract class task_driver
{
    /** @var pdo */
    public static pdo $db;

    /** @var console 控制台 */
    public console $console;

    /** @var string 命令的名字（"ounun" 后面的部分） */
    public string $name;

    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public string $help;

    /** @var string 运行 "php ./ounun list" 时的简短描述 */
    public string $description;

    /**
     * task constructor.
     * @param console $console
     */
    public function __construct(console $console)
    {
        $this->console = $console;
        $this->configure();
    }

    /**
     * 是否有效
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }

    /**
     * @param array $argv
     */
    public function help(array $argv)
    {
        console::echo("命令:", console::Color_Purple, '', 0, 0, '');
        console::echo("({$this->description})");
        console::echo('./ounun ' . $this->name . ' [参数...]', console::Color_Blue);
        console::echo($this->help, console::Color_Purple);
    }

    /**
     * 配置指令
     */
    abstract public function configure();

    /**
     * 执行指令
     * @param array $argc_input
     * @return null|int
     * @throws \LogicException
     */
    abstract public function execute(array $argc_input);

}
