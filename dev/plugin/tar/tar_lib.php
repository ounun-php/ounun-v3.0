<?php

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\plugin\tar;


class tar_lib
{
    public string $tarname = '';
    public int $filehand = 0;

    function checkcompress()
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;
        if ((substr($this->tarname, -7) == '.tar.gz') || (substr($this->tarname, -4) == '.tgz')) {
            $_dofunc_open  = 'gzopen';
            $_dofunc_close = 'gzclose';
            $_dofunc_read  = 'gzread';
            $_dofunc_write = 'gzwrite';
        } else {
            $_dofunc_open  = 'fopen';
            $_dofunc_close = 'fclose';
            $_dofunc_read  = 'fread';
            $_dofunc_write = 'fwrite';
        }
    }

    function mkdir($dir)
    {
        $dirlist = explode('/', $dir);
        $depth   = count($dirlist) - 1;
        $dir     = $dirlist[0];
        for ($i = 0; $i < $depth; $i++) {
            if (!is_dir($dir)) {
                if ($dir != '.')
                    mkdir($dir, 0777);
            }
            $dir  .= '/' . $dirlist[$i + 1];
            $last = $off;
        }
    }

    function checksum($binary_data_first, $binary_data_last = '')
    {
        if ($binary_data_last == '') {
            $binary_data_last = $binary_data_first;
        }
        $checksum = 0;
        for ($i = 0; $i < 148; $i++) {
            $checksum += ord(substr($binary_data_first, $i, 1));
        }
        for ($i = 148; $i < 156; $i++) {
            $checksum += ord(' ');
        }
        for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
            $checksum += ord(substr($binary_data_last, $j, 1));
        }
        return $checksum;
    }
}
