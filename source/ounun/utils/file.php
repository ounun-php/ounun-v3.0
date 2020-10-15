<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\utils;


use ounun\utils\parse\ini;

class file
{
    private static array $_error_msg = [];

    /**
     * 删除目录
     *
     * @param string $dir 目录的路径。
     * @param bool $recursive 允许递归删除 $path_name 所指定的多级嵌套目录。
     */
    static public function rmdir(string $dir, bool $recursive = true)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    (is_dir("{$dir}/{$file}")) ? static::rmdir("{$dir}/{$file}") : unlink("{$dir}/{$file}");
                }
                rmdir($dir);
            } else if (empty($files)) {
                rmdir($dir);
            }
        }
        if (file_exists($dir)) {
            unlink($dir);
        }
    }

    /**
     * 创建目录
     *
     * @param string $dir 目录的路径。
     * @param int $mode 默认的 mode 是 0777，意味着最大可能的访问权。
     * @param bool $recursive 允许递归创建由 $path_name 所指定的多级嵌套目录。
     */
    static public function mkdir(string $dir, int $mode = 0777, bool $recursive = true)
    {
        if (!file_exists($dir)) {
            mkdir($dir, $mode, $recursive);
        }
    }

    /**
     * 列出指定路径中的文件和目录
     *
     * @param string $dir 目录的路径。
     * @param bool $recursive 允许递归列出 $dir 所指定的多级嵌套目录。
     * @param array $return_files 如果提供 $return_files 参数， 则外部命令执行后的返回状态将会被设置到此变量中。
     * @return array
     */
    static public function scandir(string $dir, array &$return_files = [], bool $recursive = true)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir("{$dir}/{$file}")) {
                        static::scandir("{$dir}/{$file}", $return_files, $recursive);
                    } else {
                        $return_files[] = "{$dir}/{$file}";
                    }
                }
            }
        }
        return $return_files;
    }

    /**
     * 取得文件大小
     *
     * @param string $dir 目录的路径。
     * @param bool $recursive 允许递归列出 $dir 所指定的多级嵌套目录。
     * @return int
     */
    static function size(string $dir, bool $recursive = true)
    {
        $size = 0;
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir("{$dir}/{$file}")) {
                        $size += static::size("{$dir}/{$file}", $recursive);
                    } else {
                        $size += filesize("{$dir}/{$file}");
                    }
                }
            }
        } elseif (is_file($dir)) {
            $size += filesize($dir);
        }
        return $size;
    }


    /**
     * 改变文件模式
     *
     * @param string $dir 文件的路径。
     * @param int $mode 注意 mode 不会被自动当成八进制数值，而且也不能用字符串（例如 "g+w"）。要确保正确操作，需要给 mode 前面加上 0
     * @param bool $recursive 允许递归 $dir 所指定的多级嵌套目录。
     * @return bool
     */
    static function chmod(string $dir, int $mode = 0755, bool $recursive = true)
    {
        $mode = intval($mode, 8);
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir("{$dir}/{$file}")) {
                        static::chmod("{$dir}/{$file}", $mode, $recursive);
                    } else {
                        chmod("{$dir}/{$file}", $mode);
                    }
                }
            }
            chmod($dir, $mode);
        }
        return true;
    }

    /**
     * 设定文件的访问和修改时间
     *
     * @param string $dir 要设定的文件名。
     * @param int $mtime 要设定的时间。如果没有提供参数 time 则会使用当前系统的时间。
     * @param int $atime 如果给出了这个参数，则给定文件的访问时间会被设为 atime，否则会设置 为time。如果没有给出这两个参数，则使用当前系统时间。
     * @param bool $recursive 允许递归 $dir 所指定的多级嵌套目录。
     * @return bool
     */
    static function touch(string $dir, int $mtime = 0, int $atime = 0, bool $recursive = true)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir("{$dir}/{$file}")) {
                        static::touch("{$dir}/{$file}", $mtime, $atime, $recursive);
                    } else {
                        touch("{$dir}/{$file}", $mtime, $atime);
                    }
                }
            }
        } elseif (is_file($dir)) {
            touch($dir, $mtime, $atime);
        }
        return true;
    }

    /**
     * 重命名一个文件或目录
     *
     * @param string $old_path 旧名字
     * @param string $new_path 新的名字。
     * @return bool
     */
    static function rename(string $old_path, string $new_path)
    {
        return rename($old_path, $new_path);
    }



    static function move(string $source, string $target, bool $recursive = true)
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($target)) {
            static::mkdir($target);
        }
        $source = self::path($source);
        $target = self::path($target);
        $items  = glob($source . '*');
        if (!is_array($items)) return true;
        foreach ($items as $v) {
            $basename = basename($v);
            $to       = $target . '/' . $basename;
            if (is_dir($v)) {
                self::move($v, $to);
            } else {
                if (!@rename($v, $to)) {
                    self::$_error_msg[] = sprintf('can not move file %s to %s', $v, $to);
                    return false;
                }
            }
        }
        if (!@rmdir($source)) throw new ct_exception("can not rmdir $source");
        return true;
    }

    static function copy($source, $target, $mode = null, $pattern = null)
    {
        if (PHP_OS == 'WINNT') $mode = null;
        if (!is_dir($source)) return false;
        if (is_null($mode)) {
            if (!is_dir($target)) self::create($target);
            $source = self::path($source);
            $target = self::path($target);
            $items  = glob($source . '*');
            if (!is_array($items)) return true;
            foreach ($items as $v) {
                $basename = basename($v);
                $to       = $target . '/' . $basename;
                if (is_dir($v)) {
                    self::copy($v, $to);
                } else {
                    if (!@copy($v, $to)) {
                        self::$_error_msg[] = sprintf('can not copy file %s to %s', $v, $to);
                        return false;
                    }
                }
            }
        } else {
            $files = self::find($source, $pattern, $mode, true);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $newfile = str_replace($source, $target, str_replace("\\", "/", $file));
                    self::create(dirname($newfile));
                    if (!copy($file, $newfile)) {
                        self::$_error_msg[] = sprintf('can not copy file %s to %s', $file, $newfile);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    static function find($path, $pattern, $mode = 'name', $deep = false, &$array = [])
    {
        if (!is_dir($path)) return false;
        $path  = self::path($path);
        $items = glob($path . '*');
        if (!is_array($items)) return [];

        if ($mode == 'name') {
            $array = array_merge($array, preg_grep($pattern, $items));
        } elseif ($mode == 'data') {
            foreach ($items as $item) {
                if (is_file($item) && preg_grep($pattern, file_get_contents($item))) $array[] = $item;
            }
        } elseif ($mode == 'filemtime') {
            $filemtime = strtotime($pattern);
            foreach ($items as $item) {
                if (is_file($item) && @filemtime($item) >= $filemtime) $array[] = $item;
            }
        }
        if ($deep) {
            foreach ($items as $item) {
                if (is_dir($item)) self::find($item, $pattern, $mode, $deep, $array);
            }
        }
        return $array;
    }


    static function tree($path, $type = null, &$array = [])
    {
        if (!is_dir($path)) return [];
        $path  = self::path($path);
        $items = glob($path . '*');
        if (!is_array($items)) return $array;
        if ($type === null) {
            foreach ($items as $item) {
                if (is_dir($item)) {
                    $array['dir'][] = $item;
                    self::tree($item, $type, $array);
                } else {
                    $array['file'][] = $item;
                }
            }
        } elseif ($type == 'file') {
            foreach ($items as $item) {
                if (is_dir($item)) {
                    self::tree($item, $type, $array);
                } else {
                    $array[] = $item;
                }
            }
        } elseif ($type == 'dir') {
            foreach ($items as $item) {
                if (is_dir($item)) {
                    $array[] = $item;
                    self::tree($item, $type, $array);
                }
            }
        }
        return $array;
    }
}
