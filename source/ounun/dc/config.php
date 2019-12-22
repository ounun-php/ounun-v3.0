<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\dc;

use ounun\dc;

class config
{
    /** @var array<\ounun\cache\config> */
    static protected $_inst = [];

    /**
     * @param \ounun\db\pdo $db
     * @param string $tag
     * @param array $config
     * @param string $config_key
     * @return $this
     */
    static public function i(string $tag = 'tag', array $config = [], \ounun\db\pdo $db = null)
    {
        if (empty(static::$_inst[$tag])) {
            static::$_inst[$tag] = new static($tag, $config,  $db);
        }
        return static::$_inst[$tag];
    }

    /** @var array */
    protected $_cache_value = [];

    /** @var dc */
    protected $_dc;

    /** @var \ounun\db\pdo */
    protected $_db;

    /** @var int 最后更新时间，大于这个时间数据都过期 */
    protected $_last_time;

    /**
     * config constructor.
     * @param \ounun\db\pdo $db
     * @param array $config
     * @param string $tag
     */
    public function __construct(string $tag, array $config, \ounun\db\pdo $db)
    {
        $config['format_string']  = false;
        $this->_db = $db;
        $this->_dc = dc::i($tag,$config);
    }

    /**
     * @param int $last_time
     */
    public function last_modify_set(int $last_time)
    {
        $this->_last_time = $last_time;
    }

    /**
     * @param $tag_key
     */
    protected function _clean($tag_key)
    {
        $this->_cache_value[$tag_key] = null;
        unset($this->_cache_value[$tag_key]);
        $this->_dc->fast_del($tag_key);
    }

    /**
     * @param $tag_key
     * @param $mysql_method
     * @param null $args
     * @return mixed
     */
    protected function _data($tag_key, $mysql_method, $args = null)
    {

        if (!$this->_cache_value[$tag_key]) {
            $this->_dc->set_key($tag_key);
            $c = $this->_dc->get();
            //$this->_cd[$tag_key]->mtime = time();
            //debug_header('$last_modify',$last_modify,true);
            //debug_header('$this_mtime',$this->_cd[$tag_key]->mtime,true);
            if ($c == null) {
                //debug_header('$this_mtime2',222,true);
                $this->_cache_value[$tag_key] = $this->$mysql_method($args);
                $this->_dc->set_key($tag_key);
                $this->_dc->set_value(['t' => time(), 'v' => $this->_cache_value[$tag_key]]);
                $this->_dc->set();
            } elseif (!is_array($c) || (int)$c['t'] < $this->_last_time) {
                // debug_header('$this_mtime3',3333,true);
                $this->_cache_value[$tag_key] = $this->$mysql_method($args);
                $this->_dc->set_key($tag_key);
                $this->_dc->set_value(['t' => time(), 'v' => $this->_cache_value[$tag_key]]);
                $this->_dc->set();
            } else {
                $this->_cache_value[$tag_key] = $c['v'];
            }
        }
        return $this->_cache_value[$tag_key];
    }
}
