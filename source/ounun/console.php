<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;

use ounun\console\task\simple;
use ounun\console\c;

class console
{


    /** @var string 命令名称 */
    public $name;

    /** @var string 命令版本 */
    public $version;

    /** @var simple[] 命令 */
    public $commands = [];

    /** @var array  默认提供的命令 */
    protected static $default_cmds = [
        "task\\base\\help",
        "task\\base\\test",
    ];

    /**
     * console constructor.
     * @param array $cmds
     * @param string $name
     * @param string $version
     */
    public function __construct(array $cmds = [], string $name = 'Ounun CMD', string $version = '0.1')
    {
        // echo "\\ounun\\cmd\\def\\help::class:".\ounun\cmd\def\help::class;
        $this->name    = $name;
        $this->version = $version;

        $cmds = array_merge(static::$default_cmds, $cmds);
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                if (class_exists($cmd)) {
                    if (is_subclass_of($cmd, "ounun\\cmd\\cmd")) {
                        $this->add(new $cmd($this));  // 注册指令
                    }
                }
            }
        }
        // print_r(['$commands'=>$this->commands]);
    }

    /**
     * 添加一个指令
     * @param simple $cmd 命令实例
     * @return bool|simple
     */
    public function add(simple $cmd)
    {
        $this->commands[$cmd->name] = $cmd;
        return $cmd;
    }

    /**
     * 执行
     * @param array $argv
     * @return int
     */
    public function run(array $argv)
    {
        // print_r(['$argv'=>$argv]);
        if (empty($argv) || empty($argv[1]) || '--help' == $argv[1] || '--list' == $argv[1]) {
            $command = $this->commands[c::Default_Cmd];
            $command->execute($argv);
        } else {
            $command = $this->commands[$argv[1]];
            if ($command) {
                if ('--help' == $argv[2]) {
                    $command->help($argv);
                } else {
                    $run_time = 0 - microtime(true);
                    $run_cmd = str_pad($argv[1], 16);
                    static::echo("-- runing... {$run_cmd} " . date("Y-m-d H:i:s") . "             --------------------", c::Color_Cyan);
                    $command->execute($argv);
                    $run_time += microtime(true);
                    static::echo("-- done      {$run_cmd} " . date("Y-m-d H:i:s") . " run:" . str_pad(round($run_time, 4) . 's', 8) . "--------------------", c::Color_Cyan);
                }
            } else {
                static::echo("命令:{$argv[1]} 不存在!", c::Color_Light_Red);
                static::echo("你可以尝试下面", c::Color_Green);
                $command = $this->commands[c::Default_Cmd];
                $command->execute($argv);
            }
        }
        return 0;
    }



    /**
     * @param string $msg
     * @param string $color
     * @param string $file
     * @param int $line
     * @param int $time
     * @param string $end
     */
    static public function echo(string $msg, string $color = '',string $file = '', int $line = 0,int $time = 0, string $end = "\n")
    {
        if($file){
            $file = basename($file);
            $file = "{$file}:{$line} ";
        }else{
            $file = '';
        }
        if($time){
            $file = c::Color_Cyan . '['.date("Y-m-d H:i:s").']' .c::Color_None.$file;
        }
        if (empty($color)) {
            echo $file.$msg . $end;
        } else {
            echo $file.$color . $msg . c::Color_None . $end;
        }
    }

    /**
     * @param mixed $array
     * @param string $tab
     * @param int $depth0
     * @param string $file
     * @param int $line
     */
    static public function print_r($array, $tab = '', int $depth0 = 0,string $file = '', int $line = 0)
    {
        $depth = $depth0 % c::Depth_Colors_Count;
        $color = c::Depth_Colors[$depth];
        // echo "\$depth:{$depth} - ";
        if (is_array($array)) {
            if (empty($array)) {
                static::echo("[]", $color,$file,$line);
            } else {
                static::echo("[", $color,$file,$line);
                foreach ($array as $k => $v) {
                    static::echo("\t" . $tab . (is_numeric($k) ? $k : '"' . $k . '"'), $color, $file,$line,0,'');
                    static::echo(' => ', c::Color_Light_Gray, '',0,0, '');
                    static::print_r($v, "\t" . $tab, $depth + 1,$file,$line);
                }
                static::echo($tab . "]", $color,$file,$line);
            }
        } else {
            static::echo(is_numeric($array) ? $array : '"' . $array . '"', $color,$file,$line);
        }
    }
}
