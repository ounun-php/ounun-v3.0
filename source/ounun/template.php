<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun;


/**
 * Class template
 * @package ounun
 */
class template
{
    /** @var string Api接口Rest */
    const Type_Api_Rest = '';
    /** @var string Pc网页www */
    const Type_Pc       = 'pc';
    /** @var string H5网页wap */
    const Type_Wap      = 'wap';
    /** @var string Mip网页 */
    const Type_Mip      = 'mip';
    /** @var string control后台 */
    const Type_Control  = 'control';
    /** @var array 模板类型 */
    const Types = [
        self::Type_Api_Rest ,
        self::Type_Pc  ,
        self::Type_Wap ,
        self::Type_Mip ,
        self::Type_Control ,
    ];

    /** @var bool 是否开启ob_start */
    static protected $_ob_start = false;

    /** @var string 模板目录(当前) */
    protected $_dir_current;

    /** @var string 风格 */
    protected $_style;

    /** @var string 插目目录名 */
    protected $_addon_tag;

    /** @var string 模板类型(当前) */
    protected $_type_current;
    /** @var string 模板类型 */
    protected $_type;
    /** @var string 模板类型(默认为pc) */
    protected $_type_default;

    /** @var bool 是否去空格 换行 */
    protected $_is_trim = false;

    /**
     * 创建对像 template constructor.
     * @param string $tpl_style 风格
     * @param string $tpl_type  类型
     * @param string $tpl_type_default 模板文件所以目录(默认)
     * @param bool $is_trim
     */
    public function __construct(string $tpl_style = '',string $tpl_type = '', string $tpl_type_default = '', bool $is_trim = false)
    {
        if($tpl_style){
            $this->_style = $tpl_style;
        }

        if($tpl_type){
            $this->_type = $tpl_type;
        }
        if($tpl_type_default){
            $this->_type_default = $tpl_type_default;
        }

        $this->_dir_current = '';
        $this->_type_default = '';
        $this->_is_trim = $is_trim;

        $this->replace();
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param string $filename
     * @param string $addon_tag
     * @param array $types
     * @return string
     */
    public function tpl_fixed(string $filename, string $addon_tag, array $types = [], bool $show_debug = true): string
    {
        //  print_r(['\ounun::$tpl_dirs'=>\ounun::$tpl_dirs,'\ounun::$maps_paths'=>\ounun::$maps_paths]);
        $types = $types ? $types : [$this->_type, $this->_type_default];
        $addon_tag2 = $addon_tag ? "{$addon_tag}/" :'';
        foreach (\ounun::$tpl_dirs as $tpl_dir) {
            // print_r($tpl_dir);
            if('root' == $tpl_dir['type']){
                foreach ($types as $type) {
                    $filename2 = "{$tpl_dir['path']}{$this->_style}/{$type}/{$addon_tag2}{$filename}";
                    // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                    if (is_file($filename2)) {
                        $this->_dir_current = dirname($filename2) . '/';
                        $this->_addon_tag = $addon_tag;
                        $this->_type_current = $type;
                        // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                        return $filename2;
                    }
                }
            }elseif ('app' == $tpl_dir['type']){
                foreach ($types as $type) {
                    $filename2 = "{$tpl_dir['path']}".\ounun::$app_name."/template/{$type}/{$addon_tag2}{$filename}";
                    // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                    if (is_file($filename2)) {
                        $this->_dir_current = dirname($filename2) . '/';
                        $this->_addon_tag = $addon_tag;
                        $this->_type_current = $type;
                        // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                        return $filename2;
                    }
                }
            // }elseif ('app' == $tpl_dir['type']){
            }else{
                foreach ($types as $type) {
                    $filename2 = "{$tpl_dir['path']}{$addon_tag2}/template/{$type}/{$filename}";
                    //  echo "line:".__LINE__." filename:{$filename2} <br />\n";
                    if (is_file($filename2)) {
                        $this->_dir_current = dirname($filename2) . '/';
                        $this->_addon_tag = $addon_tag;
                        $this->_type_current = $type;
                        // echo "line:".__LINE__." filename:{$filename2} <br />\n";
                        return $filename2;
                    }
                }
            }

        }
        if($show_debug){
            $this->error($filename, $addon_tag);
        }
        return '';
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param string $filename
     * @param string $addon_tag
     * @return string
     */
    public function tpl_curr(string $filename, string $addon_tag = ''): string
    {
        // curr
        if ($this->_dir_current) {
            $filename2 = "{$this->_dir_current}{$filename}";
            if (is_file($filename2)) {
                // echo "filename:{$filename2}\n";
                return $filename2;
            }
        }

        // fixed
        if ($this->_type_current) {
            if ($this->_type_current == $this->_type_default) {
                $styles = [$this->_type_default, $this->_type];
            } else {
                $styles = [$this->_style, $this->_type_default];
            }
        } else {
            $styles = [$this->_style, $this->_type_default];
        }

        return $this->tpl_fixed($filename,  $addon_tag,$styles);
    }


