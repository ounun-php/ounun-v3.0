<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun;

use ounun;
use v;

/**
 * Class template
 * @package ounun
 */
class template
{
    /** @var string Api接口Rest */
    const Type_Api_Rest = '';
    /** @var string Pc网页www */
    const Type_Pc = 'pc';
    /** @var string H5网页wap */
    const Type_Wap = 'wap';
    /** @var string Mip网页 */
    const Type_Mip = 'mip';
    /** @var string control后台 */
    const Type_Control = 'control';
    /** @var array 模板类型 */
    const Types = [
        self::Type_Api_Rest,
        self::Type_Pc,
        self::Type_Wap,
        self::Type_Mip,
        self::Type_Control,
    ];

    /** @var string 应用模板类型pc/wap/mip - 模板 */
    public static string $type = 'pc';
    /** @var string 应用模板类型pc/wap/mip[默认] - 模板 */
    public static string $type_default = 'pc';

    /** @var string 主题风格(主题目录) */
    public static string $theme = 'default';
    /** @var string 主题风格(主题目录)[默认] - 模板 */
    public static string $theme_default = 'default';

    /** @var array Template view目录 */
    public static array $paths = [];
    /** @var array 模板替换数据组 */
    public static array $assign_array = [];

    /** @var array 站点SEO */
    public static array $site_tkd_seo = ['sitename' => '', 'keywords' => '', 'description' => '', 'slogan' => ''];

    /**
     * 设定 模板类型/主题风格
     *
     * @param string $tpl_type 类型
     * @param string $tpl_type_default 类型(默认)
     * @param string $tpl_theme 主题风格
     * @param string $tpl_theme_default 主题风格(默认)
     */
    static public function theme_set(string $tpl_type = '', string $tpl_type_default = '',
                                     string $tpl_theme = '', string $tpl_theme_default = '')
    {
        // 类型
        empty($tpl_type) || static::$type = $tpl_type;
        // 类型(默认)
        empty($tpl_type_default) || static::$type_default = $tpl_type_default;

        // 主题风格
        empty($tpl_theme) || static::$theme = $tpl_theme;
        // 主题风格(默认)
        empty($tpl_theme_default) || static::$theme_default = $tpl_theme_default;
    }


    /**
     * 设定 模板tpl根目录
     *
     * @param array $paths 模板tpl根目录
     */
    static public function paths_set(array $paths = [])
    {
        // 模板根目录
        if ($paths && is_array($paths)) {
            foreach ($paths as $tpl_dir) {
                // print_r(['__LINE__'=>__LINE__,'$tpl_dir'=>$tpl_dir]);
                if (!in_array($tpl_dir, static::$paths) && is_dir($tpl_dir['path'])) {
                    static::$paths[] = $tpl_dir;
                }
            }
        }
    }

    /**
     * 设定模板替换
     *
     * @param string $key
     * @param string $value
     */
    static public function assign_array_set(string $key, string $value)
    {
        static::$assign_array[$key] = $value;
    }

