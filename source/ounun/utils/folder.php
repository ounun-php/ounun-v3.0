<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\utils\helper;

class folder
{
    private static $_error_msg = [];

    static function read($pattern, $return = null)
    {
        if ($return === 'dir')
        {
            return glob($pattern, GLOB_ONLYDIR);
        }
        elseif ($return === 'file')
        {
            $array = glob($pattern);
            return $array ? array_filter($array, 'is_file') : false;
        }
        else
        {
            return glob($pattern);
        }
    }


    static function create($structure, $mode = 0755, $force = false)
    {
        if (is_dir($structure) || $structure=='')
        {
            return true;
        }
        if (is_file($structure))
        {
            if (!$force || !@unlink($structure))
            {
                self::$_error_msg[] = sprintf('%s is a file', $dir);
                return false;
            }
        }
        if (self::create(dirname($structure), $mode, $force))
        {
            return @mkdir($structure, $mode);
        }
        else
        {
            self::$_error_msg[] = sprintf('can not mkdir %s', $structure);
            return false;
        }
    }

    static function delete($path)
    {
        if (!is_dir($path)){
            return false;
        }
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) {
            return true;
        }

        foreach ($items as $v) {
            if (is_dir($v)) {
                self::delete($v);
            } else {
                if(!@unlink($v)) {
                    self::$_error_msg[] = sprintf('can not delete file %s', $v);
                    return false;
                }
            }
        }
        if(!@rmdir($path)) {
            self::$_error_msg[] = sprintf('can not rmdir %s', $path);
            return false;
        }
        return true;
    }

    static function clear($path)
    {
        if (!is_dir($path)) return false;
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) return true;
        foreach ($items as $v)
        {
            if (is_dir($v))
            {
                self::delete($v);
            }
            else
            {
                if(!@unlink($v))
                {
                    self::$_error_msg[] = sprintf('can not delete file %s', $v);
                    return false;
                }
            }
        }
        return true;
    }

    static function rename($oldpath, $newpath)
    {
        return rename($oldpath, $newpath);
    }

    static function move($source, $target)
    {
        if (!is_dir($source)) return false;
        if (!is_dir($target)) self::create($target);
        $source = self::path($source);
        $target = self::path($target);
        $items = glob($source.'*');
        if (!is_array($items)) return true;
        foreach ($items as $v)
        {
            $basename = basename($v);
            $to = $target.'/'.$basename;
            if (is_dir($v))
            {
                self::move($v, $to);
            }
            else
            {
                if(!@rename($v, $to))
                {
                    self::$_error_msg[] = sprintf('can not move file %s to %s', $v, $to);
                    return false;
                }
            }
        }
        if(!@rmdir($source)) throw new ct_exception("can not rmdir $source");
        return true;
    }

    static function copy($source, $target, $mode = null, $pattern = null)
    {
        if(PHP_OS == 'WINNT') $mode = null;
        if (!is_dir($source)) return false;
        if (is_null($mode))
        {
            if (!is_dir($target)) self::create($target);
            $source = self::path($source);
            $target = self::path($target);
            $items = glob($source.'*');
            if (!is_array($items)) return true;
            foreach ($items as $v)
            {
                $basename = basename($v);
                $to = $target.'/'.$basename;
                if (is_dir($v))
                {
                    self::copy($v, $to);
                }
                else
                {
                    if(!@copy($v, $to))
                    {
                        self::$_error_msg[] = sprintf('can not copy file %s to %s', $v, $to);
                        return false;
                    }
                }
            }
        }
        else
        {
            $files = self::find($source, $pattern, $mode, true);
            foreach ($files as $file)
            {
                if (is_file($file))
                {
                    $newfile = str_replace($source, $target, str_replace("\\", "/", $file));
                    self::create(dirname($newfile));
                    if (!copy($file, $newfile))
                    {
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
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) return [];

        if ($mode == 'name')
        {
            $array = array_merge($array, preg_grep($pattern, $items));
        }
        elseif ($mode == 'data')
        {
            foreach ($items as $item)
            {
                if (is_file($item) && preg_grep($pattern, file_get_contents($item))) $array[] = $item;
            }
        }
        elseif ($mode == 'filemtime')
        {
            $filemtime = strtotime($pattern);
            foreach ($items as $item)
            {
                if (is_file($item) && @filemtime($item)>=$filemtime) $array[] = $item;
            }
        }
        if ($deep)
        {
            foreach ($items as $item)
            {
                if (is_dir($item)) self::find($item, $pattern, $mode, $deep, $array);
            }
        }
        return $array;
    }

    static function chmod($path, $mode = 0755)
    {
        if (!is_dir($path)) return false;
        $mode = intval($mode, 8);
        if(!@chmod($path, $mode))
        {
            self::$_error_msg[] = sprintf('%s not changed to %s', $path, $mode);
        }
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) return true;
        foreach ($items as $item)
        {
            if (is_dir($item))
            {
                self::chmod($item, $mode);
            }
            else
            {
                if(!@chmod($item, $mode))
                {
                    self::$_error_msg[] = sprintf('%s not changed to %s', $item, $mode);
                }
            }
        }
        return true;
    }

    static function touch($path, $mtime = 0, $atime = 0)
    {
        if (!is_dir($path)) return false;
        if(!@touch($path, $mtime, $atime))
        {
            self::$_error_msg[] = sprintf('%s not touch to %s', $path, $mtime);
        }
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) return true;
        foreach ($items as $item)
        {
            if (is_dir($item))
            {
                self::touch($path, $mtime, $atime);
            }
            else
            {
                if(!@touch($item, $mtime, $atime))
                {
                    self::$_error_msg[] = sprintf('%s not touch to %s', $item, $mtime);
                }
            }
        }
        return true;
    }

    static function file_ext_name($filename,$flag='.')
    {
        $filearea = explode ($flag,$filename );
        $partnum = count ( $filearea );
        $fileclass = $filearea[$partnum - 1];
        return $fileclass;
    }

    static function tree($path, $mode = null, &$array = [])
    {
        if (!is_dir($path)) return false;
        $path = self::path($path);
        $items = glob($path.'*');
        if (!is_array($items)) return $array;
        if ($mode === null)
        {
            foreach ($items as $item)
            {
                if (is_dir($item))
                {
                    $array['dir'][] = $item;
                    self::tree($item, $mode, $array);
                }
                else
                {
                    $array['file'][] = $item;
                }
            }
        }
        elseif ($mode == 'file')
        {
            foreach ($items as $item)
            {
                if (is_dir($item))
                {
                    self::tree($item, $mode, $array);
                }
                else
                {
                    $array[] = $item;
                }
            }
        }
        elseif ($mode == 'dir')
        {
            foreach ($items as $item)
            {
                if (is_dir($item))
                {
                    $array[] = $item;
                    self::tree($item, $mode, $array);
                }
            }
        }
        return $array;
    }

    static function size($path,$pattern = '*')
    {
        if (!is_dir($path)) return false;
        $size = 0;
        $path = self::path($path);
        $items = glob($path.$pattern);
        if (!is_array($items)) return $size;
        foreach ($items as $item)
        {
            if (is_dir($item))
            {
                $size += self::size($item);
            }
            else
            {
                $size += filesize($item);
            }
        }
        return $size;
    }

    static function sizeunit($filesize)
    {
        if($filesize >= 1073741824)
        {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        }
        elseif($filesize >= 1048576)
        {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        }
        elseif($filesize >= 1024)
        {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        }
        else
        {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }

    static function path($path)
    {
        return rtrim(preg_replace("|[\/]+|", '/', $path), '/').'/';
    }

    static function errormsgs()
    {
        return self::$_error_msg;
    }
}
