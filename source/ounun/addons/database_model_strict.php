<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


/**
 * 数据库模型
 * Class database_model
 *
 * @package ounun\addons
 */
class database_model_strict extends database_model
{
    /** @var self 数据库实例 */
    protected static $_instance;

    /** @var array 数据表结构 */
    public array $options = [
        'fields'          => [],
        'primary'         => '',
        'readonly'        => [],
        'create_autofill' => [],
        'update_autofill' => [],
        'filters_input'   => [],
        'filters_output'  => [],
        'validators'      => [],
        'options'         => []
    ];


    /** @var array|string[][]  */
    public array $event = [
        'guest'    => ['after_add', 'after_edit', 'after_get', 'after_delete'],
        'question' => ['after_get'],
        'html'     => [
            'html_write',
            'after_add', 'after_edit', 'after_publish', 'after_unpublish', 'after_restore', 'after_pass', 'after_remove',
            'before_delete', 'before_move', 'after_move',
            'state', 'review', 'notice', 'picture'
        ],
        'search'   => [
            'after_add', 'after_edit', 'after_publish', 'after_unpublish', 'after_restore', 'after_pass', 'after_remove', 'after_delete',
            'state', 'review', 'notice', 'picture'
        ],
    ];

    /**
     * 更新数据
     *
     * @param string $where_str
     * @param array $where_bind
     * @param array $data
     * @param string|null $table
     * @return int
     */
//    public function update(string $where_str, array $where_bind, array $data, ?string $table = null)
//    {
//        $table ??= $this->table;
//        return $this->table($table)->where($where_str, $where_bind)->update($data);
//    }

    /**
     * 插入数据
     *
     * @param array $data
     * @param string|null $table
     * @return int
     */
//    public function insert(array $data, ?string $table = null)
//    {
//        $table ??= $this->table;
//        return $this->db->table($table)->insert($data);
//    }

    /**
     * 删除
     *
     * @param string $where_str
     * @param array $where_bind
     * @param int $limit 删除limit默认为1
     * @param string|null $table
     * @return int
     */
//    public function delete(string $where_str, array $where_bind, int $limit = 1, ?string $table = null)
//    {
//        $table ??= $this->table;
//        return $this->db->table($table)->where($where_str, $where_bind)->delete($limit);
//    }

    /**
     * 得到一条数据数组
     *
     * @param string $where_str
     * @param array $where_bind
     * @param string|null $table
     * @param string $field
     * @return array
     */
//    public function column_one2(string $where_str, array $where_bind, ?string $table = null, string $field = '*')
//    {
//        $table ??= $this->table;
//        return $this->table($table)
//            ->field($field)
//            ->where($where_str, $where_bind)
//            ->limit(1)
//            ->column_one();
//    }

}
