<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\db;


class kv
{
    private $handle;

    function __construct($storage = 'dba', $handler = 'flatfile')
    {
        require_once(dirname(__FILE__).'/'.'storage.php');
        $this->handle = & dbkv_storage::get_instance($storage, $handler);
    }

    function open($path, $mode = 'n')
    {
        return $this->handle->open($path, $mode);
    }

    function popen($path, $mode = 'n')
    {
        return $this->handle->popen($path, $mode);
    }

    function add($key, $value)
    {
        return $this->handle->add($key, $value);
    }

    function set($key, $value)
    {
        return $this->handle->set($key, $value);
    }

    function get($key)
    {
        return $this->handle->get($key);
    }

    function rm($key)
    {
        return $this->handle->rm($key);
    }

    function exists($key)
    {
        return $this->handle->exists($key);
    }

    function close()
    {
        return $this->handle->close();
    }
}