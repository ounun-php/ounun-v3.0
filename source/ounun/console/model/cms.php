<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\console\model;

abstract class cms
{
    /** @var self 实例 */
    protected static $instance;

    /**
     * @param \ounun\db\pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(\ounun\db\pdo $db = null): self
    {
        if (empty(static::$instance)) {
            if (empty($db)) {
                $db = \v::db_v_get();
            }
            static::$instance = new static($db);
        }
        return static::$instance;
    }

    /** @var \ounun\db\pdo */
    public $db;

    /** @var string  */
    public $table = '';

    /**  @var array  */
    public $tags = [];

    /**
     * cms constructor.
     * @param \ounun\db\pdo $db
     */
    public function __construct(\ounun\db\pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        static::$instance = $this;
    }

    /**
     * @param string $table
     * @param array $where
     * @param array $order
     * @param string $url
     * @param int $page
     * @param array $page_config
     * @param string $title
     * @param string $field
     * @param bool $end_index
     * @return array
     */
    public function lists(array $where, array $order, string $url, int $page, array $page_config, string $title = "", string $field = '*',string $table = '', bool $end_index = true)
    {
        if(empty($table)){
            $table = $this->table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $pg  = new \ounun\page\base_max( $this->db, $table, $url, $where, $page_config);
        $ps  = $pg->init($page, $title,$end_index);
        $db  = $this->db->table($table);
        if($field){
            $db->field($field);
        }
        if($where && $where['str']){
            $db->where($where['str'], $where['bind']);
        }
        if($order && is_array($order)){
            foreach ($order as $v){
                $db->order($v['field'], $v['order']);
            }
        }
        $datas = $db->limit($pg->limit_rows(), $pg->limit_start() )->column_all();

        $this->_lists_decode($datas);
        // echo $this->db->sql()."\n";
        return [$datas,$ps];
    }


    /**
     * @param string $table
     * @param int $count
     * @param int $start
     * @param array $order
     * @param array $where
     * @param string $addon_tag
     * @return array
     */
    public function lists_simple(int $count = 4, int $start = 0, array $order = [], array $where = [],string $fields = '*',string $table = '')
    {
        if(empty($table)){
            $table = $this->table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $db = $this->db->table($table)->field($fields);
        if($order && is_array($order)){
            foreach ($order as $v){
                $db->order($v['field'],$v['order']);
            }
        }
        if($where && is_array($where) && $where['str']){
            $db->where($where['str'],$where['bind']);
        }
        if($count > 0 || $start > 0 ){
            $rs = $db->limit($count,$start)->column_all();
        }else
        {
            $rs = $db->column_all();
        }

        // echo $this->db->sql()."\n";
        // $rs = [];
        $this->_lists_decode($rs);
        // echo $this->db->sql()."\n";
        return $rs;
    }

    
    /**
     * json数据decode
     * @param array $rs
     * @param bool $is_multi
     * @return mixed
     */
    protected function _lists_decode(array &$rs , bool $is_multi = true){
        if($is_multi) {
            foreach ($rs as &$v) {
                $this->_lists_decode($v,false);
            }
        }else{
            if($rs['tags']){
                $rs['tags'] = \addons\tag\apps::decode($rs['tags']);
                // print_r($v['tags']);
                if($rs['tags'] && is_array($rs['tags'])){
                    foreach ($rs['tags'] as $v1){
                        $this->tags[$rs['tag_id']] = $v1;
                    }
                }
            }
            if($rs['contents']){
                $rs['contents'] = json_decode_array($rs['contents']);
            }
            if($rs['extend']){
                $rs['extend'] = json_decode_array($rs['extend']);
            }
        }
    }
}