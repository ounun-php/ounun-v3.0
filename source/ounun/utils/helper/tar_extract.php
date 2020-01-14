<?php


namespace ounun\utils\helper;



class tar_extract extends tar_lib
{

    var $extractDir = './extract';

    function __construct($tarname, $extractDir = './extract') {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $this->tarname = $tarname;
        $this->extractDir = $extractDir;
        $this->checkcompress();

        if(!is_dir($extractDir)) {
            $this->mkdir($extractDir);
        }
        $this->extract();
    }

    function extract() {
        global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write;

        $this->filehand = $_dofunc_open($this->tarname, 'rb');
        while(($binary_buffer = fread($this->filehand, 512)) <> '') {
            $file = $this->parseHeader($binary_buffer);
            if(!$file['name']) continue;
            $file['name'] = $this->extractDir.'/'.$file['name'];
            $readtimes = floor($file['size']/512);

            $this->mkdir($file['name']);
            $fp = fopen($file['name'], 'wb');
            for($i = 0; $i<$readtimes; $i++) {
                fwrite($fp, $_dofunc_read($this->filehand, 512));
            }
            if(($lastsize = $file['size']%512)) {
                fwrite($fp, $_dofunc_read($this->filehand, 512), $lastsize);
            }
            fclose($fp);
        }
        $_dofunc_close($this->filehand);
    }

    function parseHeader($header) {

        $checksum = $this->checksum($header);
        $data = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', $header);

        $file['checksum'] = OctDec(trim($data['checksum']));

        $file['name'] = trim($data['filename']);
        $file['mode'] = OctDec(trim($data['mode']));
        $file['uid'] = OctDec(trim($data['uid']));
        $file['gid'] = OctDec(trim($data['gid']));
        $file['size'] = OctDec(trim($data['size']));
        $file['mtime'] = OctDec(trim($data['mtime']));
        $file['type'] = $data['typeflag'];

        return $file;
    }
}
