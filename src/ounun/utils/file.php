<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\utils;


class file
{
    /**
     * 删除目录
     *
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param bool $recursive 允许递归删除 $path_name 所指定的多级嵌套目录。
     */
    static public function rmdir(string $dir, bool $recursive = true)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir($dir . $file . '/')) {
                        static::rmdir($dir . $file . '/');
                    } else {
                        unlink($dir . $file);
                    }
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
     * @param string $dir 文件或目录的路径（目录尾部带/）。
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
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param array $return_files 如果提供 $return_files 参数， 则外部命令执行后的返回状态将会被设置到此变量中。
     * @param bool $recursive 允许递归列出 $dir 所指定的多级嵌套目录。
     * @param array $options 参数选项 [path=>'相对目录','ignore_filename'=>'忽略,只匹配文件名','ignore_fullname'=>'忽略,完全匹配路径及文件名']
     * @param array $return_dirs
     * @return array
     */
    static public function scandir(string $dir, array &$return_files = [], bool $recursive = true, array $options = [], array &$return_dirs = []): array
    {
        if (is_dir($dir)) {
            if (isset($options['ignore_filename']) && is_array($options['ignore_filename']) && $options['ignore_filename']) {
                $ignore_filename = array_merge(['.', '..'], $options['ignore_filename']);
            } else {
                $ignore_filename = ['.', '..'];
            }
            $options['ignore_fullname'] ??= [];
            $options['path']            ??= '';
            $files                      = array_diff(scandir($dir . $options['path']), $ignore_filename);
            foreach ($files as $file) {
                if (!in_array($options['path'] . $file, $options['ignore_fullname'])) {
                    if (is_dir($dir . $options['path'] . $file . '/')) {
                        $return_dirs[] = $options['path'] . $file;
                        if ($recursive) {
                            static::scandir($dir, $return_files, $recursive, array_merge($options, ['path' => $options['path'] . $file . '/']),$return_dirs);
                        }
                    } else {
                        $return_files[] = $options['path'] . $file;
                    }
                }
            }
        }
        return $return_files;
    }

    /**
     * 取得文件大小
     *
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param bool $recursive 允许递归列出 $dir 所指定的多级嵌套目录。
     * @return int
     */
    static function size(string $dir, bool $recursive = true): int
    {
        $size = 0;
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir($dir . $file . '/')) {
                        $size += static::size($dir . $file . '/', $recursive);
                    } else {
                        $size += filesize($dir . $file);
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
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param int $mode 注意 mode 不会被自动当成八进制数值，而且也不能用字符串（例如 "g+w"）。要确保正确操作，需要给 mode 前面加上 0
     * @param bool $recursive 允许递归 $dir 所指定的多级嵌套目录。
     * @return bool
     */
    static function chmod(string $dir, int $mode = 0755, bool $recursive = true): bool
    {
        $mode = intval($mode, 8);
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir($dir . $file . '/')) {
                        static::chmod($dir . $file . '/', $mode, $recursive);
                    } else {
                        chmod($dir . $file, $mode);
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
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param int $mtime 要设定的时间。如果没有提供参数 time 则会使用当前系统的时间。
     * @param int $atime 如果给出了这个参数，则给定文件的访问时间会被设为 atime，否则会设置 为time。如果没有给出这两个参数，则使用当前系统时间。
     * @param bool $recursive 允许递归 $dir 所指定的多级嵌套目录。
     * @return bool
     */
    static function touch(string $dir, int $mtime = 0, int $atime = 0, bool $recursive = true): bool
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($recursive) {
                foreach ($files as $file) {
                    if (is_dir($dir . $file . '/')) {
                        static::touch($dir . $file . '/', $mtime, $atime, $recursive);
                    } else {
                        touch($dir . $file, $mtime, $atime);
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
     * @param string $old_path 旧名字，文件或目录的路径（目录尾部带/）。
     * @param string $new_path 新的名字，文件或目录的路径（目录尾部带/）。
     * @return bool
     */
    static function rename(string $old_path, string $new_path): bool
    {
        return rename($old_path, $new_path);
    }

    /**
     * 文件及文件夹移动
     *
     * @param string $source 源，文件或目录的路径（目录尾部带/）。
     * @param string $target 目标，文件或目录的路径（目录尾部带/）。
     * @return bool
     */
    static function move(string $source, string $target): bool
    {
        if (is_dir($source)) {
            if (!is_dir($target)) {
                static::mkdir($target);
            }
            $files = array_diff(scandir($source), ['.', '..']);
            foreach ($files as $file) {
                if (is_dir($source . $file . '/')) {
                    static::move($source . $file . '/', $target . $file . '/');
                } else {
                    rename($source . $file, $target . $file);
                }
            }
            rmdir($source);
        } elseif (is_file($source)) {
            static::mkdir(dirname($target));
            rename($source, $target);
        }
        return true;
    }

    /**
     * 查找条件， 文件及文件夹复制
     *
     * @param string $source 源，文件或目录的路径（目录尾部带/）。
     * @param string $dest 目标，文件或目录的路径（目录尾部带/）。
     * @param string|null $type
     * @param string|null $pattern
     * @return bool
     */
    static function copy(string $source, string $dest, ?string $type = null, ?string $pattern = null): bool
    {
        if (is_null($type)) {
            if (is_dir($source)) {
                static::mkdir($dest);
                $files = array_diff(scandir($source), ['.', '..']);
                foreach ($files as $file) {
                    if (is_dir($source . $file . '/')) {
                        static::copy($source . $file . '/', $dest . $file . '/', $type, $pattern);
                    } else {
                        copy($source . $file, $dest . $file);
                    }
                }
            } elseif (is_file($source)) {
                static::mkdir(dirname($dest));
                copy($source, $dest);
            }
        } else {
            $files = self::find($source, $pattern, $type, true);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $newfile = str_replace($source, $dest, str_replace("\\", "/", $file));
                    self::mkdir(dirname($newfile));
                    copy($file, $newfile);
                }
            }
        }
        return true;
    }

    /**
     * 查找
     *
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param string $pattern 查找规则
     * @param string $type 查找类型
     * @param bool $recursive 允许递归 $dir 所指定的多级嵌套目录。
     * @param array $return_files
     * @return array
     */
    static function find(string $dir, string $pattern, $type = self::Find_Type_Name, $recursive = false, &$return_files = []): array
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
        } elseif (is_file($dir)) {
            $files = [$dir];
        } else {
            $files = [];
        }
        if ($type == static::Find_Type_Name) {
            $return_files = array_merge($return_files, preg_grep($pattern, $files));
        } elseif ($type == static::Find_Type_Content) {
            /** @var string $file */
            foreach ($files as $file) {
                if (is_file($file) && preg_grep($pattern, [file_get_contents($file)])) {
                    $return_files[] = $file;
                }
            }
        } elseif ($type == static::Find_Type_Filemtime) {
            $filemtime = strtotime($pattern);
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) >= $filemtime) {
                    $return_files[] = $file;
                }
            }
        }
        if ($recursive) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    static::find($dir . $file . '/', $pattern, $type, $recursive, $return_files);
                }
            }
        }
        return $return_files;
    }

    /** @var string 文件名 */
    const Find_Type_Name = 'name';
    /** @var string 文件内容 */
    const Find_Type_Content = 'content';
    /** @var string 文件名创建时间 */
    const Find_Type_Filemtime = 'filemtime';

    /**
     * 树
     *
     * @param string $dir 文件或目录的路径（目录尾部带/）。
     * @param string|null $type 返回类型
     * @param array $return_files
     * @return array
     */
    static function tree(string $dir, ?string $type = null, array &$return_files = []): array
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            if ($type === null) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $return_files['dir'][] = $file;
                        static::tree($file, $type, $return_files);
                    } else {
                        $return_files['file'][] = $file;
                    }
                }
            } elseif ($type == static::Tree_Type_File) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        static::tree($file, $type, $return_files);
                    } else {
                        $return_files['file'][] = $file;
                    }
                }
            } elseif ($type == static::Tree_Type_Dir) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $return_files['dir'][] = $file;
                        static::tree($file, $type, $return_files);
                    }
                }
            }
        } elseif (is_file($dir)) {
            if ($type === null) {
                $return_files['file'][] = $dir;
            } elseif ($type == static::Tree_Type_File) {
                $return_files['file'][] = $dir;
            }
        }
        return $return_files;
    }

    /** @var string 文件 */
    const Tree_Type_File = 'file';
    /** @var string 目录 */
    const Tree_Type_Dir = 'dir';
    /** @var string 文件与目录（默认） */
    // const Tree_Type_Default = null;
}
