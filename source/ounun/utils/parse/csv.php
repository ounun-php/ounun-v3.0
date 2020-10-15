<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\utils\parse;

class csv
{
    public function parse(string $config, bool $is_head_field = true)
    {
        $dataset = [];
        $fields  = [];
        if (is_file($config)) {
            $handle = fopen($config, 'r');
            if ($is_head_field) {
                while (($data = fgetcsv($handle)) !== false) {
                    if ($fields) {
                        $dataset[] = array_combine($fields, $data);
                    } else {
                        $fields = $data;
                    }
                }
            } else {
                while (($data = fgetcsv($handle)) !== false) {
                    $dataset[] = $data;
                }
            }
        } else {
            $datas = str_getcsv($config, "\n");
            if ($is_head_field) {
                foreach ($datas as &$data) {
                    if ($fields) {
                        $dataset[] = array_combine($fields, str_getcsv($data));
                    } else {
                        $fields = str_getcsv($data);
                    }
                }
            } else {
                foreach ($datas as &$data) {
                    $dataset[] = str_getcsv($data);
                }
            }
        }
        return $dataset;
    }
}
