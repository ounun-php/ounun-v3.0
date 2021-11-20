<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\client;


class session
{
    /** @var string  session prefix * */
    protected string $_prefix = '';

    /**
     * session constructor.
     *
     * @param string|null $session_key
     */
    public function __construct(?string $session_key = null)
    {
        $this->prefix_set($session_key);
    }

    /**
     * @param string|null $session_key
     */
    public function prefix_set(?string $session_key = null)
    {
        $this->_prefix = $session_key ?? 'adm';
    }

    /**
     * @return string
     */
    public function prefix_get(): string
    {
        return $this->_prefix;
    }

    /**
     * 内部 获取key的值
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->_prefix . $key;
        return $_SESSION[$key] ?? $default;
    }

    /**
     * 内部 设定key
     *
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function set(string $key, mixed $val): bool
    {
        $_SESSION[$this->_prefix . $key] = $val;
        return true;
    }

    /**
     * 内部 删除key的值
     *
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        unset($_SESSION[$this->_prefix . $key]);
        return true;
    }

    /**
     * 清理
     *
     * @return bool
     */
    public function clean(): bool
    {
        if(empty($_SESSION)){
            return true;
        }
        if(!is_array($_SESSION)){
            return false;
        }

        if($this->_prefix){
            $prefix_len = strlen($this->_prefix);
            foreach ($_SESSION as $k => $v) {
                if ($this->_prefix == substr($k, 0, $prefix_len)) {
                    unset($_SESSION[$k]);
                }
            }
        }else{
            foreach ($_SESSION as $k => $v) {
                unset($_SESSION[$k]);
            }
        }
        return true;
    }
}
