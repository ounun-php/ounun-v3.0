<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\utils;


class hide
{
    /**
     * IP隐藏第3段
     * @param $ip
     * @return string
     */
    static public function ipv4($ip)
    {
        $ip = explode('.', $ip);
        $ip[2] = '*';
        return implode('.', $ip);
    }
}
