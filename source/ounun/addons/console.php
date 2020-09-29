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
            foreach ($commands as $key => $command) {
                if ($key && $command) {
                    $this->add($key, $command);
                }
            }
        }
    }

    /**
     * 添加一个指令
     *
     * @param string $key 索引关键key
     * @param string $command 命令实例
     * @return int
     */
    public function add(string $key, string $command)
    {
        static::$commands[$key] = $command;
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
        if (empty($argv) || empty($argv[1]) || '--help' == $argv[1] || '--list' == $argv[1]) {
            /** @var string $command */
            $command = static::$commands[command_c::Default_Cmd];
        } else if ($argv[1]) {
            // addons
            $command = "\\addons\\{$argv[1]}\\command";
            // command
            if (!class_exists($command)) {
                if (isset(static::$commands[$argv[1]])) {
                    /** @var string $command */
                    $command = static::$commands[$argv[1]];
                }
            }
        }

        // command 为空
        if (empty($command)) {
            static::echo("命令:{$argv[1]} 不存在!", command_c::Color_Light_Red);
            static::echo("你可以尝试下面", command_c::Color_Green);
            $command = static::$commands[command_c::Default_Cmd];
        }

        // command 执行
        if ($command && class_exists($command)) {
            if (is_subclass_of($command, command::class)) {
                if ('--help' == $argv[2]) {
                    (new $command($this))->help($argv);  // 执行指令help
                } else {
                    $run_cmd = str_pad($command, 16);
                    /** @var command $command_o */
                    $command_o = new $command();
                    $command_o->start();
                    static::echo(date("Y-m-d H:i:s") . "    runing... {$run_cmd}                      --------------------", command_c::Color_Cyan);
                    $command_o->execute($argv);  // 执行指令
                    if ($command_o->run_state()) {
                        $command_o->stop();
                    }
                    static::echo(date("Y-m-d H:i:s") . "    done      {$run_cmd}    运行时间:" . str_pad(round($command_o->run_time(), 4) . '秒', 8) . " --------------------", command_c::Color_Cyan);
                }
            } else {
                static::echo("命令:{$command} 父类不是:" . command::class, command_c::Color_Light_Red);
            }
        } else {
            static::echo("命令:{$argv[1]} 文件不存在!", command_c::Color_Light_Red);
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
