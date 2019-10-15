<?php
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
    /** @var string crontab 定时任务 */
    const App_Name_Crontab = 'crontab';
    /** @var array 应用名称组 */
    const App_Names = [
        self::App_Name_Web ,
        self::App_Name_Api  ,
        self::App_Name_Control ,
        self::App_Name_Process ,
        self::App_Name_Crontab ,
    ];

    /** @var array 公共配制数据 */
    static public $global = [];
    /** @var \v */
    static public $view;
    /** @var array DB配制数据 */
    static public $database = [];
    /** @var string 默认 数据库 */
    static public $database_default = '';

    /** @var array 自动加载路径paths */
    static public $maps_paths = [];
    /** @var array 自动加载路径maps */
    static public $maps_class = [];

    /** @var string 根目录 */
    static public $dir_root = '';
    /** @var string Data目录 */
    static public $dir_data = '';
    /** @var string Ounun目录 */
    static public $dir_ounun = __DIR__ . '/';
    /** @var string 根目录(App) */
    static public $dir_app = '';

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
    static public $url_www = '';
    /** @var string Mobile URL */
    static public $url_wap = '';
    /** @var string Mip URL */
    static public $url_mip = '';

    /** @var string Api URL */
    static public $url_api = '';
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

    /** @var string 当前APP */
    static public $app_name = '';
    /** @var string 当前APP Path */
    static public $app_path = '';
    /** @var string 域名Domain */
    static public $app_domain = '';
    /** @var string 当前app之前通信内问key */
    static public $app_key_communication = '';

    /** @var string 应用模板类型 pc  */
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

    /** @var \ounun\console\model\i18n 语言包 */
    static public $i18n;
    /** @var string 默认语言 */
    static public $lang_default = 'zh_cn';
    /** @var string 当前语言 */
    static public $lang = 'zh_cn';
    /** @var array 支持的语言 */
    public static $langs = [
        "en_us" => "English", // "zh"=>"繁體中文",
        "zh_cn" => "简体中文", // "ja"=>"日本語",
    ];

    /**
     * 设定语言
     * @param string $lang
     * @param string $lang_default
     */
    static public function lang_set(string $lang, string $lang_default = '')
    {
        if ($lang) {
            static::$lang = $lang;
        }
        $lang_default && self::$lang_default = $lang_default;
        $i18ns = ['app\\' . static::$app_name . '\\model\\i18n', 'extend\\i18n', 'ounun\\mvc\\model\\i18n'];
        if ($lang != static::$lang_default) {
            array_unshift($i18ns, 'app\\' . static::$app_name . '\\model\\i18n\\' . $lang);
        }
        foreach ($i18ns as $i18n) {
            $file = static::load_class_file_exists($i18n);
            // echo ' \$i18n -->1:'.$i18n." \$file:".$file."\n";
            if ($file) {
                // echo ' \$i18n -->2:'.$i18n."\n";
                static::$i18n = $i18n;
                require $file;
                break;
            }
        }
    }

    /**
     * 设定支持的语言
     * @param array $lang_list
     */
    static public function lang_support_set(array $lang_list = [])
    {
        if ($lang_list) {
            foreach ($lang_list as $lang => $lang_name) {
                static::$langs[$lang] = $lang_name;
            }
        }
    }

    /**
     * 本地环境变量设定
     * @param array $ini
     */
    static public function environment_set(array $ini = [])
    {
        // print_r($ini);
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
     * 设定地址
     * @param string $url_www
     * @param string $url_wap
     * @param string $url_mip
     * @param string $url_api
     * @param string $url_res
     * @param string $url_static
     * @param string $url_upload
     * @param string $url_static_g
     * @param string $app_domain
     */
    static public function urls_domain_set(string $url_www, string $url_wap, string $url_mip, string $url_api, string $url_res, string $url_static, string $url_upload, string $url_static_g, string $app_domain)
    {
        /** Www URL */
        static::$url_www = $url_www;
        /** Wap URL Mobile */
        static::$url_wap = $url_wap;
        /** Mip URL */
        static::$url_mip = $url_mip;

        /** Api URL */
        static::$url_api = $url_api;
        /** Res URL */
        static::$url_res = $url_res;
        /** Static URL */
        static::$url_static = $url_static;
        /** Upload URL */
        static::$url_upload = $url_upload;
        /** StaticG URL */
        static::$url_static_g = $url_static_g;
        /** 项目主域名 */
        static::$app_domain = $app_domain;
    }

    /**
     * 设定目录
     * @param string $dir_ounun
     * @param string $dir_root
     * @param string $dir_data
     * @param string $app_name
     * @param string $app_path
     * @param string $dir_app
     */
    static public function app_name_path_set(string $dir_ounun, string $dir_root, string $dir_data, string $app_name, string $app_path, string $dir_app = '')
    {
        // 当前APP
        if ($app_name) {
            static::$app_name = $app_name;
        }
        // 当前APP Path
        if ($app_path) {
            static::$app_path = $app_path;
        }
        // Ounun目录
        if ($dir_ounun) {
            static::$dir_ounun = $dir_ounun;
        }
        // 根目录
        if ($dir_root) {
            static::$dir_root = $dir_root;
        }
        // 根目录
        if ($dir_data) {
            static::$dir_data = $dir_data;
        }
        // APP目录
        if ($dir_app) {
            static::$dir_app = $dir_app;
        } elseif (!static::$dir_app) {
            static::$dir_app = Dir_App . static::$app_name . '/';
        }
    }

    /**
     * 设定 模板及模板根目录
     * @param string $tpl_style 风格
     * @param string $tpl_style_default 风格(默认)
     * @param string $tpl_type  类型
     * @param string $tpl_type_default  类型(默认)
     * @param array  $tpl_dirs  模板根目录
     */
    static public function template_set(array $tpl_dirs = [],
                                        string $tpl_style = '', string $tpl_style_default = '',
                                        string $tpl_type = '', string $tpl_type_default = '')
    {
        // 模板根目录
        if($tpl_dirs && is_array($tpl_dirs)){
            foreach ($tpl_dirs as $tpl_dir){
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
        } else {
            if (static::$i18n && empty(static::$tpl_style)) {
                static::$tpl_style = static::i18n_get()::tpl_style;
            }
        }
        // 风格(默认)
        if ($tpl_style_default) {
            static::$tpl_style_default = $tpl_style_default;
        } else {
            if (static::$i18n && empty(static::$tpl_style_default)) {
                static::$tpl_style_default = static::i18n_get()::tpl_style_default;
            }
        }

        // 类型
        if ($tpl_type) {
            static::$tpl_type = $tpl_type;
        } else {
            if (static::$i18n && empty(static::$tpl_type)) {
                static::$tpl_type = static::i18n_get()::tpl_type;
            }
        }
        // 类型(默认)
        if ($tpl_type_default) {
            static::$tpl_type_default = $tpl_type_default;
        } else {
            if (static::$i18n && empty(static::$tpl_type_default)) {
                static::$tpl_type_default = static::i18n_get()::tpl_type_default;
            }
        }
    }

    /**
     * @param string $seo_title
     * @param string $seo_keywords
     * @param string $seo_description
     * @param string $seo_h1
     * @param string $etag
     */
    static public function template_page_tkd_set(string $seo_title = '', string $seo_keywords = '', string $seo_description = '', string $seo_h1 = '', string $etag = '')
    {
        if ($seo_title) {
            static::template_replace_str_set('{$seo_title}', $seo_title);
        }
        if ($seo_keywords) {
            static::template_replace_str_set('{$seo_keywords}', $seo_keywords);
        }
        if ($seo_description) {
            static::template_replace_str_set('{$seo_description}', $seo_description);
        }
        if ($seo_h1) {
            static::template_replace_str_set('{$seo_h1}', $seo_h1);
        }
        if ($etag) {
            static::template_replace_str_set('{$etag}', $etag);
        }
    }

    /**
     * 设定模板替换
     * @param array $data
     */
    static public function template_array_set(array $data)
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
            '{$page_url}' => static::$page_url, // static::$view->page_url,
            '{$page_file}' => static::$page_base_file,//static::$view->page_file,

            '{$url_www}' => static::$url_www,
            '{$url_wap}' => static::$url_wap,
            '{$url_mip}' => static::$url_mip,
            '{$url_api}' => static::$url_api,

            //  '{$url_page}' => static::url_page(),

            '{$canonical_www}' => static::$page_www, // static::$url_www . $url_base,
            '{$canonical_mip}' => static::$page_mip, // static::$url_mip . $url_base,
            '{$canonical_wap}' => static::$page_wap, // static::$url_wap . $url_base,

            '{$app}' => static::$app_name,
            '{$domain}' => static::$app_domain,

            '{$res}' => static::$url_res,

            // '{$sitename}' => i18n()::title,

            '{$static}' => static::$url_static,
            '{$upload}' => static::$url_upload,
            '{$static_g}' => static::$url_static_g,

            '/public/static_g/' => static::$url_static_g,
            '/public/static/' => static::$url_static,
            '/public/upload/' => static::$url_upload,
        ], static::$tpl_replace_str);
    }

    /**
     * 语言包
     * @return \ounun\console\model\i18n
     */
    static public function i18n_get()
    {
        return static::$i18n;
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
                $page_url = static::$app_path . substr($url, 1);
            }else{
                $page_lang = '/' . $lang;
                $page_url = $page_lang. static::$app_path . substr($url, 1);
            }
        } else {
            $page_base_file = '/'.$url;
            if ($lang == static::$lang_default) {
                $page_lang = '';
                $page_url = static::$app_path . $url;
            }else{
                $page_lang = '/' . $lang;
                $page_url =  $page_lang. static::$app_path . $url;
            }
        }
        if(empty(static::$page_url)){
            static::url_page_set($page_base_file,$page_url,$page_lang);
        }
        return $page_url;
    }

    /**
     * @param string $page_base_file
     * @param string $page_url
     * @param string $page_lang
     */
    static public function url_page_set(string $page_base_file,string $page_url,string $page_lang)
    {
        /** @var string Base Page */
        static::$page_base_file = $page_base_file;
        /** @var string URL Page */
        static::$page_url = $page_url;
        
        /** @var string Www Page */
        $a = explode('/',static::$url_www,5);
        $p = $a[3]?"/{$a[3]}":'';
        static::$page_www = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";
        /** @var string Mobile Page */
        $a = explode('/',static::$url_wap,5);
        $p = $a[3]?"/{$a[3]}":'';
        static::$page_wap = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";
        /** @var string Mip Page */
        $a = explode('/',static::$url_mip,5);
        $p = $a[3]?"/{$a[3]}":'';
        static::$page_mip = "{$a[0]}//{$a[2]}{$page_lang}{$p}{$page_base_file}";
    }

    /**
     * 静态地址
     * @param $url
     * @param string $static_root
     * @return string
     */
    static public function url_static($url, string $static_root = '/static/'): string
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
    static public function url_root_curr_get()
    {
        if (static::$tpl_style == '_mip') {
            return static::$url_mip;
        } elseif (static::$tpl_style == '_wap') {
            return static::$url_wap;
        }
        return static::$url_www;
    }

    /**
     * 添加自动加载路径
     * @param string $path 目录路径
     * @param string $namespace_prefix 命名空间
     * @param bool $cut_path 是否剪切 目录路径中的 命名空间
     */
    static public function add_paths(string $path, string $namespace_prefix = '', bool $cut_path = false)
    {
        if ($path) {
            if ($namespace_prefix) {
                $first = explode('\\', $namespace_prefix)[0];
                $len = strlen($namespace_prefix) + 1;
            } else {
                $first = '';
                $len = 0;
            }
            if (!static::$maps_paths || !static::$maps_paths[$first] || !(is_array(static::$maps_paths[$first]) && in_array($path, array_column(static::$maps_paths[$first], 'path')))) {
                static::$maps_paths[$first][] = ['path' => $path, 'len' => $len, 'cut' => $cut_path, 'namespace' => $namespace_prefix];
            }
        }
    }

    /**
     * 添加App路径(根目录)
     * @param string $path
     * @param bool $is_auto_helper
     * @param bool $is_auto_task
     */
    static public function add_paths_app_root(string $path, bool $is_auto_helper = false, bool $is_auto_task = false)
    {
        /** src-0 \         自动加载 */
        \ounun::add_paths($path . 'src/', '', false);
        /** src-0 \addons   自动加载  */
        \ounun::add_paths($path . 'addons/', 'addons', true);

        /** 加载helper */
        if ($is_auto_helper) {
            is_file($path . 'app/helper.php') && require $path . 'app/helper.php';
        }

        /** 加载task */
        if ($is_auto_task) {
            $task = is_file($path . 'app/task.php') ? include $path . 'app/task.php' : [];
            if ($task) {
                if (static::$global['task'] && is_array(static::$global['task'])) {
                    static::$global['task'] = array_merge(static::$global['task'], $task);
                } else {
                    static::$global['task'] = $task;
                }
            }
        }
    }

    /**
     * 添加App路径(具体应用)
     * @param string $paths_app_root   应用逻辑程序所在的根目录
     * @param string $namespace_prefix 加载类前缀 默认 app
     * @param string $paths_app_curr   当前程序所在目录
     * @param bool $is_auto_helper     是否默认加载helper
     * @param array  $tpl_dirs
     * @param string $tpl_style
     * @param string $tpl_style_default   模板风格（默认）
     * @param string $tpl_type
     * @param string $tpl_type_default
     */
    static public function add_paths_app_instance(string $paths_app_root, string $namespace_prefix,
                                                  string $paths_app_curr, bool $is_auto_helper = true,
                                                  array $tpl_dirs = [], string $tpl_style = '',  string $tpl_style_default = '',  string $tpl_type = '', string $tpl_type_default = '')
    {
        /** controller add_paths */
        static::add_paths($paths_app_root, $namespace_prefix, true);
        /** template_set */
        static::template_set($tpl_dirs,$tpl_style ,   $tpl_style_default , $tpl_type  ,  $tpl_type_default);
        /** load_config */
        static::load_config($paths_app_curr, $is_auto_helper);
    }

    /**
     * 添加类库映射 (为什么不直接包进来？到时才包这样省一点)
     * @param string $class
     * @param string $filename
     * @param bool $is_require 是否默认加载
     */
    static public function add_class($class, $filename, $is_require = false)
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
        //  echo __FILE__.':'.__LINE__.' $class:'."{$class}\n";
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

        // 查找 PSR-4 prefix
        $filename = strtr($class, '\\', '/') . '.php';
        $firsts = [explode('\\', $class)[0], ''];
        foreach ($firsts as $first) {
            if (isset(static::$maps_paths[$first])) {
                foreach (static::$maps_paths[$first] as $v) {
                    if ('' == $v['namespace']) {
//                        print_r(static::$maps_paths);
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
        //         'www.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    static public $routes_default = ['app' => 'www', 'url' => '/'];

    /** 路由数据 */
    static public $routes_cache = [];

    /**
     * 设定路由数据
     * @param array $routes
     * @param array $routes_default
     * @param array $routes_cache
     */
    static public function routes_set(array $routes, array $routes_default = [],array $routes_cache = [])
    {
        if ($routes) {
            foreach ($routes as $k => $v) {
                static::$routes[$k] = $v;
            }
        }
        if ($routes_default) {
            static::$routes_default = $routes_default;
        }
        if($routes_cache) {
            static::$routes_cache = $routes_cache;
        }
    }

    /**
     * 模块 快速路由
     * @param array $mod
     * @param string $addon_tag
     * @return array
     */
    static public function routes_get(array $mod = [])
    {
        $app_name            = (static::$app_name == static::App_Name_Web || in_array(static::$app_name,static::App_Names)) ? static::$app_name : static::App_Name_Web;
        $class_filename      = '';

        if ($mod[1] && ($route = static::$routes_cache["{$mod[0]}/$mod[1]"]) && $route['addon_tag']){
            $addon_tag              = $route['addon_tag'];
            $class_filename         = "{$addon_tag}/{$app_name}/{$route['subclass']}.php";
            $classname              = "\\addons\\{$addon_tag}\\{$app_name}\\{$route['subclass']}";
            static::$url_addon_pre  = $route['url']?'/'.$route['url']:'';
            array_shift($mod);
        }elseif(($route = static::$routes_cache[((is_array($mod) && $mod[0]) ? $mod[0] : static::def_module)]) && $route['addon_tag']){
            $addon_tag              = $route['addon_tag'];
            if($route['subclass']){
                $class_filename         = "{$addon_tag}/{$app_name}/{$route['subclass']}.php";
                $classname              = "\\addons\\{$addon_tag}\\{$app_name}\\{$route['subclass']}";
            }else{
                $class_filename         = "{$addon_tag}/{$app_name}.php";
                $classname              = "\\addons\\{$addon_tag}\\{$app_name}";
            }
            static::$url_addon_pre  = $route['url'] && $route['url'] != static::def_module ? '/'.$route['url'] : '';
        }

        // paths
        if($class_filename){
            $paths           = static::$maps_paths['addons'];
            if ($paths && is_array($paths)) {
                foreach ($paths as $v) {
                    $filename = $v['path'] . $class_filename;
                    // echo "\$filename:{$filename0}\n";
                    if (is_file($filename)) {
                        //  echo " --> \$filename000:{$filename}\n";
                        if ($mod[1]) {
                            array_shift($mod);
                        } else {
                            $mod = [static::def_method];
                        }
                        return [$filename,$classname,$mod];
                    }
                }
            }
        }
        return ['','',$mod];
    }
}

/**
 * 开始
 * @param array $mod
 * @param string $host
 */
function start(array $mod, string $host)
{
    // 语言
    if ($mod && $mod[0] && ounun::$langs[$mod[0]]) {
        $lang = array_shift($mod);
    } else {
        $lang = ounun::$lang ? ounun::$lang : ounun::$lang_default;
    }
    // load_config 0 Dir
    ounun::load_config(Dir_App, false);

    // Routes
    if ($mod && $mod[0] && ounun::$routes["{$host}/{$mod[0]}"]) {
        $mod_0 = array_shift($mod);
        $cfg_0 = ounun::$routes["{$host}/{$mod_0}"];
    } elseif (ounun::$routes[$host]) {
        $cfg_0 = ounun::$routes[$host];
    } else {
        $cfg_0 = ounun::$routes_default;
    }

    // apps_domain_set
    ounun::app_name_path_set(Dir_Ounun, Dir_Root, Dir_Data, (string)$cfg_0['app'], (string)$cfg_0['url']);
    // add_paths_app_instance
    ounun::add_paths_app_instance(Dir_App,'app',
        Dir_App . ounun::$app_name . '/', true,
        [],(string)$cfg_0['tpl_style'], (string)$cfg_0['tpl_style_default'],(string)$cfg_0['tpl_type'], (string)$cfg_0['tpl_type_default']);
    // lang_set
    ounun::lang_set($lang);

    // 开始 重定义头
    header('X-Powered-By: cms.cc v3.2.1; ounun.org v3.1.2;');
    // 设定 模块与方法(缓存)
    list($filename,$classname,$mod) = ounun::routes_get($mod);
    // echo "\$filename:".__LINE__." -->:{$filename}\n";
    // 设定 模块与方法
    if(empty($filename)){
        if (is_array($mod) && $mod[0]) {
            $filename  = ounun::load_controller("controller/{$mod[0]}.php");
            $addon_tag = $mod[0];
            if ($filename) {
                $module    = $mod[0];
                $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
                if ($mod[1]) {
                    array_shift($mod);
                } else {
                    $mod = [ounun::def_method];
                }
            } else {
                if ($mod[1]) {
                    $filename = ounun::load_controller("controller/{$mod[0]}/{$mod[1]}.php");
                    if ($filename) {
                        $module    = $mod[0] . '\\' . $mod[1];
                        $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
                        if ($mod[2]) {
                            array_shift($mod);
                            array_shift($mod);
                        } else {
                            $mod = [ounun::def_method];
                        }
                    } else {
                        $filename = ounun::load_controller("controller/{$mod[0]}/index.php");
                        if ($filename) {
                            $module    = "{$mod[0]}\\index";
                            $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
                            array_shift($mod);
                        }
                    }
                } else {
                    $filename = ounun::load_controller("controller/{$mod[0]}/index.php");
                    if ($filename) {
                        $module    = "{$mod[0]}\\index";
                        $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
                        $mod       = [ounun::def_method];
                    }
                } // end --------- if ($mod[1])
            } // end ------------- \Dir_App . "module/" . $mod[0] . '.php';
            // echo "\$filename:".__LINE__." -->:{$filename}\n";
        } else {
            // 默认模块 与 默认方法
            $module    = ounun::def_module;
            $addon_tag = ounun::def_module;
            $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
            $filename  = ounun::load_controller("controller/index.php");
            if($filename){
                $mod   =[ounun::def_method];
            }
        }
        // echo "\$filename:".__LINE__." -->:{$filename}\n";
        if(empty($filename)){
            $module    = ounun::def_module;
        //  $addon_tag = config::def_module;
            $classname = '\\app\\' . ounun::$app_name . '\\controller\\' . $module;
            $filename  = ounun::load_controller("controller/index.php");
        }
        // echo "\$filename:".__LINE__." -->:{$filename}\n";
    }
    // 包括模块文件
    if ($filename) {
        require $filename;
        if (class_exists($classname, false)) {
            new $classname($mod);
            exit();
        } else {
            $error = "Can't find controller:'{$classname}' filename:" . $filename;
        }
    } else {
        $error = "Can't find controller:{$classname}";
    }
    header('HTTP/1.1 404 Not Found');
    trigger_error($error, E_USER_ERROR);
}

/** Web */
function start_web()
{
    $uri = url_original($_SERVER['REQUEST_URI']);
    $mod = url_to_mod($uri);
    start($mod, $_SERVER['HTTP_HOST']);
}

/**
 * Cmd
 * @param $argv
 */
function start_task($argv)
{
    // load_config 0 Dir
    ounun::load_config(Dir_App);
    // task
    $task = is_file(Dir_App . 'task.php') ? include Dir_App . 'task.php' : [];
    // console
    $c = new ounun\console($task);
    $c->run($argv);
}

/** 加载common.php */
require __DIR__ . '/helper.php';
/** 注册自动加载 */
spl_autoload_register('\\ounun::load_class');
