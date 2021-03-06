<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\addons;


abstract class apps
{
    /** @var string 插件名称 */
    const Addon_Name = '[基类]';

    /** @var string 插件tag */
    const Addon_Tag = '_base';

    /** @var string|null 版本号 */
    const Addon_Version = null;

    /** @var string|null 版本号 */
    const Logo = null;

    /** @var string 菜单tag */
    const Menu_Tag = '_base';

    /** @var string 系统 */
    const Is_System = false;
}
