<?php
namespace ounun\db;


class dbkv
{
    private $handle;

    function __construct($storage = 'dba', $handler = 'flatfile')
    {
        require_once(dirname(__FILE__).DS.'storage.php');
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