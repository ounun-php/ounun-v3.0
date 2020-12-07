<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\seo;

/** 本插件所在目录 */
define('Dir_Plugins_Pinyin', __DIR__ . '/');

/**
 * 汉字转拼音
 */
class pinyin_gbk
{
    protected array $_data = [];

    /**
     * Constructor
     *
     * Simply globalizes the $RTR object.  The front
     * loads the Router class early on so it's not available
     * normally as other classes are.
     *
     */
    public function __construct()
    {
        $fp = fopen(Dir_Plugins_Pinyin . 'res/pinyin.dat', 'r');
        while (!feof($fp)) {
            $line                             = trim(fgets($fp));
            $this->_data[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
        }
        fclose($fp);
    }

    /**
     * 汉字转拼音
     * @param string $string 要转换的汉字
     * @param string $from_encoding 汉字编码
     * @param bool $initial 首字母是否大写
     * @param string $space 拼音之间的间隔
     * @return string
     */
    public function convert(string $string, $from_encoding = 'gbk', $initial = true, $space = ''): string
    {
        $py = $this->pinyin($string, $from_encoding);
        if ($initial) {
            $rs = [];
            foreach ($py as $v) {
                $rs[] = ucfirst($v);
            }
            $py = $rs;
        }
        return implode($space, $py);
    }

    /**
     * 提取汉字声母（每个字拼音的第一个字母）
     * @param string $string 要提取汉字
     * @param string $from_encoding 汉字编码
     * @return string
     */
    public function head(string $string, $from_encoding = 'gbk'): string
    {
        $rs = [];
        $py = $this->pinyin($string, $from_encoding);
        foreach ($py as $v) {
            $rs[] = substr($v, 0, 1);
        }
        return implode('', $rs);
    }

    /**
     * 返回一个数组(一般不用这个)
     * @param string $string
     * @param string $from_encoding
     * @return  array <string, $string>
     */
    public function pinyin(string $string, $from_encoding = 'gbk'): array
    {
        if ($from_encoding != 'gbk') {
            $string = mb_convert_encoding($string, 'gbk', $from_encoding);
        }
        $_res = [];
        for ($i = 0; $i < strlen($string); $i++) {
            $_P = ord($string[$i]);
            if ($_P > 0x80) {
                $c = $string[$i] . $string[$i + 1];
                $i++;
                if (isset($this->_data[$c])) {
                    $_res[] = $this->_data[$c];
                }
            } else {
                $_res[] = $string[$i];
            }
        }
        return $_res;
    }
    //echo Pinyin('第二个参数随意设置',2);
}
// $py = new CI_Pinyin();
// $rs  = $py->convert('第23rtg二个参数随意设置','utf-8',false,'');

// var_dump($rs);
// END URI Class

/* End of file URI.php */
/* Location: ./system/core/URI.php */
