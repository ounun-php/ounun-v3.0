<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\apps;

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

    /** @var array 插件子模块(主要是子类继承用) */
    protected static array $_addons_view_class = [];

    /** @var array 依赖插件 */
    public static array $addons_require = [];

    /**
     * 加载模块
     * @param array $addons
     */
    static public function mount_multi(array $addons)
    {
        /** @var self $addon */
        foreach ($addons as $addon) {
            if (is_array($addon) && $addon['apps']) {
                static::mount_single($addon['apps'], (string)$addon['url'], (string)$addon['view_class'], (string)$addon['auto']);
            } else {
                trigger_error("Can't find addon_tag:" . json_encode_unescaped($addon), E_USER_ERROR);
            }
        }
    }

    /**
     * @param addons $addon_apps 插件类名tag
     * @param string $addon_url 插件网址目录
     * @param string $view_class 插件类名称
     * @param bool $is_auto_reg_subclass 插件是否自动注册旗下子类
     */
    static public function mount_single($addon_apps, string $addon_url, string $view_class = '', bool $is_auto_reg_subclass = false)
    {
        // addon
        \ounun::$routes_cache[$addon_url] = [
            'apps'       => $addon_apps,
            'url'        => $addon_url,
            'view_class' => $view_class,
            'auto'       => $is_auto_reg_subclass,
        ];
        //
        \ounun::addons_set($addon_apps);
        //
        if ($is_auto_reg_subclass && empty($view_class)) {
            $addons_view_class = $addon_apps::addons_view_class();
            if ($addons_view_class && is_array($addons_view_class)) {
                // $addon_url
                $addon_url = $addon_url ? $addon_url . '/' : '';
                /** @var addons $addon */
                foreach ($addons_view_class as $addon) {
                    if (is_array($addon) && $addon['view_class']) {
                        if ($addon && is_array($addon) && $addon['top']) {
                            $url = $addon['url'] ?? $addon['view_class'];
                        } else {
                            $url = $addon_url . $addon['view_class'];
                        }
                        \ounun::$routes_cache[$url] = [
                            'apps'       => $addon_apps,
                            'url'        => $url,
                            'view_class' => $addon['view_class'],
                            'auto'       => false,
                        ];
                    } else {
                        trigger_error("Can't find addon_tag:" . json_encode_unescaped($addon), E_USER_ERROR);
                    }
                }
            }
        } // if(empty(view_class)){
    }

    /**
     * 关连 插件
     * @param string $addon_apps
     * @return array
     */
    static public function addons_related(string $addon_apps = '')
    {
        if (empty($addon_apps)) {
            $addon_apps = static::class;
        }
        $addons = [];
        /** @var addons $addon */
        foreach (\ounun::$maps_installed_addons as $addon) {
            if (in_array($addon_apps, $addon::$addons_require)) {
                // print_r(['$addon_apps'=>$addon_apps,'$addon'=>$addon,'$addon::$addons_require'=>$addon::$addons_require]);
                $addons[] = $addon;
            }
        }
        return $addons;
    }

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
