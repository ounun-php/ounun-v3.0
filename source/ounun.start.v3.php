<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

use ounun\addons\addons;
use ounun\debug;
use ounun\template;

/**
 * 路由
 * @param $routes      array  目录路由表
 * @param $host        string 主机
 * @param $mod         array  目录数组
 * @param $default_app string 默认应用
 * @return string 应用
 */
class ounun
{
    /** @var string 默认模块名称 */
    const Def_Module = 'index';
    /** @var string 默认插件名称 */
    const Def_Addon = 'system';
    /** @var string 默认操作名称 */
    const Def_Method = 'index';

    /** @var string web 网页 */
    const App_Name_Web = 'web';
    /** @var string api 数据API接口 */
    const App_Name_Api = 'api';
    /** @var string control 控制后台 */
    const App_Name_Control = 'control';
    /** @var string process 异步进程 */
    const App_Name_Process = 'process';
    /** @var string command 后台任务（定时任务） */
    const App_Name_Command = 'command';
    /** @var array 应用名称组 */
    const App_Names = [
        self::App_Name_Web,
        self::App_Name_Api,
        self::App_Name_Control,
        self::App_Name_Process,
        self::App_Name_Command,
    ];

    /** @var v */
    public static v $view;

    /** @var array 公共配制数据 */
    public static array $global = [];
    /** @var array 公共配制数据(插件) */
    public static array $global_addons = [];
    /** @var array 公共配制数据(应用) */
    public static array $global_apps = [];

    /** @var array DB配制数据 */
    public static array $database = [];
    /** @var string 默认 数据库 */
    public static string $database_default = '';

    /** @var array 命令s */
    public static array $commands = [];

    /** @var array 自动加载路径paths */
    public static array $maps_paths = [];
    /** @var array 自动加载路径maps */
    public static array $maps_class = [];
    /** @var array 已安装的功能模块(插件) */
    public static array $maps_installed_addons = [];
    /** @var array 插件addon      映射数据 */
    public static array $maps_addon = [];

    /** @var string 当前插件addon  网址Url前缀Path(URL) */
    public static string $addon_path_curr = '';

    /** @var string 当前APP */
    public static string $app_name = '';
    /** @var string 当前APP Url前缀Path */
    public static string $app_path = '';
    /** @var string 域名Domain */
    public static string $app_domain = '';
    /** @var string 项目代号 */
    public static string $app_code = '';
    /** @var string 当前版本号(本地cache) 1.1.1 */
    public static string $app_version = '1.1.1';
    /** @var string 当前app之前通信内问key */
    public static string $app_key_communication_private = '';

    /** @var string 当前面页(文件名)  Page Base */
    public static string $page_base_file = '';
    /** @var string 当前面页(网址)    Page URL */
    public static string $page_url = '';

    /** @var string Www Page */
    public static string $page_www = '';
    /** @var string Mobile Page */
    public static string $page_wap = '';
    /** @var string Mip Page */
    public static string $page_mip = '';

    /** @var string Www Root_Url */
    public static string $root_www = '';
    /** @var string Wap Root_Url */
    public static string $root_wap = '';
    /** @var string Mip Root_Url */
    public static string $root_mip = '';
    /** @var string Api Root_Url */
    public static string $root_api = '';

    /** @var string Res URL */
    public static string $url_res = '';
    /** @var string Static URL */
    public static string $url_static = '';
    /** @var string StaticG URL */
    public static string $url_static_g = '';
    /** @var string Upload URL */
    public static string $url_upload = '';

    /** @var string 应用模板类型 pc www */
    public static string $tpl_type = 'pc';
    /** @var string 应用模板类型[默认] */
    public static string $tpl_type_default = 'pc';
    /** @var string 模板-样式 */
    public static string $tpl_style = 'default';
    /** @var string 模板-样式[默认] */
    public static string $tpl_style_default = 'default';

    /** @var array Template view目录 */
    public static array $tpl_dirs = [];
    /** @var array 模板替换数据组 */
    public static array $tpl_replace_array = [];

