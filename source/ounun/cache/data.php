<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\cache;


class data
{
    /**
     * 获得libs Data数据
     * @param string $data_mod
     * @param string $data_dir
     * @return mixed
     */
    function data(string $data_mod, string $data_dir)
    {
        $filename = "{$data_dir}data.{$data_mod}.ini.php";
        if (file_exists($filename)) {
            return require $filename;
        }
        return null;
    }
}