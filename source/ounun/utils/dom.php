<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\utils;

class dom
{
    /**
     * @param array $data
     * @param int $len
     * @param string $a_ext
     * @return string
     */
    static public function a(array $data, int $len = 0, string $a_ext = ''): string
    {
        $tag = $len ? str::msubstr($data['title'], 0, $len, true) : $data['title'];
        return "<a href=\"{$data['url']}\" title=\"{$data['title']}\" {$a_ext}>{$tag}</a>";
    }

    /**
     * @param array $urls
     * @param int $len
     * @return array
     */
    static public function a_m(array $urls, int $len = 0): array
    {
        $rs = [];
        foreach ($urls as $v) {
            if ($v['url'] && $v['title']) {
                $ext  = $v['ext'] ? $v['ext'] : '';
                $rs[] = static::a($v, $len, $ext);
            }
        }
        return $rs;
    }

    /**
     * @param array $urls
     * @param string $glue
     * @param int $len
     * @return string
     */
    static public function a_s(array $urls, string $glue = "", int $len = 0): string
    {
        $rs = static::a_m($urls, $len);
        return implode($glue, $rs);
    }

    /**
     * @param array $array
     * @param string $url_fun
     * @param bool $is_html
     * @return array
     */
    static public function kv2a_m(array $array, string $url_fun, $is_html = true): array
    {
        $rs = [];
        foreach ($array as $id => $title) {
            $url  = static::$url_fun($id);
            $rs[] = [
                'title' => $title,
                'url'   => $url
            ];
        }
        if ($is_html) {
            $rs = static::a_m($rs);
        }
        return $rs;
    }

    /**
     * 输出连接好的 html
     * @param array $array
     * @param string $url_fun
     * @param string $glue
     * @return string
     */
    static public function kv2a_s(array $array, string $url_fun, string $glue = ""): string
    {
        $rs = static::kv2a_m($array, $url_fun, true);
        return implode($glue, $rs);
    }

    /**
     * 获取标题颜色
     * @param string $str
     * @param string $color
     * @return string
     */
    static public function color_text(string $str, string $color = ''): string
    {
        if ($color) {
            return "<span style=\"color: {$color}\">{$str}</span>";
        } else {
            return $str;
        }
    }

    /**
     * 获取特定时时间颜色
     * @param string $type
     * @param int $time
     * @param string $color
     * @param int $interval
     * @return string
     */
    static public function color_date(string $type = 'Y-m-d H:i:s', int $time = 0, string $color = 'red', int $interval = 86400): string
    {
        if ((time() - $time) > $interval) {
            return date($type, $time);
        } else {
            return self::color_text(date($type, $time), $color);
        }
    }
}
