<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\apps;

class addons
{
    /** @var string 插件名称 */
    const Addon_Name = '[基类]';

    /** @var string 插件tag */
    const Addon_Tag  = '_base';

    /** @var string 菜单tag */
    const Menu_Tag = '_base';

    /** @var string 系统 */
    const Is_System = false;

    /** @var array 功能模块(插件) */
    public static $addons_apps     = [];

    /** @var array 插件子模块(主要是子类继承用) */
    public static $addons_subclass = [];

    /** @var self 实例 */
    protected static $_instance;

    /**
     * @return self 返回数据库连接对像
     */
    public static function i(): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * 加载模块
     * @param string $addons
     */
    static public function mount_multi(array $addons)
    {
        /** @var self $addon */
        foreach ($addons as $addon){
            if(is_array($addon) && $addon['apps'] && $addon['url']){
                static::mount_single($addon['apps'],(string)$addon['url'] ,(string)$addon['view_class'],(string)$addon['auto']);
//          }else if($addon){
//                $addon::mount_single($addon,$addon::Addon_Tag);
            }else{
                trigger_error("Can't find addon_tag:".json_encode_unescaped($addon), E_USER_ERROR);
            }
        }
    }

    /**
     * @param \ounun\apps\addons $addon_apps       插件类名tag
     * @param string $addon_url              插件网址目录
     * @param string $view_class             插件类名称
     * @param bool $is_auto_reg_subclass     插件是否自动注册旗下子类
     */
    static public function mount_single($addon_apps, string $addon_url, string $view_class = '', bool $is_auto_reg_subclass = false)
    {
        // addon
        \ounun::$routes_cache[$addon_url] = [
            'apps'        => $addon_apps,
            'url'         => $addon_url,
            //'addon_tag' => $addon_apps::Addon_Tag,
            'view_class'  => $view_class,
            'auto'        => $is_auto_reg_subclass,
        ];
        //
        static::apps_push($addon_apps);
        //
        if($is_auto_reg_subclass && empty($view_class)) {
            /** @var array $addons_subclass addon_subclass */
            $addons_subclass = $addon_apps::$addons_subclass;
            if($addons_subclass && is_array($addons_subclass)){
                // $addon_url
                $addon_url = $addon_url ? $addon_url.'/' : '';
                /** @var \ounun\apps\addons $addon */
                foreach ($addons_subclass as $addon){
                    if (is_array($addon) && $addon['view_class']) {
                        if ($addon && is_array($addon) && $addon['top']) {
                            $url = $addon['url']??$addon['view_class'];
                        } else {
                            $url = $addon_url.$addon['view_class'];
                        }
                        \ounun::$routes_cache[$url] = [
                            'apps'         => $addon_apps,
                            'url'          => $url,
                            // 'addon_tag' => $addon_apps::Addon_Tag,
                            'view_class'   => $addon['view_class'],
                            'auto'         => false,
                        ];
                    }else{
                        trigger_error("Can't find addon_tag:".json_encode_unescaped($addon), E_USER_ERROR);
                    }
                }
            }
        } // if(empty(view_class)){
    }

    /**
     * 菜单数据
     * @return array
     */
    static public function apps_menu_control()
    {
        $menu = [];
        return [static::Menu_Tag,$menu];
    }

    /**
     * 插件依赖
     * @return array
     */
    static public function require_addons()
    {
        $addons = [];
        return $addons;
    }

    /**
     * 环境配制
     * @return array
     */
    static public function env_config()
    {
        return [];
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $fields
     * @param bool $app      是否支持app各自设定
     * @param bool $global
     * @param bool $system
     * @param bool $multi
     * @param bool $multi_array
     * @param string $multi_key
     * @param mixed  $multi_value
     * @return array
     */
    public static function env_template(string $key, string $name, array $fields, bool $app = true, bool $global=false,bool $system=false,
                                        bool $multi=false, bool $multi_array = false, string $multi_key = '', $multi_value = null)
    {
        return [
            'name'   => $name,
            'global' => $global,
            'system' => $system,
            'app'    => $app,
            'key'    => $key,
            'fields' => $fields,

            'multi'         => $multi,
            'multi_array'   => $multi_array,
            'multi_key'     => $multi_key,
            'multi_value'   => $multi_value,
        ];
    }

    /**
     * @param string $field
     * @param string $name
     * @param string $type
     * @param $default
     * @param int $len_min
     * @param int $len_max
     * @param array $enum_value
     * @return array
     */
    public static function env_template_field(string $field, string $name, string $type, $default, int $len_min = 0, int $len_max = 32,array $enum_value=[])
    {
        return [
            'field'     => $field,
            'name'      => $name,
            'type'      => $type,
            'len_min'   => $len_min,
            'len_max'   => $len_max,
            'default'   => $default,
            'enum_value'=> $enum_value
        ];
    }



    /**
     * 公共配制数据
     * @param string $config_key
     * @param $default
     * @return mixed|string
     */
    public static function env_global(string $config_key,$default)
    {
        if(\ounun::$global && \ounun::$global[$config_key]){
            return  \ounun::$global[$config_key];
        }
        return $default;
    }

    /**
     * 公共配制数据(插件)
     * @param string $config_key
     * @param $default
     * @param string $addon_tag
     * @return mixed
     */
    protected static function env_global_addons(string $config_key,$default,string $addon_tag)
    {
        if(\ounun::$global_addons){
            $tag = \ounun::$global_addons[$addon_tag];
            if($tag && $tag[$config_key]){
                return $tag[$config_key];
            }
        }
        return $default;
    }

    /**
     * 添加$addon
     * @param string $addon_apps
     */
    static public function apps_push(string $addon_apps)
    {
        if($addon_apps && !in_array($addon_apps,self::$addons_apps)){
            array_push(self::$addons_apps,$addon_apps);
        }
    }
}
