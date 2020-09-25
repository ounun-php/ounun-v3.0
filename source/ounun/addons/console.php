<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


class console
{
    /** @var array 命令 */
    public static array $commands = [];

    /** @var string 命令的名字（"ounun" 后面的部分） */
    public string $name;
    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public string $version;

    /**
     * console constructor.
     *
     * @param array $commands
     * @param string $name
     * @param string $version
     */
    public function __construct(array $commands = [], string $name = 'Ounun Command', string $version = '0.1')
    {
        // name
        empty($name) || $this->name = $name;

        // version
        empty($version) || $this->version = $version;

        // add
        if (is_array($commands)) {
            foreach ($commands as $command) {
                if (class_exists($command)) {
                    if (is_subclass_of($command, command::class)) {
                        $this->add(new $command($this));  // 注册指令
                    }
                }
            }
        }
    }

    /**
     * 添加一个指令
     *
     * @param command $command 命令实例
     * @return int
     */
    public function add(command $command)
    {
        static::$commands[$command->name] = $command;
        return count(static::$commands);
    }

    /**
     * 执行
     *
     * @param array $argv
     * @return int
     */
    public function execute(array $argv)
    {
        // print_r(['$argv'=>$argv]);
        if (empty($argv) || empty($argv[1]) || '--help' == $argv[1] || '--list' == $argv[1]) {
            /** @var command $command */
            $command = static::$commands[command_c::Default_Cmd];
            $command->execute($argv);
        } else {
            /** @var command $command */
            $command = static::$commands[$argv[1]];
            if ($command) {
                if ('--help' == $argv[2]) {
                    $command->help($argv);
                } else {
                    $run_time = 0 - microtime(true);
                    $run_cmd  = str_pad($argv[1], 16);
                    static::echo("-- runing... {$run_cmd} " . date("Y-m-d H:i:s") . "             --------------------", command_c::Color_Cyan);
                    $command->execute($argv);
                    $run_time += microtime(true);
                    static::echo("-- done      {$run_cmd} " . date("Y-m-d H:i:s") . " run:" . str_pad(round($run_time, 4) . 's', 8) . "--------------------", command_c::Color_Cyan);
                }
            } else {
                static::echo("命令:{$argv[1]} 不存在!", command_c::Color_Light_Red);
                static::echo("你可以尝试下面", command_c::Color_Green);
                $command = static::$commands[command_c::Default_Cmd];
                $command->execute($argv);
            }
        }
        return 0;
    }

    /**
     * @param string $msg 信息
     * @param string $color 颜色
     * @param string $file 文件名称
     * @param int $line 行号
     * @param int $time 时间
     * @param string $end 结束
     */
    static public function echo(string $msg, string $color = '', string $file = '', int $line = 0, int $time = 0, string $end = "\n")
    {
        if ($file) {
            $file = basename($file);
            $file = "{$file}:{$line} ";
        } else {
            $file = '';
        }
        if ($time) {
            $file = command_c::Color_Cyan . '[' . date("Y-m-d H:i:s") . ']' . command_c::Color_None . $file;
        }
        if (empty($color)) {
            echo $file . $msg . $end;
        } else {
            echo $file . $color . $msg . command_c::Color_None . $end;
        }
    }


    /**
     * 打印数组
     * @param mixed $array 数组
     * @param string $tab tab
     * @param int $depth0 深度
     * @param string $file 文件名称
     * @param int $line 行号
     */
    static public function print_r($array, $tab = '', int $depth0 = 0, string $file = '', int $line = 0)
    {
        $depth = $depth0 % command_c::Depth_Colors_Count;
        $color = command_c::Depth_Colors[$depth];
        // echo "\$depth:{$depth} - ";
        if (is_array($array)) {
            if (empty($array)) {
                static::echo("[]", $color, $file, $line);
            } else {
                static::echo("[", $color, $file, $line);
                foreach ($array as $k => $v) {
                    static::echo("\t" . $tab . (is_numeric($k) ? $k : '"' . $k . '"'), $color, $file, $line, 0, '');
                    static::echo(' => ', command_c::Color_Light_Gray, '', 0, 0, '');
                    static::print_r($v, "\t" . $tab, $depth + 1, $file, $line);
                }
                static::echo($tab . "]", $color, $file, $line);
            }
        } else {
            static::echo(is_numeric($array) ? $array : '"' . $array . '"', $color, $file, $line);
        }
    }
}
