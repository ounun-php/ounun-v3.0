<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\utils;


class file
{
    /**
     * 删除目录
     * @param  string $pathname
     */
    static public function deldir(string $pathname)
    {
        //如果是目录则继续
        if (is_dir($pathname)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($pathname);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($pathname . $val)) {
                        //子目录中操作删除文件夹和文件
                        static::deldir($pathname . $val . '/');
                        //目录清空后删除空文件夹
                        if (file_exists($pathname . $val . '/')) {
                            rmdir($pathname . $val . '/');
                        }
                    } else {
                        //如果是文件直接删除
                        unlink($pathname . $val);
                    }
                }
            }
            rmdir($pathname);
        }
    }

    /**
     * 创建目录
     * @param string $pathname
     * @param int $mode
     * @param bool $recursive
     */
    static public function mkdir(string $pathname,int $mode = 0777, bool $recursive = false)
    {
        if (!file_exists($pathname)) {
            mkdir($pathname, $mode, $recursive);
        }
    }


    /**
     * 读取目录下所有文件名
     * @param string $pathname
     */
    static public function readfile(string $pathroot, array &$files = [], string $pathname = '')
    {
        // 如果是目录则继续
        if (is_dir($pathroot.$pathname)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($pathroot.$pathname);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($pathroot.$pathname . $val)) {
                        // 子目录
                        static::readfile($pathroot,$files,$pathname . $val . '/');
                    } else {
                        // 是文件
                        $files[] = $pathname.$val;
                    }
                }
            }
        }
    }
}
