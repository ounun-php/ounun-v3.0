<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\seo;

/**
 * 汉字转拼音
 */
class wei
{
    public static function wei($str): string
    {
        return strtr($str, data::wei);
    }

    public static function test()
    {
        echo count(data::wei), "<br />\n";
        $d = data::wei;
        $f = file_get_contents('42537.txt');
        $f = explode("\n", $f);
        foreach ($f as $v) {
            $v2     = explode('→', $v);
            $v3     = trim($v2[0]);
            $v4     = trim($v2[1]);
            $d[$v3] = $v4;
        }
        echo count($f), "<br />\n";
        echo count($d), "<br />\n<pre>";
        var_export($d);
    }
}
//WeiSEO::test();