    /** @var string 当前语言 */
    public static string $lang = 'zh_cn';
    /** @var string 默认语言 */
    public static string $lang_default = 'zh_cn';
    /** @var array 支持的语言 */
    public static array $lang_supports = [
        "en_us" => "English", // "zh"=>"繁體中文",
        "zh_cn" => "简体中文", // "ja"=>"日本語",
    ];
    /** @var array 站点SEO */
    public static array $seo_site = [
        'sitename'    => '',
        'keywords'    => '',
        'description' => '',
        'slogan'      => ''
    ];

    /**
     * 添加命令行
     * @param array $commands
     */
    static public function commands_set(array $commands)
    {
        foreach ($commands as $command) {
            if ($command && !in_array($command, \ounun::$commands)) {
                \ounun::$commands[] = $command;
            }
        }
    }

    /**
     * 本地环境变量设定 (应用)
     * @param string $app_name
     */
//    static public function environment_app_set(string $app_name)
//    {
//        // 为空时直接返回
//        if (empty($app_name)) {
//            return;
//        }
//        $config_ini = static::$global_apps[$app_name];
//
//        // print_r(['$app_name'=>$app_name,'$config_ini'=>$config_ini]);
//        if ($config_ini) {
//            static::environment_set($config_ini);
//        }
//    }

    /**
     * 本地环境变量设定
     * @param array $config_ini
     */
//    static public function environment_set(array $config_ini = [])
//    {
//        // 为空时直接返回
//        if (empty($config_ini)) {
//            return;
//        }
//
//        // 添加App路径(根目录)
//        $key = 'paths';
//        if (isset($config_ini[$key])) {
//            $vs = $config_ini[$key];
//
//            if ($vs && is_array($vs)) {
//                foreach ($vs as $v) {
//                    if (is_array($v) && $v['path']) {
//                        static::path_set($v['path'], $v['is_auto_helper']);
//                    }
//                }
//            } else {
//                if (file_exists(Dir_Root)) {
//                    static::path_set(Dir_Root, true);
//                }
//                if (file_exists(Dir_Vendor . 'cms.cc/')) {
//                    static::path_set(Dir_Vendor . 'cms.cc/', true);
//                }
//            }
//        }
//
//        // app数据
//        $key = 'apps';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                $routes_default = $config['default'] ?? [];
//                unset($config['default']);
//                if ($config && $routes_default) {
//                    static::apps_set($config, $routes_default, []);
//                }
//            }
//        }
//
//        // 挂载模块路由
//        $key = 'routes';
//        if (isset($config_ini[$key])) {
//            $addons = $config_ini[$key];
//            if ($addons && is_array($addons)) {
//                addons::mount_multi($addons);
//            }
//        }
//
//        // 域名&项目代号&当前app之前通信内问key
//        $key = 'domain';
//        if (isset($config_ini[$key])) {
//            $vs = $config_ini[$key];
//            if ($vs && is_array($vs)) {
//                static::domain_set($vs['domain'], $vs['code'], $vs['version'], $vs['key']);
//            }
//        }
//
//        // 统计 / 备案号 / Baidu / xzh / 配制cache_file
//        $key = 'global';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::global_set($config);
//            }
//        }
//
//        // 设定模板目录
//        $key = 'template_paths';
//        if (isset($config_ini[$key])) {
//            $tpl_dirs = $config_ini[$key];
//            if ($tpl_dirs && is_array($tpl_dirs)) {
//                static::template_paths_set($tpl_dirs);
//            }
//        }
//
//        // html变量替换
//        $key = 'template_array';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::template_array_set($config);
//            }
//        }
//
//        // 配制database
//        $key = 'databases';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::database_set($config, $config_ini['database_default']);
//            }
//        }
//
//        // 设定语言 & 设定支持的语言
//        $key = 'lang';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            if ($config && is_array($config)) {
//                static::lang_set('', $config['default'], $config['support']);
//            }
//        }
//
//        // 设定路由数据
//        $key = 'urls';
//        if (isset($config_ini[$key])) {
//            $urls = $config_ini[$key];
//            if ($urls && is_array($urls)) {
//                static::urls_set($urls['root_www'], $urls['root_wap'], $urls['root_mip'], $urls['root_api'], $urls['url_res'], $urls['url_upload'], $urls['url_static'], $urls['url_static_g']);
//            }
//        }
//
//        // 设定站点页面SEO
//        $key = 'seo_site';
//        if (isset($config_ini[$key])) {
//            $config = $config_ini[$key];
//            static::seo_site_set($config['sitename'], $config['keywords'], $config['description'], $config['slogan']);
//        }
//
////        // 设定站点页面SEO
////        $key = 'seo_page';
////        if (isset($config_ini[$key])) {
////            $config = $config_ini[$key];
////            static::seo_page_set((string)$config['title'], (string)$config['keywords'], (string)$config['description'], (string)$config['h1'], (string)$config['etag']);
////        }
//
//        // 公共配制数据(应用)
////        $key = '__app__';
////        if (isset($config_ini[$key])) {
////            $configs = $config_ini[$key];
////            if ($configs && is_array($configs)) {
////                foreach ($configs as $app_name => $config) {
////                    if ($config && is_array($config)) {
////                        static::global_addons_set($config, '', $app_name);
////                    }
////                }
////            }
////        } // end if
//
//        // 公共配制数据(插件)
////        $key = '__addons__';
////        if (isset($config_ini[$key])) {
////            $configs = $config_ini[$key];
////            if ($configs && is_array($configs)) {
////                foreach ($configs as $addon_tag => $config) {
////                    if ($config && is_array($config)) {
////                        static::global_addons_set($config, $addon_tag, '');
////                    }
////                }
////            }
////        } // end if
//    }

