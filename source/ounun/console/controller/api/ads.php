<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\console\controller\api;

class ads extends \v
{
    /** @var array */
    static protected $ads = [];

    /**
     * 广告  Www - PC
     * @param $mod array
     */
    public function www($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['www']) . ";\n");
    }

    /**
     * 广告 Wap
     * @param $mod array
     */
    public function wap($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['wap']) . ";\n");
    }
}
