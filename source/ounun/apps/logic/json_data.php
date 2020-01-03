<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\apps\logic;


class json_data extends \ounun\apps\logic
{
    /**  @var array  */
    public $tags = [];

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
            $table = $this->_table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $pg  = new \ounun\page\base( $this->_db, $table, $url, $where, $page_config);
        $ps  = $pg->initialize($page, $title,$end_index);
        $db  = $this->_db->table($table);
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
        $datas = $db->limit($pg->limit_rows(), $pg->limit_offset() )->column_all();

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
            $table = $this->_table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $db = $this->_db->table($table)->field($fields);
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
        }else{
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
     * @param array  $rs
     * @param array  $extend_fields
     * @param bool   $is_multi
     */
    protected function _lists_decode(array &$rs ,array $extend_fields = [], bool $is_multi = true)
    {
        if($is_multi) {
            foreach ($rs as &$v) {
                $this->_lists_decode($v,$extend_fields,false);
            }
        }else{
            if($rs['contents']) {
                $rs['contents'] = json_decode_array($rs['contents']);
            }
            if($rs['extend']) {
                $rs['extend'] = json_decode_array($rs['extend']);
            }
            if($extend_fields && is_array($extend_fields)){
                foreach ($extend_fields as $field){
                    if($field && $rs[$field]){
                        $rs[$field] = json_decode_array($rs[$field]);
                    }
                }
            }
        }
    }
}