    /**
     * 设定语言 & 设定支持的语言
     * @param string $lang
     * @param string $lang_default
     * @param array $lang_support_list 设定支持的语言
     */
    static public function lang_set(string $lang = '', string $lang_default = '', array $lang_support_list = [])
    {
        if ($lang) {
            static::$lang = $lang;
        }
        if ($lang_default) {
            static::$lang_default = $lang_default;
        }
        if ($lang_support_list && is_array($lang_support_list)) {
            foreach ($lang_support_list as $lang => $lang_name) {
                static::$lang_supports[$lang] = $lang_name;
            }
        }
    }

    /**
     * 设定站点的SEO
     * @param string $sitename
     * @param string $keywords
     * @param string $description
     * @param string $slogan
     */
    static public function seo_site_set(string $sitename = '', string $keywords = '', string $description = '', string $slogan = '')
    {
        $sitename && static::$seo_site['sitename'] = $sitename;
        $keywords && static::$seo_site['keywords'] = $keywords;
        $description && static::$seo_site['description'] = $description;
        $slogan && static::$seo_site['slogan'] = $slogan;
    }

    /**
     * 设定页面的SEO
     * @param string $title
     * @param string $keywords
     * @param string $description
     * @param string $h1
     * @param string $etag
     */
    static public function seo_page_set(string $title = '', string $keywords = '', string $description = '', string $h1 = '', string $etag = '')
    {
        $seo_page = [];
        $title && $seo_page['{$seo_title}'] = $title;
        $keywords && $seo_page['{$seo_keywords}'] = $keywords;
        $description && $seo_page['{$seo_description}'] = $description;
        $h1 && $seo_page['{$seo_h1}'] = $h1;
        $etag && $seo_page['{$seo_etag}'] = $etag;
        $seo_page && static::$tpl_replace_array = array_merge(static::$tpl_replace_array, []);
    }

    /**
     * 设定公共配制数据
     * @param array $config
     */
    static public function global_set(array $config = [])
    {
        if ($config) {
            foreach ($config as $key => $value) {
                static::$global[$key] = $value;
            }
        }
    }

    /**
     * 设定公共配制数据(应用)
     * @param array $config
     * @param string $app_name
     */
    static public function global_apps_set(array $config = [], string $app_name = '')
    {
        if ($config) {
            $app_name ??= static::$app_name;
            if (!isset(static::$global_apps[$app_name])) {
                static::$global_apps[$app_name] = [];
            } elseif (is_array(static::$global_apps[$app_name])) {
                static::$global_apps[$app_name] = [];
            }
            foreach ($config as $key => $value) {
                static::$global_apps[$app_name][$key] = $value;
            }
        } // end $config
    }

    /**
     * 设定公共配制数据(插件)
     * @param array $config
     * @param string $addon_tag
     */
    static public function global_addons_set(array $config = [], string $addon_tag = '')
    {
        if ($config) {
            if ($addon_tag) {
                if (!isset(static::$global_addons[$addon_tag])) {
                    static::$global_addons[$addon_tag] = [];
                } elseif (is_array(static::$global_addons[$addon_tag])) {
                    static::$global_addons[$addon_tag] = [];
                }
                foreach ($config as $key => $value) {
                    static::$global_addons[$addon_tag][$key] = $value;
                }
            }
        } // end $config
    }

