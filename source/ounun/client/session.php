<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\client;


class session
{
    /** @var string  session key * */
    protected string $_key = '';

    /**
     * session constructor.
     * @param string|null $session_key
     */
    public function __construct(?string $session_key = null)
    {
        $this->key_set($session_key);
    }

    /**
     * @param string|null $session_key
     */
    public function key_set(?string $session_key = null)
    {
        $this->_key = $session_key ?? 'adm';
    }

    /**
     * @return string
     */
    public function key_get()
    {
        return $this->_key;
    }

    /**
     * 内部 获取key的值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $key = $this->_key . $key;
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * 内部 设定key
     *
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function set(string $key, $val)
    {
        $_SESSION[$this->_key . $key] = $val;
        return true;
    }

    /**
     * 内部 删除key的值
     *
     * @param string $key
     * @return bool
     */
    public function del(string $key)
    {
        unset($_SESSION[$this->_key . $key]);
        return true;
    }

    /**
     * 清理
     *
     * @return bool
     */
    public function clean()
    {
        if ($this->_key && $_SESSION) {
            $key_len = strlen($this->_key);
            foreach ($_SESSION as $k => $v) {
                if ($this->_key == substr($k, 0, $key_len)) {
                    unset($_SESSION[$k]);
                }
            }
        }
        return true;
    }
}
