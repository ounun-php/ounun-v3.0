<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\utils;


class shell
{
    /** @var bool  */
    protected $_debug = false;

    /**
     * shell constructor.
     * @param $debug
     */
    public function __construct($debug)
    {
        $this->_debug = $debug;
    }

    /**
     * @param $dir
     */
    protected function _mkdir($dir)
    {
        if ($this->_debug) {
            echo "debug mkdir:{$dir}\n";
        } else {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    /**
     * @param string $msg
     * @param string $cmd
     * @param string $sudo
     */
    protected function _sh_file(string $msg, string $cmd, $sudo = '')
    {
        $cmd = "#!/bin/sh\n" . $cmd;
        if ($sudo) {
            $cmd = str_replace($sudo, '', $cmd);
        }
        $filename = "/tmp/cmd_" . time() . ".sh";
        file_put_contents($filename, $cmd);
        $cmd_file = "chmod +x {$filename}\n{$sudo}{$filename}";
        echo "\$cmd_file:{$cmd_file}\n";
        $this->_sh($msg, $cmd_file);
        // unlink($filename);
    }

    /**
     * @param string $msg
     * @param string $cmd
     * @return bool
     */
    protected function _sh(string $msg, string $cmd)
    {
        if ($this->_debug) {
            echo "\n\ndebug no run ......\n\n\n";
            echo 'cmd:<pre>', $cmd, '</pre>';
        } else {
            $retval = '';
            echo '<pre>', "\n{$msg}:\n";
            // echo  $cmd;
            $last_line = \system($cmd, $retval);
            echo '</pre>';
        }
        return true;
    }
}