    /**
     * 公共配制数据
     * @param string $config_key
     * @param $default
     * @return mixed|string
     */
    public static function global_get(string $config_key, $default)
    {
        if (\ounun::$global && \ounun::$global[$config_key]) {
            return \ounun::$global[$config_key];
        }
        return $default;
    }

    /**
     * 公共配制数据(应用)
     * @param string $key
     * @param $default
     * @param string $app_name
     * @return mixed
     */
    public static function global_apps_get(string $key, $default, string $app_name = '')
    {
        if (\ounun::$global_apps) {
            $app_name ??= static::$app_name;
            $tag      = \ounun::$global_apps[$app_name];
            if ($tag && $tag[$key]) {
                return $tag[$key];
            }
        }
        return $default;
    }

    /**
     * 公共配制数据(插件)
     * @param string $key
     * @param mixed $default
     * @param string $addon_tag
     * @return mixed
     */
    public static function global_addons_get(string $key, string $addon_tag, $default)
    {
        if (\ounun::$global_addons) {
            $tag = \ounun::$global_addons[$addon_tag];
            if ($tag && $tag[$key]) {
                return $tag[$key];
            }
        }
        return $default;
    }

    /**
     * 设定DB配制数据
     * @param array $database_config
     * @param string $database_default
     */
    static public function database_set(array $database_config = [], string $database_default = '')
    {
        if ($database_config) {
            foreach ($database_config as $db_key => $db_cfg) {
                static::$database[$db_key] = $db_cfg;
            }
        }
        if ($database_default) {
            static::$database_default = $database_default;
        }
    }

    /**
     * 默认 数据库
     * @return string
     */
    static public function database_default_get()
    {
        if (empty(static::$database_default)) {
            static::$database_default = static::$app_name;
        }
        return static::$database_default;
    }

    /**
     * 添加$addon
     * @param string $addon_apps
     */
    static public function addons_set(string $addon_apps)
    {
        if ($addon_apps && !in_array($addon_apps, static::$maps_installed_addons)) {
            array_push(static::$maps_installed_addons, $addon_apps);
        }
    }

    /**
     * 添加App路径(根目录)
     * @param string $path_root
     * @param string $app_name
     * @param bool $is_auto_helper
     */
    static public function path_set(string $path_root, bool $is_auto_helper = false, string $app_name = '')
    {
        if (empty($path_root)) {
            return;
        }

        /** src-0 \         自动加载 */
        ounun::load_class_set($path_root . 'src/', '', false);
        /** src-0 \addons   自动加载  */
        ounun::load_class_set($path_root . 'addons/', 'addons', true);

        /** 加载helper */
        if ($is_auto_helper) {
            is_file($path_root . 'app/helper.php') && require $path_root . 'app/helper.php';
            if ($app_name) {
                is_file($path_root . 'app/helper.' . $app_name . '.php') && require $path_root . 'app/helper.' . $app_name . '.php';
            }
        }
    }

    /**
     * 设定地址
     * @param string $root_www
     * @param string $root_wap
     * @param string $root_mip
     * @param string $root_api
     * @param string $url_res
     * @param string $url_static
     * @param string $url_upload
     * @param string $url_static_g
     */
    static public function urls_set(string $root_www, string $root_wap, string $root_mip, string $root_api,
                                    string $url_res, string $url_upload, string $url_static, string $url_static_g)
    {
        /** Www URL */
        static::$root_www = $root_www;
        /** Wap URL Mobile */
        static::$root_wap = $root_wap;
        /** Mip URL */
        static::$root_mip = $root_mip;
        /** Api URL */
        static::$root_api = $root_api;

        /** Res URL */
        static::$url_res = $url_res;
        /** Upload URL */
        static::$url_upload = $url_upload;
        /** Static URL */
        static::$url_static = $url_static;
        /** StaticG URL */
        static::$url_static_g = $url_static_g;
    }