    /**
     * 设定模板替换
     *
     * @param array|null $data
     */
    static public function assign_array_multi_set(?array $data)
    {
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                static::$assign_array[$key] = $value;
            }
        }
    }

    /**
     * 赋值(默认) $seo + $url
     *
     * @return array
     */
    static public function assign_array_get(): array
    {
        return array_merge([
            '{$page_url}'         => ounun::$page_url,      // $lang/$app_path/$base_url,
            '{$page_file}'        => ounun::$page_file_path,// 基础url,
            // 根目录/面面路径
            '{$page_www}'         => ounun::$page_www,
            '{$page_wap}'         => ounun::$page_wap,
            '{$page_mip}'         => ounun::$page_mip,
            // 根目录
            '{$root_www}'         => ounun::$root_www,
            '{$root_wap}'         => ounun::$root_wap,
            '{$root_mip}'         => ounun::$root_mip,
            '{$root_api}'         => ounun::$root_api,
            // static
            '{$root_res}'         => ounun::$url_res,
            '{$root_upload}'      => ounun::$url_upload, '/public/uploads' => ounun::$url_upload,
            '{$root_static}'      => ounun::$url_static, '/public/static' => ounun::$url_static,
            '{$root_static_g}'    => ounun::$url_static_g, '/public/static_g' => ounun::$url_static_g,
            // seo_site
            '{$site_name}'        => static::$site_tkd_seo['sitename'],
            '{$site_keywords}'    => static::$site_tkd_seo['keywords'],
            '{$site_description}' => static::$site_tkd_seo['description'],
            '{$site_slogan}'      => static::$site_tkd_seo['slogan'],
            // app_name
            '{$app_name}'         => ounun::$app_name,
            '{$app_domain}'       => ounun::$app_domain,
        ], static::$assign_array);
    }

    /**
     * 设定页面的SEO
     *
     * @param string $title
     * @param string $keywords
     * @param string $description
     * @param string $h1
     * @param string $etag
     */
    static public function page_seo_set(string $title = '', string $keywords = '', string $description = '', string $h1 = '', string $etag = '')
    {
        $page_seo = [];
        empty($title) || $page_seo['{$seo_title}'] = $title;
        empty($keywords) || $page_seo['{$seo_keywords}'] = $keywords;
        empty($description) || $page_seo['{$seo_description}'] = $description;
        empty($h1) || $page_seo['{$seo_h1}'] = $h1;
        empty($etag) || $page_seo['{$seo_etag}'] = $etag;
        empty($page_seo) || static::$assign_array = array_merge(static::$assign_array, $page_seo);
    }

    /** @var bool 是否开启ob_start */
    protected static bool $_ob_start = false;
    /** @var string 缓存数据 */
    protected static string $_ob_string;
    /** @var array 回调数组 */
    protected static array $_ob_callbacks = [];

    /** @var string 模板目录(当前) */
    protected string $_current_path;

    /** @var string 插目目录名 */
    protected string $_addon_tag;

    /** @var string 主题风格 */
    protected string $_theme;
    /** @var string 主题风格(默认) */
    protected string $_theme_default;

    /** @var string 模板类型 */
    protected string $_type;
    /** @var string 模板类型(默认为pc) */
    protected string $_type_default;

    /** @var bool 是否去空格 换行 */
    protected bool $_is_trim;

    /**
     * 创建对像 template constructor.
     *
     * @param bool $is_trim 是否去除多余的空格换行
     * @param string|null $theme 风格
     * @param string|null $theme_default 风格(默认)
     * @param string|null $type 类型
     * @param string|null $type_default 模板文件所以目录(默认)
     */
    public function __construct(bool $is_trim = false,
                                ?string $theme = null, ?string $theme_default = null,
                                ?string $type = null, ?string $type_default = null)
    {
        $theme ??= static::$theme;
        if ($theme) {
            $this->_theme = $theme;
        }

        $theme_default ??= static::$theme_default;
        if ($theme_default) {
            $this->_theme_default = $theme_default;
        }

        $type ??= static::$type;
        if ($type) {
            $this->_type = $type;
        }

        $type_default ??= static::$type_default;
        if ($type_default) {
            $this->_type_default = $type_default;
        }

        $this->_current_path = '';
        $this->_is_trim      = $is_trim;

        $this->assign();
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     *
     * @param string $filename
     * @param string $addon_tag
     * @param bool $remember_dir_current
     * @return string
     */
    public function fixed(string $filename, string $addon_tag, bool $remember_dir_current = true): string
    {
        // $types
        if ($this->_type_default && $this->_type != $this->_type_default) {
            $types = [$this->_type, $this->_type_default];
        } else {
            $types = [$this->_type];
        }

        if ($addon_tag) {
            $this->_addon_tag = $addon_tag;
            $addon_tag2       = $addon_tag . '/';
        } else {
            $addon_tag2 = '';
        }
        foreach (static::$paths as $tpl_dir) {
            // print_r($tpl_dir);
            if ('root' == $tpl_dir['type']) {
                // $styles
                if ($this->_theme_default && $this->_theme != $this->_theme_default) {
                    $styles = [$this->_theme, $this->_theme_default];
                } else {
                    $styles = [$this->_theme];
                }
                foreach ($styles as $style) {
                    foreach ($types as $type) {
                        $filename2 = "{$tpl_dir['path']}{$style}/{$type}/{$addon_tag2}{$filename}";
                        // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                        if (is_file($filename2)) {
                            if ($remember_dir_current) {
                                $this->_current_path = dirname($filename2) . '/';
                            }
                            return $filename2;
                        }
                    } // end $types
                } // end $styles
//          }elseif ('addons' == $tpl_dir['type']){
            } else {
                foreach ($types as $type) {
                    $filename2 = "{$tpl_dir['path']}{$addon_tag2}template/{$type}/{$filename}";
                    // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                    if (is_file($filename2)) {
                        if ($remember_dir_current) {
                            $this->_current_path = dirname($filename2) . '/';
                        }
                        return $filename2;
                    }
                }
            }
        }
        if (global_all('debug', false,'template')) {
            $this->error($filename, $addon_tag);
        }
        return '';
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     *
     * @param string $filename
     * @param string $addon_tag
     * @return string
     */
    public function curr(string $filename, string $addon_tag = ''): string
    {
        // curr
        if ($this->_current_path) {
            $filename2 = "{$this->_current_path}{$filename}";
            if (is_file($filename2)) {
                // echo "filename:{$filename2}\n";
                return $filename2;
            }
        }

        // $this->_addon_tag == ''
        if (empty($addon_tag) && $this->_addon_tag) {
            $addon_tag = $this->_addon_tag;
            $filename2 = $this->fixed($filename, '', false);
            if ($filename2) {
                return $filename2;
            }
        }

        return $this->fixed($filename, $addon_tag, false);
    }


    /**
     * 报错
     *
     * @param string $filename
     * @param string $addon_tag
     */
    protected function error(string $filename, string $addon_tag = '')
    {
        echo "<div style='border: #eeeeee 1px dotted;padding: 10px;'>
                    <strong style='padding:0 10px 0 0;color: red;'>Template: </strong>{$filename} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>AddonTag: </strong>{$this->_addon_tag} " . ($addon_tag ? "\$addon_tag:{$addon_tag}" : '') . "<br />
                    <strong style='padding:0 10px 0 0;color: red;'>Style: </strong>{$this->_theme} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Dir_Current: </strong>{$this->_current_path} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Type: </strong>{$this->_type} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Type_Default: </strong>{$this->_type_default} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Dirs: </strong>" . json_encode_unescaped(static::$paths) . " <br />
              </div>";
        error_php("Can't find \$filename:{$filename} \$addon_tag:{$addon_tag}");
    }

    /**
     * 替换
     */
    public function assign()
    {
        if (empty(v::$cache_html) || v::$cache_html->stop) {
            static::ob_start();
            register_shutdown_function([$this, 'callback'], true);
        }
    }

    /**
     * 创建缓存
     * @param bool $output 是否有输出
     */
    public function callback(bool $output = true)
    {
        // 执行
        if ($output) {
            $buffer = ob_get_contents();
            ob_clean();
            ob_implicit_flush(1);
            // 执行回调
            if (static::$_ob_callbacks && is_array(static::$_ob_callbacks)) {
                foreach (static::$_ob_callbacks as $v) {
                    array_push($v[1], $buffer);
                    call_user_func_array($v[0], $v[1]);
                }
            }
            // exit
            exit(static::trim($buffer, $this->_is_trim));
        }
    }

    /**
     * trim
     *
     * @param string $buffer
     * @param bool $is_trim
     * @return string
     */
    static public function trim(string $buffer, bool $is_trim): string
    {
        // 写文件
        if ($is_trim) {
            $buffer = preg_replace_callback('/<script(.*?)>([\s\S]*?)<\/script>/m', function ($matches) {
                $matches_2 = preg_replace(['/<!--[\s\S]*?-->/m', '/\/\*[\s\S]*?\*\//m', '/[^\S]\/\/.*/', '/\s{2,}/m',], ['', '', '', ' ',], $matches[2]);
                return "<script{$matches[1]}>{$matches_2}</script>";
            }, $buffer);
            $buffer = preg_replace_callback('/<style(.*?)>([\s\S]*?)<\/style>/m', function ($matches) {
                $matches_2 = preg_replace(['/\/\*[\s\S]*?\*\//m', '/\s{2,}/m',], ['', '',], $matches[2]);
                return "<style{$matches[1]}>{$matches_2}</style>";
            }, $buffer);

            $pattern     = ['/\s{2,}/', '/>\s?</', '/\"\s?' . '>/'];
            $replacement = [' ', '><','">'];
            $buffer      = preg_replace($pattern, $replacement, $buffer);
        }

        // 替换
        return strtr($buffer, static::assign_array_get());
    }

    /**
     * 变量替换
     *
     * @param string $subject 要替换的字符串
     * @param array $data
     * @param string|null $regex
     * @return string
     */
    static public function replace(string $subject, array $data, ?string $regex = null): string
    {
        $regex ??= '/\{\$(\w+)\}/';
        return preg_replace_callback($regex, function ($var) use ($data) {
            return $data[$var[1]] ?? $var[0];
        }, $subject);
    }

    /**
     * 开启 ob_start
     */
    static public function ob_start()
    {
        if (empty(static::$_ob_start)) {
            ob_start();
            static::$_ob_start = true;
        }
    }

    /**
     * 设定 回调函数
     * @param callable $callback
     * @param array $param_arr
     */
    static public function ob_func(callable $callback, array $param_arr)
    {
        static::$_ob_callbacks[] = [$callback, $param_arr];
        // echo __METHOD__.':'.count(static::$_ob_callbacks)."\n";
    }

    /**
     * ob_start 状态
     */
    static public function ob_status(): bool
    {
        return static::$_ob_start;
    }
}
