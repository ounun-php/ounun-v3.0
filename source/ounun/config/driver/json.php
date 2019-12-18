<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\config\driver;


class json
{
    /**
     * @param $config
     * @return mixed
     */
    public function parse($config)
    {
        if (is_file($config)) {
            $config = file_get_contents($config);
        }
        return json_decode($config, true);
    }
}
