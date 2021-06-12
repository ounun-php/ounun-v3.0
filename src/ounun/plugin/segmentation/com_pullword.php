<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\plugin\segmentation;


class com_pullword
{

    /**
     * 中文分词API
     *   注：所有数据（输入和输出）必须是utf8编码的字符串。支持json
     *
     * @param string $text  要分词的语句
     * @param float $score  保留准确概率   0 保留所有单词    0.5 保留准确率大于50%的单词(推荐) 1 只保留准确率为100%的单词
     * @param bool $is_json 是否json      1 以json格式返回   0 不以json格式返回
     * @return mixed
     */
    public static function tag(string $text,float $score = 0.55,bool $is_json = true)
    {
        $rs   = [];
        // param2 = 0 调试模式关闭   param2 = 1调试模式打开（显示每个单词的准确概率）
        $url2 = 'http://api.pullword.com/get.php?source='.urlencode($text).'&param1='.$score.'&param2=1&json=' . ($is_json?'1':'0');
        $c2 = file_get_contents($url2);
        $c3 = json_decode($c2, true);
        if ($c3 && $c3['t'] && $c3['p']) {
            return $rs[] = ['tag_name'=>$c3['t'],'score'=>$c3['p']];
        }
        return $rs;
    }
}
