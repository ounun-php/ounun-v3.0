<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

declare (strict_types = 1);
namespace ounun\addons\command;

use ounun\addons\command;
use ounun\addons\console;

class help extends command
{
    /** @var self logic */
    public static $logic;

    /** @inheritDoc */
    protected function _initialize(){}

    /** @inheritDoc */
    public function configure()
    {
        // 命令的名字（"./ounun" 后面的部分）
        $this->name = 'help';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Display this help message';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Displays help for a command";
        // parent
        parent::configure();
    }

    /** @inheritDoc */
    public function execute(array $argc_input) : int
    {
        console::echo("\n可执行命令:", command_c::Color_Purple);
        $commands = [];
        foreach (\ounun::$addon_route as $addon){
            $command = '\\addons\\' . ($addon['apps'])::Addon_Tag . '\\command';
            $commands[$command] = $command;
        }

        foreach (array_merge(console::$commands,$commands) as $command) {
            if(is_subclass_of($command, command::class)){
                /** @var command $c */
                $c = new $command();
                console::echo('./ounun '.$c->name, command_c::Color_None, '',0,0," \t");
                console::echo($c->description, command_c::Color_Green);
            }
        }

        console::echo('帮助', command_c::Color_Purple, '',0,0,' ');
        console::echo('./ounun 命令或addons名 --help', command_c::Color_Light_Purple,'',0,0, '  ');
        console::echo('显示对应"命令"提示', command_c::Color_Purple, '',0,0,"\n\n");
        return 0;
    }
}
