<?php

use ounun\apps\addons;
use ounun\apps\i18n;

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
    const def_module = 'index';
    /** @var string 默认操作名称 */
    const def_method = 'index';

    /** @var string web 网页 */
    const App_Name_Web = 'web';
    /** @var string api 数据API接口 */
    const App_Name_Api = 'api';
    /** @var string control 控制后台 */
    const App_Name_Control = 'control';
    /** @var string process 异步进程 */
    const App_Name_Process = 'process';
    /** @var string crontab 后台任务（定时任务） */
    const App_Name_Command = 'command';
    /** @var array 应用名称组 */
    const App_Names = [
        self::App_Name_Web,
        self::App_Name_Api,
        self::App_Name_Control,
        self::App_Name_Process,
        self::App_Name_Command,
    ];

    /** @var array 公共配制数据 */
    static public $global = [];
    /** @var array 公共配制数据(插件) */
    static public $global_addons = [];
    /** @var array 公共配制数据(应用) */
    static public $global_app = [];
    /** @var v */
    static public $view;
    /** @var array DB配制数据 */
    static public $database = [];
    /** @var string 默认 数据库 */
    static public $database_default = '';

    /** @var array 自动加载路径paths */
    static public $maps_paths = [];
    /** @var array 自动加载路径maps */
    static public $maps_class = [];
    /** @var array 已安装的功能模块(插件) */
    public static $maps_installed_addons = [];

    /** @var string 根目录 */
    static public $dir_root = '';
    /** @var string 根目录(App) */
    static public $dir_app = '';
    /** @var string Data目录 */
    static public $dir_data = '';
    /** @var string Ounun目录 */
    static public $dir_ounun = __DIR__ . '/';

    /** @var string 当前APP */
    static public $app_name = '';
    /** @var string 当前APP Path */
    static public $app_path = '';

    /** @var string 域名Domain */
    static public $app_domain = '';
    /** @var string 项目代号 */
    static public $app_code = '';
    /** @var string 项目站点名称 */
    // static public $app_sitename = '';
    /** @var string 当前版本号(本地cache) 1.1.1 */
    static public $app_version = '1.1.1';
    /** @var string 当前app之前通信内问key */
    static public $app_key_communication_private = '';

    /** @var string 当前面页(文件名)  Page Base */
    static public $page_base_file = '';
    /** @var string 当前面页(网址)    Page URL */
    static public $page_url = '';

    /** @var string Www Page */
    static public $page_www = '';
    /** @var string Mobile Page */
    static public $page_wap = '';
    /** @var string Mip Page */
    static public $page_mip = '';

    /** @var string Www URL */
    static public $root_www = '';
    /** @var string Mobile URL */
    static public $root_wap = '';
    /** @var string Mip URL */
    static public $root_mip = '';

    /** @var string Api URL */
    static public $root_api = '';
    /** @var string Res URL */
    static public $url_res = '';
    /** @var string Static URL */
    static public $url_static = '';
    /** @var string Upload URL */
    static public $url_upload = '';
    /** @var string StaticG URL */
    static public $url_static_g = '';
    /** @var string 为插件时网址前缀URL */
    static public $url_addon_pre = '';

    /** @var string 应用模板类型 pc www */
    static public $tpl_type = 'pc';
    /** @var string 应用模板类型[默认] */
    static public $tpl_type_default = 'pc';
    /** @var string 模板-样式 */
    static public $tpl_style = 'default';
    /** @var string 模板-样式[默认] */
    static public $tpl_style_default = 'default';
    /** @var array Template view目录 */
    static public $tpl_dirs = [];
    /** @var array 模板替换数据组 */
    static public $tpl_replace_str = [];

    /** @var array 站点SEO */
    static public $seo_site = ['sitename' => '', 'keywords' => '', 'description' => '', 'slogan' => ''];
    /** @var array 页面SEO */
    // static public $seo_page = ['title' => '', 'keywords' => '', 'description' => '', 'h1' => '', 'etag' => ''];

    /** @var string 当前语言 */
    static public $lang = 'zh_cn';
    /** @var string 默认语言 */
    static public $lang_default = 'zh_cn';
    /** @var array 支持的语言 */
    static public $lang_supports = [
        "en_us" => "English", // "zh"=>"繁體中文",
        "zh_cn" => "简体中文", // "ja"=>"日本語",
    ];
    /** @var array 命令s */
    static public $commands = [];

    /**
     * 本地环境变量设定 (应用)
     * @param string $app_name
     */
    static public function environment_app_set(string $app_name)
    {
        // 为空时直接返回
        if (empty($app_name)) {
            return;
        }
        $config_ini = static::$global_app[$app_name];

        // print_r(['$app_name'=>$app_name,'$config_ini'=>$config_ini]);
        if ($config_ini) {
            static::environment_set($config_ini);
        }
    }

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
     * 本地环境变量设定
     * @param array $config_ini
     */
    static public function environment_set(array $config_ini = [])
    {
        // 为空时直接返回
        if (empty($config_ini)) {
            return;
        }

        // 添加App路径(根目录)
        $key = 'paths';
        if (isset($config_ini[$key])) {
            $vs = $config_ini[$key];

            if ($vs && is_array($vs)) {
                foreach ($vs as $v) {
                    if (is_array($v) && $v['path']) {
                        static::paths_root_set($v['path'], $v['is_auto_helper']);
                    }
                }
            } else {
                if (file_exists(Dir_Root)) {
                    static::paths_root_set(Dir_Root, true);
                }
                if (file_exists(Dir_Vendor . 'cms.cc/')) {
                    static::paths_root_set(Dir_Vendor . 'cms.cc/', true);
                }
            }
        }

        // app数据
        $key = 'apps';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            if ($config && is_array($config)) {
                $routes_default = $config['default'] ?? [];
                unset($config['default']);
                if ($config && $routes_default) {
                    static::routes_set($config, $routes_default, []);
                }
            }
        }

        // 挂载模块路由
        $key = 'routes';
        if (isset($config_ini[$key])) {
            $addons = $config_ini[$key];
            if ($addons && is_array($addons)) {
                addons::mount_multi($addons);
            }
        }

        // 域名&项目代号&当前app之前通信内问key
        $key = 'domain';
        if (isset($config_ini[$key])) {
            $vs = $config_ini[$key];
            if ($vs && is_array($vs)) {
                static::domain_set($vs['domain'], $vs['code'], $vs['version'], $vs['key']);
            }
        }

        // 统计 / 备案号 / Baidu / xzh / 配制cache_file
        $key = 'global';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            if ($config && is_array($config)) {
                static::global_set($config);
            }
        }

        // 设定模板目录
        $key = 'template_paths';
        if (isset($config_ini[$key])) {
            $tpl_dirs = $config_ini[$key];
            if ($tpl_dirs && is_array($tpl_dirs)) {
                static::template_paths_set($tpl_dirs);
            }
        }

        // html变量替换
        $key = 'template_array';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            if ($config && is_array($config)) {
                static::template_array_set($config);
            }
        }

        // 配制database
        $key = 'databases';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            if ($config && is_array($config)) {
                static::database_set($config, $config_ini['database_default']);
            }
        }

        // 设定语言 & 设定支持的语言
        $key = 'lang';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            if ($config && is_array($config)) {
                static::lang_set('', $config['default'], $config['support']);
            }
        }

        // 设定路由数据
        $key = 'urls';
        if (isset($config_ini[$key])) {
            $urls = $config_ini[$key];
            if ($urls && is_array($urls)) {
                static::urls_set($urls['root_www'], $urls['root_wap'], $urls['root_mip'], $urls['root_api'], $urls['url_res'], $urls['url_upload'], $urls['url_static'], $urls['url_static_g']);
            }
        }

        // 设定站点页面SEO
        $key = 'seo_site';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            static::seo_site_set($config['sitename'], $config['keywords'], $config['description'], $config['slogan']);
        }

        // 设定站点页面SEO
        $key = 'seo_page';
        if (isset($config_ini[$key])) {
            $config = $config_ini[$key];
            static::seo_page_set((string)$config['title'], (string)$config['keywords'], (string)$config['description'], (string)$config['h1'], (string)$config['etag']);
        }

        // 公共配制数据(应用)
        $key = '__app__';
        if (isset($config_ini[$key])) {
            $configs = $config_ini[$key];
            if ($configs && is_array($configs)) {
                foreach ($configs as $app_name => $config) {
                    if ($config && is_array($config)) {
                        static::global_addons_set($config, '', $app_name);
                    }
                }
            }
        } // end if

        // 公共配制数据(插件)
        $key = '__addons__';
        if (isset($config_ini[$key])) {
            $configs = $config_ini[$key];
            if ($configs && is_array($configs)) {
                foreach ($configs as $addon_tag => $config) {
                    if ($config && is_array($config)) {
                        static::global_addons_set($config, $addon_tag, '');
                    }
                }
            }
        } // end if
    }

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
            self::$lang_default = $lang_default;
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

        $seo_page && static::$tpl_replace_str = array_merge(static::$tpl_replace_str, []);
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
     * 设定公共配制数据(插件) 或 (应用)
     * @param string $addon_tag
     * @param array $config
     */
    static public function global_addons_set(array $config = [], string $addon_tag = '', string $app_name = '')
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
            } elseif ($app_name) {
                if (!isset(static::$global_app[$app_name])) {
                    static::$global_app[$app_name] = [];
                } elseif (is_array(static::$global_app[$app_name])) {
                    static::$global_app[$app_name] = [];
                }
                foreach ($config as $key => $value) {
                    static::$global_app[$app_name][$key] = $value;
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
     * 公共配制数据(插件)
     * @param string $config_key
     * @param $default
     * @param string $addon_tag
     * @return mixed
     */
    public static function global_addons_get(string $config_key, $default, string $addon_tag)
    {
        if (\ounun::$global_addons) {
            $tag = \ounun::$global_addons[$addon_tag];
            if ($tag && $tag[$config_key]) {
                return $tag[$config_key];
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
     * 添加$addon
     * @param string $addon_apps
     */
    //static public function apps_push(string $addon_apps)
    static public function addons_set(string $addon_apps)
    {
        if ($addon_apps && !in_array($addon_apps, static::$maps_installed_addons)) {
            array_push(static::$maps_installed_addons, $addon_apps);
        }
    }

    /**
     * 应用/名称/目录 设定
     * @param string $app_name
     * @param string $app_path
     * @param string $dir_root
     * @param string $dir_app
     * @param string $dir_data
     * @param string $dir_ounun
     */
    static public function app_set(string $app_name = '', string $app_path = '',
                                   string $dir_root = '', string $dir_app = '', string $dir_data = '', string $dir_ounun = '')
    {
        // 当前APP
        $app_name && static::$app_name = $app_name;
        // 设定配制
        if (static::$app_name) {
            static::environment_app_set(static::$app_name);
        }
        // 当前APP Path
        $app_path && static::$app_path = $app_path;

        // 根目录
        $dir_root && static::$dir_root = $dir_root;
        // APP目录
        if ($dir_app) {
            static::$dir_app = $dir_app;
        } elseif (!static::$dir_app) {
            static::$dir_app = Dir_App . static::$app_name . '/';
        }
        // 数据目录
        $dir_data && static::$dir_data = $dir_data;
        // Ounun目录
        $dir_ounun && static::$dir_ounun = $dir_ounun;

        \ounun\debug::header(['$app_name' => static::$app_name, '$app_path' => static::$app_path,
                              '$dir_root' => static::$dir_root], '', __FILE__, __LINE__);
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
        /** 项目站点名称 */
        // $app_sitename &&  static::$app_sitename = $app_sitename;
        /** 当前版本号(本地cache) 1.1.1 */
        $app_version && static::$app_version = $app_version;
        /** 当前app之前通信内问key */
        $app_key_communication_private && static::$app_key_communication_private = $app_key_communication_private;
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
    static public function urls_set(string $root_www, string $root_wap, string $root_mip, string $root_api, string $url_res, string $url_upload, string $url_static, string $url_static_g)
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
        if ($tpl_style) {
            static::$tpl_style = $tpl_style;
        }
        // 风格(默认)
        if ($tpl_style_default) {
            static::$tpl_style_default = $tpl_style_default;
        }

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
                static::$tpl_replace_str[$key] = $value;
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
        static::$tpl_replace_str[$key] = $value;
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
            '{$root_upload}'      => static::$url_upload, '/public/upload/' => static::$url_upload,
            '{$root_static}'      => static::$url_static, '/public/static/' => static::$url_static,
            '{$root_static_g}'    => static::$url_static_g, '/public/static_g/' => static::$url_static_g,
            // seo_site
            '{$site_name}'        => static::$seo_site['name'],
            '{$site_keywords}'    => static::$seo_site['keywords'],
            '{$site_description}' => static::$seo_site['description'],
            '{$site_slogan}'      => static::$seo_site['slogan'],
            // seo_page
//            '{$seo_title}'        => static::$seo_page['title'],
//            '{$seo_keywords}'     => static::$seo_page['keywords'],
//            '{$seo_description}'  => static::$seo_page['description'],
//            '{$seo_h1}'           => static::$seo_page['h1'],
//            '{$seo_etag}'         => static::$seo_page['etag'],

            '{$app_name}'   => static::$app_name,
            '{$app_domain}' => static::$app_domain,
        ], static::$tpl_replace_str);
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
    static public function root_url_curr_get()
    {
        if (static::$tpl_style == \ounun\template::Type_Mip) {
            return static::$root_mip;
        } elseif (static::$tpl_style == \ounun\template::Type_Wap) {
            return static::$root_wap;
        }
        return static::$root_www;
    }

    /**
     * 添加自动加载路径
     * @param string $path 目录路径
     * @param string $namespace_prefix 命名空间
     * @param bool $cut_path 是否剪切 目录路径中的 命名空间
     */
    static public function paths_class_set(string $path, string $namespace_prefix = '', bool $cut_path = false)
    {
        if ($path) {
            if ($namespace_prefix) {
                $first = explode('\\', $namespace_prefix)[0];
                $len   = strlen($namespace_prefix) + 1;
            } else {
                $first = '';
                $len   = 0;
            }
            if (!static::$maps_paths
                || !static::$maps_paths[$first]
                || !(is_array(static::$maps_paths[$first]) && in_array($path, array_column(static::$maps_paths[$first], 'path')))) {
                static::$maps_paths[$first][] = [
                    'path'      => $path,
                    'len'       => $len,
                    'cut'       => $cut_path,
                    'namespace' => $namespace_prefix
                ];
            }
        }
    }

    /**
     * 添加App路径(根目录)
     * @param string $path
     * @param bool $is_auto_helper
     * @param bool $is_auto_command
     */
    static public function paths_root_set(string $path, ?bool $is_auto_helper = false)
    {
        if (empty($path)) {
            return;
        }
        /** src-0 \         自动加载 */
        ounun::paths_class_set($path . 'src/', '', false);
        /** src-0 \addons   自动加载  */
        ounun::paths_class_set($path . 'addons/', 'addons', true);

        /** 加载helper */
        if ($is_auto_helper) {
            is_file($path . 'app/helper.php') && require $path . 'app/helper.php';
        }
    }

    /**
     * 添加App路径(具体应用)
     *    --param string $paths_app_root 应用逻辑程序所在的根目录
     *    --param string $namespace_prefix 加载类前缀 默认 app
     * @param string $paths_app_curr 当前程序所在目录
     * @param bool $is_auto_helper 是否默认加载helper
     *
     * @param array $tpl_dirs
     *
     * @param string $tpl_style
     * @param string $tpl_style_default 模板风格（默认）
     * @param string $tpl_type
     * @param string $tpl_type_default
     */
    static public function paths_instance(// string $paths_app_root, string $namespace_prefix,
        string $paths_app_curr, bool $is_auto_helper = true,
        array $tpl_dirs = [], string $tpl_style = '', string $tpl_style_default = '', string $tpl_type = '', string $tpl_type_default = '')
    {
        /** controller add_paths */
        // static::paths_class_set($paths_app_root, $namespace_prefix, true);
        /** load_config */
        static::load_config($paths_app_curr, $is_auto_helper);
        /** template_set */
        static::template_paths_set($tpl_dirs, $tpl_style, $tpl_style_default, $tpl_type, $tpl_type_default);
    }

    /**
     * 自动加载的类
     * @param $class
     */
    static public function load_class($class)
    {
//        echo // __FILE__.':'.__LINE__. ' $class:'.
//            "{$class}<br />\n";
        $file = static::load_class_file_exists($class);
        if ($file) {
            require $file;
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
     * 加载Config
     * @param string $dir
     * @param bool $is_auto_helper
     */
    static public function load_config(string $dir, bool $is_auto_helper = false)
    {
        /** 加载helper */
        if ($is_auto_helper) {
            is_file($dir . 'helper.php') && require $dir . 'helper.php';
        }
        // echo 'load_config0 -> '.__LINE__.':'.(is_file($dir.'helper.php')?'1':'0').' '.$dir.'helper.php'."\n";
        if (Environment) {
            /** 加载config */
            is_file($dir . 'config.php') && require $dir . 'config.php';
            // echo 'load_config1 -> '.__LINE__.':'.(is_file($dir.'config.php')?'1':'0').' '.$dir.'config.php'."\n";
            /** 加载config-xxx */
            if (Environment && is_file($dir . 'config' . Environment . '.php')) {
                require $dir . 'config' . Environment . '.php';
                //echo 'load_config2 -> '.__LINE__.':'.(file_exists($dir.'config'.Environment.'.php')?'1':'0').' '.$dir.'config'.Environment.'.php'."\n";
            }
        } else {
            /** 加载config */
            is_file($dir . 'config.php') && require $dir . 'config.php';
        }
    }

    /** 路由数据 */
    static public $routes = [
        //         'www.866bet.com/api'  => ['app_name'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app_name'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app_name'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    static public $routes_default = ['app_name' => self::App_Name_Web, 'url' => '/'];

    /** 路由数据 */
    public static $routes_cache = [];

    /**
     * 设定路由数据
     * @param array $routes
     * @param array $routes_default
     * @param array $routes_cache
     */
    static public function routes_set(array $routes, array $routes_default = [], array $routes_cache = [])
    {
        if ($routes) {
            foreach ($routes as $k => $v) {
                static::$routes[$k] = $v;
            }
        }
        if ($routes_default) {
            static::$routes_default = $routes_default;
        }
        if ($routes_cache) {
            static::$routes_cache = $routes_cache;
        }
    }

    /**
     * 模块 快速路由
     * @param array $url_mods
     * @return array
     */
    static public function routes_get(array $url_mods = [])
    {
        // 修正App_Name
        $app_name = (static::$app_name == static::App_Name_Web || in_array(static::$app_name, static::App_Names))
            ? static::$app_name
            : static::App_Name_Web;

        // 这里修正URL兼容源生与重写
        if ($app_name == static::App_Name_Control) {
            foreach (static::$maps_installed_addons as $apps) {
                $addon_tag = $apps::Addon_Tag;
                if ($addon_tag) {
                    /** @var addons $addon_apps_old */
                    $addon_info = static::$routes_cache[$addon_tag];
                    if ($addon_info) {
                        /** @var addons $addon_apps_old */
                        $addon_apps_old = $addon_info['apps'];
                        $addon_tag_old  = $addon_apps_old::Addon_Tag;
                        if ($addon_tag_old == $addon_tag) {
                            if ($addon_info['auto'] == false) {
                                addons::mount_single($apps, $addon_tag, '', true);
                            }
                        }
                    } else {
                        addons::mount_single($apps, $addon_tag, '', true);
                    }
                }
            }
        }

        // debug
        \ounun\debug::header(\ounun::$routes_cache, '', __FILE__, __LINE__);

        // 插件路由
        $addon_tag = '';

        /** @var addons $apps */
        if ($url_mods[1] && ($route = static::$routes_cache["{$url_mods[0]}/$url_mods[1]"]) && $apps = $route['apps']) {
            array_shift($url_mods);
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif ($url_mods[0] && ($route = static::$routes_cache[$url_mods[0]]) && $apps = $route['apps']) {
            array_shift($url_mods);
            $addon_tag = $apps::Addon_Tag;
        } elseif (($route = static::$routes_cache['']) && $apps = $route['apps']) {
            $addon_tag = $apps::Addon_Tag;
        } else {
            error_php('ounun::$routes_cache[\'\']: There is no default value。' . PHP_EOL
                . 'routes_cache:' . json_encode(ounun::$routes_cache) . '');
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
        // print_r([$class_filename,$class_name]);
        static::$url_addon_pre = $route['url'] ? '/' . $route['url'] : '';

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
                            $url_mods = [static::def_method];
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
    // 语言
    if ($url_mods && $url_mods[0] && ounun::$lang_supports[$url_mods[0]]) {
        $lang = array_shift($url_mods);
    } else {
        $lang = ounun::$lang ? ounun::$lang : ounun::$lang_default;
    }
    // load_config 0 Dir
    ounun::load_config(Dir_App, false);

    // Routes
    // print_r(['{$host}/{$mod[0]}' => "{$host}/{$url_mods[0]}", '$host' => $host, '$mod' => $url_mods]);
    if ($url_mods && $url_mods[0] && $cfg_0 = ounun::$routes["{$host}/{$url_mods[0]}"]) {
        array_shift($url_mods);
        // $cfg_0 = ounun::$routes["{$host}/{$mod_0}"];
    } elseif (ounun::$routes[$host]) {
        $cfg_0 = ounun::$routes[$host];
    } else {
        $cfg_0 = ounun::$routes_default;
    }

    // apps_domain_set
    ounun::app_set((string)$cfg_0['app_name'], (string)$cfg_0['path'], Dir_Root, '', Dir_Data, Dir_Ounun);
    // add_paths_app_instance
    ounun::paths_instance(// Dir_App, 'app',
        Dir_App . ounun::$app_name . '/', true,
        [], (string)$cfg_0['tpl_style'], (string)$cfg_0['tpl_style_default'], (string)$cfg_0['tpl_type'], (string)$cfg_0['tpl_type_default']);
    // lang_set
    ounun::lang_set($lang);

    // 开始 重定义头
    header('X-Powered-By: cms.cc; ounun.org;');

    \ounun\debug::header(['$url_mods' => $url_mods], '', __FILE__, __LINE__);

    // 设定 模块与方法(缓存)
    /** @var v $classname */
    list($filename, $classname, $addon_tag, $url_mods) = ounun::routes_get($url_mods);

    \ounun\debug::header(['$filename' => $filename, '$classname' => $classname, '$addon_tag' => $addon_tag, '$url_mods' => $url_mods], '', __FILE__, __LINE__);
//   echo "\$filename:" . __LINE__ . " -->\$filename:{$filename} \$classname:{$classname} \$addon_tag:{$addon_tag} \$mod:" . json_encode_unescaped($url_mods) . "\n";

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
    trigger_error($error, E_USER_ERROR);
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
ounun::paths_class_set(Dir_Ounun, 'ounun', false);
/** 加载common.php */
require __DIR__ . '/helper.php';
