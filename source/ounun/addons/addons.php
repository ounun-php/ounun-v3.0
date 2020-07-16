<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;

use ounun\db\pdo;

abstract class addons
{
    /** @var string 插件名称 */
    const Addon_Name = '[基类]';

    /** @var string 插件tag */
    const Addon_Tag = '_base';

    /** @var string 菜单tag */
    const Menu_Tag = '_base';

    /** @var string 系统 */
    const Is_System = false;

    /** @var array 依赖插件 */
    public static array $addons_require = [];

    /** @var array 插件子模块(主要是子类继承用) */
    protected static array $_addons_view_class = [];

    /** @return array 插件子模块 */
    static public function addons_view_class()
    {
        return static::$_addons_view_class;
    }

    /**
     * 环境配制
     * @return array
     */
    static public function verb_config()
    {
        return [];
    }
}
