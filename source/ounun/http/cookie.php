<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\http;


class cookie
{
    private $_prefix = '';
    private $_path = '/';
    private $_domain = '';

    public function __construct($prefix = 'cc_', $path = '/', $domain = '')
    {
        $this->_prefix = $prefix;
        $this->_path = $path;
        $this->_domain = $domain;
    }

    function set($var, $value = null, $time = 0)
    {
        if (is_null($value)) {
            $time = time() - 3600;
        } elseif ($time > 0 && $time < 31536000){
            $time += time();
        }
        $s = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
        $var = $this->_prefix.$var;
        $_COOKIE[$var] = $value;
        if(is_array($value)) {
            foreach($value as $k=>$v) {
                setcookie($var.'['.$k.']', $v, $time, $this->_path, $this->_domain, $s);
            }
        } else {
            setcookie($var, $value, $time, $this->_path, $this->_domain, $s);
        }
    }

    function get($var)
    {
        $var = $this->_prefix.$var;
        return isset($_COOKIE[$var]) ? $_COOKIE[$var] : false;
    }
}