    /**
     * 报错
     * @param string $filename
     * @param string $addon_tag
     */
    protected function error(string $filename, string $addon_tag = '')
    {
        echo "<div style='border: #eeeeee 1px dotted;padding: 10px;'>
                    <strong style='padding:0 10px 0 0;color: red;'>Template: </strong>{$filename} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>AddonTag: </strong>{$addon_tag} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Style: </strong>{$this->_style} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Type: </strong>{$this->_type} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Type_Current: </strong>{$this->_type_current} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Type_Default: </strong>{$this->_type_default} <br />
                    <strong style='padding:0 10px 0 0;color: red;'>Dirs: </strong>".json_encode_unescaped(\ounun::$tpl_dirs)." <br />
              </div>";
        trigger_error("Can't find Template:{$filename}", E_USER_ERROR);
    }

    /**
     * 替换
     * @param bool $trim
     */
    public function replace()
    {
        if (empty(\v::$cache_html) || \v::$cache_html->stop) {
            // ob_start();
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
        if($output){
            $buffer = ob_get_contents();
            ob_clean();
            ob_implicit_flush(1);

            exit(static::trim($buffer,$this->_is_trim));
        }
    }

    /**
     * @param string $buffer
     * @param bool $is_trim
     * @return string
     */
    static public function trim(string $buffer,bool $is_trim)
    {
        // 写文件
        if ($is_trim) {
            /*            $pattern = ['/<!--.*?-->/', '/[^:\-\"]\/\/[^\S].*?\n/', '/\/\*.*?\*\//', '/[\n\r\t]*?/', '/\s{2,}/', '/>\s?</', '/<!--.*?-->/', '/\"\s?>/'];*/
//            $replacement = ['', '', '', '', ' ', '><', '', '">'];
//            $buffer = preg_replace($pattern, $replacement, $buffer);
            $buffer = preg_replace_callback('/\<script(.*?)\>([\s\S]*?)<\/script\>/m', function ($matches) {
                $matches_2 = preg_replace(['/<!--[\s\S]*?-->/m', '/\/\*[\s\S]*?\*\//m', '/[^\S]\/\/.*/', '/\s{2,}/m',], ['', '', '', ' ',], $matches[2]);
                return "<script{$matches[1]}>{$matches_2}</script>";
            }, $buffer);
            $buffer = preg_replace_callback('/\<style(.*?)\>([\s\S]*?)<\/style\>/m', function ($matches) {
                $matches_2 = preg_replace(['/\/\*[\s\S]*?\*\//m', '/\s{2,}/m',], ['', '',], $matches[2]);
                return "<style{$matches[1]}>{$matches_2}</style>";
            }, $buffer);

            $pattern = ['/\s{2,}/', '/>\s?</', '/\"\s?' . '>/'];
            $replacement = [' ', '><', '">'];
            $buffer = preg_replace($pattern, $replacement, $buffer);
        }

        // 替换
        return strtr($buffer, \ounun::template_replace_str_get());
    }

    /**
     * 开启 ob_start
     */
    static public function ob_start()
    {
        if(empty(static::$_ob_start)){
            ob_start();
            static::$_ob_start = true;
        }
        return;
    }
}