    /**
     * 域名&项目代号&当前app之前通信内问key
     * @param string $app_domain
     * @param string $app_code
     * @param string $app_version
     * @param string $app_key_communication_private
     */
    static public function domain_set(string $app_domain = '', string $app_code = '', string $app_version = '', string $app_key_communication_private = '')
    {
        /** 项目主域名 */
        $app_domain && static::$app_domain = $app_domain;
        /** 项目代号 */
        $app_code && static::$app_code = $app_code;
        /** 当前版本号(本地cache) 1.1.1 */
        $app_version && static::$app_version = $app_version;
        /** 当前app之前通信内问key */
        $app_key_communication_private && static::$app_key_communication_private = $app_key_communication_private;
    }

    /**
     * 设定 模板及模板根目录
     * @param array $tpl_dirs 模板根目录
     *
     * @param string $tpl_style 风格
     * @param string $tpl_style_default 风格(默认)
     * @param string $tpl_type 类型
     * @param string $tpl_type_default 类型(默认)
     */
    static public function template_paths_set(array $tpl_dirs = [],
                                              string $tpl_style = '', string $tpl_style_default = '',
                                              string $tpl_type = '', string $tpl_type_default = '')
    {
        // 模板根目录
        if ($tpl_dirs && is_array($tpl_dirs)) {
            foreach ($tpl_dirs as $tpl_dir) {
                // print_r(['__LINE__'=>__LINE__,'$tpl_dir'=>$tpl_dir]);
                if (!in_array($tpl_dir, static::$tpl_dirs) && is_dir($tpl_dir['path'])) {
                    // print_r(['__LINE__'=>__LINE__,'$tpl_dir'=>$tpl_dir]);
                    static::$tpl_dirs[] = $tpl_dir;
                }
            }
        }

        // 风格
        $tpl_style && static::$tpl_style = $tpl_style;
        // 风格(默认)
        $tpl_style_default &&  static::$tpl_style_default = $tpl_style_default;

        // 类型
        if ($tpl_type) {
            static::$tpl_type = $tpl_type;
        }
        // 类型(默认)
        if ($tpl_type_default) {
            static::$tpl_type_default = $tpl_type_default;
        }
    }

