<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\attachment;

class upload extends driver
{
    public function __construct($dir = '', array $allow_exts = [], $maxfilesize = 0)
    {
        parent::__construct($dir);
        $this->set($dir, $allow_exts, $maxfilesize);
    }

    public function set($dir, array $allow_exts = [], $maxfilesize = 0)
    {
        if (!is_null($maxfilesize)) $this->filesize_max = $maxfilesize * 1024;
        parent::set($dir, $allow_exts);
    }

    public function execute($field, $rename = false)
    {
        if (!isset($_FILES[$field]) || !$_FILES[$field]['name']) {
            $this->_error_code = 4;
            return false;
        }

        $info = [];
        if (is_array($_FILES[$field]['name'])) {
            foreach ($_FILES[$field]['name'] as $key => $name) {
                $file   = [
                    'tmp_name' => $_FILES[$field]['tmp_name'][$key],
                    'name'     => $_FILES[$field]['name'][$key],
                    'size'     => $_FILES[$field]['size'][$key],
                    'type'     => $_FILES[$field]['type'][$key],
                    'error'    => $_FILES[$field]['error'][$key],
                    'rename'   => $rename
                ];
                $result = $this->move($file);
                if (!$result) {
                    return false;
                }
                $info[] = $result;
            }
        } else {
            $file           = $_FILES[$field];
            $file['rename'] = $rename;
            $info           = $this->move($file);
        }
        return $info;
    }

    public function error()
    {
        $error = [
            1  => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
            2  => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
            3  => '文件只有部分被上传',
            4  => '没有文件被上传',
            6  => '找不到临时文件夹',
            7  => '文件写入失败',
            10 => '上传文件格式不符合要求',
            11 => '重命名文件格式不符合要求',
            12 => '上传文件太大',
            13 => '文件移动出错',
        ];
        return $error[$this->_error_code];
    }

    private function check($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errno = $file['error'];
            return false;
        }

        if ($file['size'] > $this->filesize_max) {
            $this->errno = 12;
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errno = 13;
            return false;
        }

        if ($this->allow_exts != '*' && !preg_match("/\.($this->allow_exts)$/i", $file['name'])) {
            $this->errno = 10;
            return false;
        }
        return true;
    }

    private function move($file)
    {
        if (!$this->check($file)) {
            return false;
        }

        $fileext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($file['rename'] === true) {
            $this->filename_set(null, $fileext);
        } elseif ($file['rename'] === false) {
            $this->filename_set($file['name']);
        } else {
            $this->filename_set($file['rename']);
        }

        $this->_target = $this->_dir . $this->_filename;
        if (!@move_uploaded_file($file['tmp_name'], $this->_target)) {
            $this->errno = 13;
            return false;
        }
        @unlink($file['tmp_name']);

        $filepath = $this->format(dirname($this->_target), false) . '/';
        $info     = [
            'alias'    => $file['name'],
            'filename' => $this->_filename,
            'filepath' => $filepath,
            'filemime' => $file['type'],
            'fileext'  => $fileext,
            'filesize' => $file['size']
        ];
        if ($this->image_is($file['name'])) {
            $info['isimage'] = 1;
        }
        $this->_files[] = $info;
        return $info['filepath'] . $info['filename'];
    }
}
