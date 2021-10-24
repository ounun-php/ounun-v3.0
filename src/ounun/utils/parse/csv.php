<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\utils\parse;

class csv
{
    public function parse(string $config, bool $is_head_field = true): array
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
            $data_list = str_getcsv($config, "\n");
            if ($is_head_field) {
                foreach ($data_list as &$data) {
                    if ($fields) {
                        $dataset[] = array_combine($fields, str_getcsv($data));
                    } else {
                        $fields = str_getcsv($data);
                    }
                }
            } else {
                foreach ($data_list as &$data) {
                    $dataset[] = str_getcsv($data);
                }
            }
        }
        return $dataset;
    }
}