    /**
     * 设定模板替换
     * @param array $data
     */
    static public function template_array_set(?array $data)
    {
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                static::$tpl_replace_array[$key] = $value;
            }
        }
    }

    /**
     * 设定模板替换
     * @param string $key
     * @param string $value
     */
    static public function template_replace_str_set(string $key, string $value)
    {
        static::$tpl_replace_array[$key] = $value;
    }

    /**
     * 赋值(默认) $seo + $url
     * @return array
     */
    static public function template_replace_str_get()
    {
        return array_merge([
            '{$page_url}'  => static::$page_url,      // $lang/$app_path/$base_url,
            '{$page_file}' => static::$page_base_file,// 基础url,
            // 根目录/面面路径
            '{$page_www}'  => static::$page_www,
            '{$page_wap}'  => static::$page_wap,
            '{$page_mip}'  => static::$page_mip,
            // 根目录
            '{$root_www}'  => static::$root_www,
            '{$root_wap}'  => static::$root_wap,
            '{$root_mip}'  => static::$root_mip,
            '{$root_api}'  => static::$root_api,

            '{$root_res}'         => static::$url_res,
            '{$root_upload}'      => static::$url_upload, '/public/uploads/' => static::$url_upload,
            '{$root_static}'      => static::$url_static, '/public/static/' => static::$url_static,
            '{$root_static_g}'    => static::$url_static_g, '/public/static_g/' => static::$url_static_g,
            // seo_site
            '{$site_name}'        => static::$seo_site['name'],
            '{$site_keywords}'    => static::$seo_site['keywords'],
            '{$site_description}' => static::$seo_site['description'],
            '{$site_slogan}'      => static::$seo_site['slogan'],
            // app_name
            '{$app_name}'         => static::$app_name,
            '{$app_domain}'       => static::$app_domain,
        ], static::$tpl_replace_array);
    }

    /**
     * @param string $url 当前面页
     * @param string $lang
     * @return string
     */
    static public function url_page(string $url = '', $lang = '')
    {
        if (!$lang) {
            $lang = static::$lang;
        }
        if ($url !== '' && $url[0] == '/') {
            $page_base_file = $url;
            if ($lang == static::$lang_default) {
                $page_lang = '';
                $page_url  = static::$app_path . substr($url, 1);
            } else {
                $page_lang = '/' . $lang;
                $page_url  = $page_lang . static::$app_path . substr($url, 1);
            }
        } else {
            $page_base_file = '/' . $url;
            if ($lang == static::$lang_default) {
                $page_lang = '';
                $page_url  = static::$app_path . $url;
            } else {
                $page_lang = '/' . $lang;
                $page_url  = $page_lang . static::$app_path . $url;
            }
        }
        if (empty(static::$page_url)) {
            static::url_page_set($page_base_file, $page_url, $page_lang);
        }
        return $page_url;
    }

    /**
     * @param string $page_base_file
     * @param string $page_url
     * @param string $page_lang
     */
    static public function url_page_set(string $page_base_file, string $page_url, string $page_lang)
    {
        /** @var string Base Page */
        static::$page_base_file = $page_base_file;
        /** @var string URL Page */
        static::$page_url = $page_url;

        /** @var string Www Page */
        $a                = explode('/', static::$root_www, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_www = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";

        /** @var string Mobile Page */
        $a                = explode('/', static::$root_wap, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_wap = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";

        /** @var string Mip Page */
        $a                = explode('/', static::$root_mip, 5);
        $p                = $a[3] ? "/{$a[3]}" : '';
        static::$page_mip = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";
    }

    /**
     * 静态地址
     * @param $url
     * @param string $static_root
     * @return string
     */
    static public function root_static($url, string $static_root = '/static/'): string
    {
        if ($url && is_array($url)) {
            $url = count($url) > 1 ? '??' . implode(',', $url) : $url[0];
        }
        return "{$static_root}{$url}";
    }

    /**
     * 当前带http的网站根
     * @return string
     */
    static public function root_curr_get()
    {
        if (static::$tpl_style == template::Type_Mip) {
            return static::$root_mip;
        } elseif (static::$tpl_style == template::Type_Wap) {
            return static::$root_wap;
        }
        return static::$root_www;
    }

    /**
     * 添加类库映射 (为什么不直接包进来？到时才包这样省一点)
     * @param string $class
     * @param string $filename
     * @param bool $is_require 是否默认加载
     */
    static public function class_set($class, $filename, $is_require = false)
    {
        // echo __FILE__.':'.__LINE__.' $class:'."{$class} \$filename:{$filename}\n";
        if ($is_require && is_file($filename)) {
            require $filename;
        } else {
            static::$maps_class[$class] = $filename;
        }
    }

    /**
     * 自动加载的类
     * @param $class
     */
    static public function load_class($class)
    {
//      echo // __FILE__.':'.__LINE__. ' $class:'."{$class}<br />\n";
        $filename = static::load_class_file_exists($class);
        if ($filename) {
            require $filename;
        }
    }

    /**
     * 添加自动加载路径
     * @param string $path_root 目录路径
     * @param string $namespace_prefix 命名空间
     * @param bool $cut_path 是否剪切 目录路径中的 命名空间
     */
    static public function load_class_set(string $path_root, string $namespace_prefix = '', bool $cut_path = false)
    {
        if ($path_root) {
            if ($namespace_prefix) {
                $first = explode('\\', $namespace_prefix)[0];
                $len   = strlen($namespace_prefix) + 1;
            } else {
                $first = '';
                $len   = 0;
            }
            if (!static::$maps_paths
                || !static::$maps_paths[$first]
                || !(is_array(static::$maps_paths[$first]) && in_array($path_root, array_column(static::$maps_paths[$first], 'path')))) {
                static::$maps_paths[$first][] = [
                    'path'      => $path_root,
                    'len'       => $len,
                    'cut'       => $cut_path,
                    'namespace' => $namespace_prefix
                ];
            }
        }
    }

    /**
     * 加载的类文件是否存在
     * @param $class
     * @return string
     */
    static protected function load_class_file_exists($class)
    {
        // 类库映射
        if (!empty(static::$maps_class[$class])) {
            $file = self::$maps_class[$class];
            // echo "\$file:{$file}\n";
            if ($file && is_file($file)) {
                return $file;
            }
        }

//        print_r([
//            '$class'=>$class,
//            // 'static::$maps_paths'=>static::$maps_paths
//        ]);
        // echo " \n<br />\$class'=> {$class} ";
        // exit();
        // 查找 PSR-4 prefix
        $filename = strtr($class, '\\', '/') . '.php';
        $firsts   = [explode('\\', $class)[0], ''];
        foreach ($firsts as $first) {
            if (isset(static::$maps_paths[$first])) {
                foreach (static::$maps_paths[$first] as $v) {
                    if ('' == $v['namespace']) {
                        // print_r(static::$maps_paths);
                        $file = $v['path'] . $filename;
//                                                echo " load_class2  -> \$class1 :{$class}  \$first:{$first}   \$len:{$v['len']}\n".
//                                                    "                \t\t\$path:{$v['path']}\n".
//                                                    "                \t\t\$filename:{$filename}\n".
//                                                    "                \t\t\$file1:{$file} \n";
                        if (is_file($file)) {
                            return $file;
                        }
                    } elseif (0 === strpos($class, $v['namespace'])) {
                        $file = $v['path'] . (($v['cut'] && $v['len']) ? substr($filename, $v['len']) : $filename);
//                                                echo " load_class  -> \$class0 :{$class}  \$first:{$first}  \$len:{$v['len']}\n".
//                                                    "                \t\t\$path:{$v['path']}\n".
//                                                    "                \t\t\$filename:{$filename}\n".
//                                                    "                \t\t\$file1:{$file} \n".var_export($v,true);
                        if (is_file($file)) {
                            return $file;
                        }
                    }
                }
            }
        }
        // echo ' ---> bad';
        return '';
    }

    /**
     * 加载controller
     * @param string $class_filename
     * @return string
     */
    static public function load_controller(string $class_filename)
    {
        $paths = static::$maps_paths['app'];
        if ($paths && is_array($paths)) {
            foreach ($paths as $v) {
                $filename = $v['path'] . static::$app_name . '/' . $class_filename;
                //  echo "\$filename:{$filename}\n";
                if (is_file($filename)) {
                    return $filename;
                }
            }
        }
        return '';
    }

    /**
     * 加载runtime
     * @param string $app_name
     * @param bool $is_auto_runtime
     * @param bool $is_auto_lang
     */
    static public function load_runtime(string $app_name, bool $is_auto_runtime = false, bool $is_auto_lang = false)
    {
        if (empty($app_name)) {
            return;
        }
        /** 加载runtime */
        if ($is_auto_runtime) {
            $filename = Dir_Storage . 'app/' . $app_name . '/runtime.php';
            is_file($filename) && require $filename;
        }
        /** 加载lang */
        if ($is_auto_lang) {
            if (\ounun::$lang_default == \ounun::$lang) {
                $langs = [\ounun::$lang];
            } else {
                $langs = [\ounun::$lang, \ounun::$lang_default];
            }
            foreach ($langs as $lang) {
                $filename = Dir_Storage . 'app/' . $app_name . '/lang.' . $lang . '.php';
                is_file($filename) && require $filename;
            }
        }
    }

    /** 应用app数据 */
    public static array $apps = [];
    /** 应用app数据(默认) */
    public static array $apps_default = ['app_name' => self::App_Name_Web, 'url' => '/'];

    /** 插件addons挂载数据 */
    public static array $addons_mount = [];

    /**
     * 设定路由数据
     * @param array $apps
     * @param array $apps_default
     * @param array $addons_mount
     */
    static public function apps_set(array $apps, array $apps_default = [], array $addons_mount = [])
    {
        if ($apps) {
            foreach ($apps as $k => $v) {
                static::$apps[$k] = $v;
            }
        }
        if ($apps_default) {
            static::$apps_default = $apps_default;
        }
        if ($addons_mount) {
            static::$addons_mount = $addons_mount;
        }
    }

    /**
     * 模块 快速路由
     * @param array $url_mods
     * @return array
     */
    static public function apps_get(array $url_mods = [])
    {
        // 修正App_Name
        $app_name = (static::$app_name == static::App_Name_Web || in_array(static::$app_name, static::App_Names))
            ? static::$app_name
            : static::App_Name_Web;
        // debug::header(\ounun::$apps_cache, '', __FILE__, __LINE__);

        // 插件路由
        $addon_tag = '';
        /** @var addons $apps */
        if ($url_mods[1] && ($route = static::$addons_mount["{$url_mods[0]}/$url_mods[1]"]) && $apps = $route['apps']) {
            array_shift($url_mods);
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif ($url_mods[0] && ($route = static::$addons_mount[$url_mods[0]]) && $apps = $route['apps']) {
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif (($route = static::$addons_mount['']) && $apps = $route['apps']) {
            $addon_tag = $apps::Addon_Tag;
        } else {
            error_php('ounun::$apps_cache[\'\']: There is no default value -> $apps_cache:' . json_encode(ounun::$addons_mount) . '');
        }

        // api
        if ($app_name == static::App_Name_Api) {
            $filename   = Dir_Ounun . 'ounun/restful.php';
            $class_name = "\\ounun\\restful";
            return [$filename, $class_name, $addon_tag, $url_mods];
        }

        // view_class
        if ($route['view_class']) {
            $class_filename = "{$addon_tag}/{$app_name}/{$route['view_class']}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}\\{$route['view_class']}";
        } else {
            $class_filename = "{$addon_tag}/{$app_name}.php";
            $class_name     = "\\addons\\{$addon_tag}\\{$app_name}";
        }
        static::$addon_path_curr = $route['url'] ? '/' . $route['url'] : '';

        // paths
        if ($class_filename) {
            $paths = static::$maps_paths['addons'];
            if ($paths && is_array($paths)) {
                foreach ($paths as $v) {
                    $filename = $v['path'] . $class_filename;
                    // echo "\$filename:{$filename0}\n";
                    if (is_file($filename)) {
                        //  echo " --> \$filename000:{$filename}\n";
                        if (empty($url_mods)) {
                            $url_mods = [static::Def_Method];
                        }
                        return [$filename, $class_name, $addon_tag, $url_mods];
                    }
                }
            } // if ($paths
        }
        return ['', '', '', $url_mods];
    }
}

/**
 * 开始
 * @param array $url_mods
 * @param string $host
 */
function start(array $url_mods, string $host)
{
    // 语言lang
    if ($url_mods && $url_mods[0] && ounun::$lang_supports[$url_mods[0]]) {
        $lang = array_shift($url_mods);
    } else {
        $lang = ounun::$lang ?? ounun::$lang_default;
    }
    ounun::lang_set($lang);

    // 应用app
    if ($url_mods && $url_mods[0] && $apps = ounun::$apps["{$host}/{$url_mods[0]}"]) {
        array_shift($url_mods);
    } elseif (ounun::$apps[$host]) {
        $apps = ounun::$apps[$host];
    } else {
        $apps = ounun::$apps_default;
    }
    ounun::$app_name = (string)$apps['app_name']; // 当前APP
    ounun::$app_path = (string)$apps['path'];     // 当前APP Path

    // load_config
    ounun::path_set(Dir_Root, true);
    // template_set
    ounun::template_paths_set([], (string)$apps['tpl_style'], (string)$apps['tpl_style_default'], (string)$apps['tpl_type'], (string)$apps['tpl_type_default']);

    // 开始 重定义头
    header('X-Powered-By: cms.cc; ounun.org;');
    debug::header(['$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 设定 模块与方法(缓存)
    /** @var v $classname */
    list($filename, $classname, $addon_tag, $url_mods) = ounun::apps_get($url_mods);
    debug::header(['$filename' => $filename, '$classname' => $classname, '$addon_tag' => $addon_tag, '$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 包括模块文件
    if ($filename) {
        require $filename;
        if (class_exists($classname, false)) {
            new $classname($url_mods, $addon_tag);
            exit();
        } else {
            $error = "LINE:" . __LINE__ . " Can't find controller:'{$classname}' filename:" . $filename;
        }
    } else {
        $error = "LINE:" . __LINE__ . " Can't find controller:{$classname}";
    }
    header('HTTP/1.1 404 Not Found');
    error_php($error);
}

/** Web */
function start_web()
{
    $uri      = url_original($_SERVER['REQUEST_URI']);
    $url_mods = url_to_mod($uri);
    start($url_mods, $_SERVER['HTTP_HOST']);
}

/** 注册自动加载 */
spl_autoload_register('\\ounun::load_class');
/** 自动加载 src-4 \ounun  */
ounun::load_class_set(Dir_Ounun, 'ounun', false);
/** 加载common.php */
require __DIR__ . '/helper.php';
