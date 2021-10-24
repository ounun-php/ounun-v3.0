<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

declare (strict_types=1);

namespace ounun\addons\command;

use ounun\addons\command;
use ounun\addons\console;

class help extends command
{
    /** @var self logic */
    public static $logic;

    /** @inheritDoc */
    protected function _initialize()
    {
    }

    /** @inheritDoc */
    public function configure()
    {
        // 命令的名字（"./ounun" 后面的部分）
        $this->name = 'help';
        // 运行 "php think list" 时的简短描述
        $this->description = '显示帮助信息';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Displays help for a command";
        // parent
        parent::configure();
    }

    /** @inheritDoc */
    public function execute(array $argc_input): int
    {
        console::echo("\n可执行命令:", command_c::Color_Purple);
        $commands = [];
        foreach (\ounun::$addon as $apps => $addon) {
            $command = '\\addons\\' . ($apps)::Addon_Tag . '\\command';
            $is_have = \ounun::load_class_file_exists($command);
            // echo "\$command:{$command}  \$is_have:".($is_have?'1':'0')."\n";
            if ($is_have) {
                $commands[$command] = $command;
            }
        }

//        print_r(['console::$commands' => console::$commands]);

        $rs      = [];
        $len_max = 0;
        foreach (array_merge(console::$commands, $commands) as $key => $command) {
            $command = $command[0] === '\\' ? $command : '\\' . $command;
//            echo "\$key:{$key} \$command:{$command}\n";
            if (is_subclass_of($command, command::class)) {
                /** @var command $c */
                $c        = new $command();
                $len_name = strlen($c->name);
                $len_max  = $len_max > $len_name ? $len_max : $len_name;
                $rs[]     = ['name' => $c->name, 'description' => $c->description];
            }
        }

//        print_r(['$rs' => $rs,'$len_max'=>$len_max]);

        foreach ($rs as $v) {
            console::echo('./ounun ' . str_pad($v['name'], $len_max), command_c::Color_None, '', 0, 0, " \t");
            console::echo($v['description'], command_c::Color_Green);
        }

        console::echo('帮助', command_c::Color_Purple, '', 0, 0, ' ');
        console::echo('./ounun 命令或addons名 --help', command_c::Color_Light_Purple, '', 0, 0, '  ');
        console::echo('显示对应"命令"提示', command_c::Color_Purple, '', 0, 0, "\n\n");
        return 0;
    }
}
