<?php

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\plugin\tar;


class tar extends tar_lib
{
    public array $filelist = [];
    public array $filealias = [];

    function tar($tarname, $filelist, $filealias = [])
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $this->tarname = $tarname;
        $this->checkcompress();
        $this->filelist  = is_array($filelist) ? $filelist : explode(' ', $filelist);
        $this->filealias = $filealias;
        $this->create();
    }

    function create()
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $this->filehand = $_dofunc_open($this->tarname, 'wb');

        $this->parse($this->filelist, $this->filealias);
        $this->footer();

        $_dofunc_close($this->filehand);
    }

    function parse($filelist, $alias = [])
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $files = count($filelist);
        for ($i = 0; $i < $files; $i++) {
            $filename = $filelist[$i];
            if (is_dir($filename)) {
                $dirh = opendir($filename);
                readdir($dirh); // '.'
                readdir($dirh); // '..'
                while ($nextfile = readdir($dirh)) {
                    $temp_filelist[] = ($filename != '.') ? $filename . '/' . $nextfile : $nextfile;
                }
                $this->parse($temp_filelist);
                closedir($dirh);
                unset($dirh);
                unset($temp_filelist);
                unset($nextfile);
                continue;
            }
            $filealias = ($alias[$i]) ? $alias[$i] : $filename;
            $this->parseFile($filename, $filealias);
        }
    }

    function parseFile($filename, $alias = '')
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;
        if (!$alias) $alias = $filename;
        $v_info  = stat($filename);
        $v_uid   = sprintf('%6s ', DecOct($v_info[4]));
        $v_gid   = sprintf('%6s ', DecOct($v_info[5]));
        $v_perms = sprintf('%6s ', DecOct(fileperms($filename)));
        clearstatcache();
        $v_size  = filesize($filename);
        $v_size  = sprintf('%11s ', DecOct($v_size));
        $v_mtime = sprintf('%11s', DecOct(filemtime($filename)));

        $v_binary_data_first = pack('a100a8a8a8a12A12', $alias, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime);
        $v_binary_data_last  = pack('a1a100a6a2a32a32a8a8a155a12', '', '', '', '', '', '', '', '', '', '');
        $_dofunc_write($this->filehand, $v_binary_data_first, 148);

        $v_checksum = $this->checksum($v_binary_data_first, $v_binary_data_last);

        $v_checksum    = sprintf('%6s ', DecOct($v_checksum));
        $v_binary_data = pack('a8', $v_checksum);
        $_dofunc_write($this->filehand, $v_binary_data, 8);
        $_dofunc_write($this->filehand, $v_binary_data_last, 356);

        $fp = fopen($filename, 'rb');
        while (($buffer = fread($fp, 512)) <> '') {
            $binary_buffer = pack('a512', $buffer);
            $_dofunc_write($this->filehand, $binary_buffer);
        }
    }

    function footer()
    {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $v_binary_data = pack('a512', '');
        $_dofunc_write($this->filehand, $v_binary_data);
    }
}
