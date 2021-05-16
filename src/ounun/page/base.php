<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\page;


use ounun\db\pdo;

/**
 * 主要功能: 分頁,有問題問我吧,沒時間寫注
 * Class base_max
 *
 * @package ounun\page
 */
class base extends simple
{
    /**
     * 设定 函数
     * @param pdo $db
     * @param string $table
     * @param string $where_str
     * @param array $where_paras
     */
    public function db_set(pdo $db, string $table, string $where_str = '', array $where_paras = [])
    {
        $fn = function () use ($db,$table,$where_str,$where_paras){
            // 从数据库中得到数据总行数
            return $db->table($table)->where($where_str,$where_paras)->count_value();
        };
        $this->fn_total_set($fn);
    }
}
