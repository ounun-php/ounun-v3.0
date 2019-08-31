<?php
namespace ounun\utils;

class url
{
    /**
     * @param array $data
     * @param int $len
     * @param string $a_ext
     * @return string
     */
    static  public function a(array $data, int $len = 0, string $a_ext = ''): string
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
                $ext = $v['ext'] ? $v['ext'] : '';
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
    static  public function a_s(array $urls, string $glue = "", int $len = 0): string
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
            $url = static::$url_fun($id);
            $rs[] = [
                'title' => $title,
                'url' => $url
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
    static  public function kv2a_s(array $array, string $url_fun, string $glue = ""): string
    {
        $rs = static::kv2a_m($array, $url_fun, true);
        return implode($glue, $rs);
    }
}
