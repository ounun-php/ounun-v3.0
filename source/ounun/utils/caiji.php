<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\utils;


use ounun\client\proxy;

class caiji
{
    /**
     * 获取目录内容(左边)
     *
     * @param string $content 所在内容
     * @param string $left 目标内容左边标识点
     * @param string $right 目标内容右边标识点
     * @return string
     */
    static public function left(string $content, string $left, string $right)
    {
        return explode($right, explode($left, $content, 2)[1], 2)[0];
    }

    /**
     * 获取目录内容(右边)
     *
     * @param string $content 所在内容
     * @param string $right 目标内容右边标识点
     * @param string $left 目标内容左边标识点
     * @return string
     */
    static public function right(string $content, string $right, string $left)
    {
        return explode($left, explode($right, $content, 2)[0], 2)[1];
    }

    /**
     * 获取目录内容(左右两边边)
     *
     * @param string $content 所在内容
     * @param string $left 目标内容左边标识点
     * @param string $right 目标内容右边标识点
     * @return string
     */
    static public function left_right(string $content, string $left, string $right)
    {
        $pos = strpos($content, $left);
        if ($pos !== false) {
            $content = substr($content, $pos + strlen($left));
        }
        $pos = strrpos($content, $right);
        if ($pos === false) {
            return $content;
        }
        return substr($content, 0, $pos);
    }

    /**
     * 获取目录内容(左边)
     *
     * @param string $content 所在内容
     * @param string $middle 目标内容分格点
     * @param string $left 目标内容左边标识点
     * @param string $right 目标内容右边标识点
     * @return array
     */
    static public function list_left(string $content, string $middle, string $left, string $right)
    {
        $rs = [];
        $c2 = explode($middle, $content);
        foreach ($c2 as $v2) {
            $v3 = self::left($v2, $left, $right);
            if ($v3) {
                $rs[] = $v3;
            }
        }
        return $rs;
    }

    /**
     * 获取目录内容(右边)
     *
     * @param string $content 所在内容
     * @param string $middle 目标内容分格点
     * @param string $right 目标内容右边标识点
     * @param string $left 目标内容左边标识点
     * @return array
     */
    static public function list_right(string $content, string $middle, string $right, string $left)
    {
        $rs = [];
        $c2 = explode($middle, $content);
        foreach ($c2 as $v2) {
            $v3 = self::right($v2, $right, $left);
            if ($v3) {
                $rs[] = $v3;
            }
        }
        return $rs;
    }

    /**
     * 获取目录内容
     *
     * @param string $content 所在内容
     * @param string $middle 目标内容分格点
     * @param array $rules 分析规则 ['key'=> 主键, 'type' => <'left'默认,'right'> , 'left' => $left, 'right'=>$right]
     * @return mixed
     */
    static public function list(string $content, string $middle, array $rules)
    {
        $rs   = [];
        $c2   = explode($middle, $content);
        $key0 = $rules[0]['key'];
        foreach ($c2 as $v2) {
            if ($v2) {
                $rs2 = [];
                foreach ($rules as ['key' => $key, 'type' => $type, 'left' => $left, 'right' => $right]) {
                    if ('right' == $type) {
                        $rs2[$key] = self::right($v2, $right, $left);
                    } else {
                        $rs2[$key] = self::left($v2, $left, $right);
                    }
                }
                if ($rs2[$key0]) {
                    $rs[] = $rs2;
                }
            }
        }
        return $rs;
    }

    /**
     * 取出正则数据
     *
     * @param  $pattern string
     *      网址: <a href="(http://:any)">(:any)</a>
     *      网址: <img src="(http://:any)" :any?/>
     * @param  $subject string
     * @return mixed
     */
    static public function preg_match_all(string $pattern, string $subject)
    {
        $matches = [];
        preg_match_all('/' . $pattern . '/', $subject, $matches, PREG_SET_ORDER);
        return $matches;
    }

    /**
     * 正则提取正文里指定的第几张图片地址
     *
     * @param string $content
     * @return array
     */
    static public function img_urls(string $content): array
    {
        preg_match_all('/<img.*?src="(.*?)"/si', $content, $imgarr);///(?<=img.src=").*?(?=")/si
        // print_r($imgarr[1]);
        // preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
        return $imgarr[1];
    }

    /**
     * 随机生成国内ip
     *
     * @return string
     */
    static public function rand_inland_ip()
    {
        $ip_long  = [
            [607649792, 608174079], //36.56.0.0-36.63.255.255
            [975044608, 977272831], //58.30.0.0-58.63.255.255
            [999751680, 999784447], //59.151.0.0-59.151.127.255
            [1019346944, 1019478015], //60.194.0.0-60.195.255.255
            [1038614528, 1039007743], //61.232.0.0-61.237.255.255
            [1783627776, 1784676351], //106.80.0.0-106.95.255.255
            [1947009024, 1947074559], //116.13.0.0-116.13.255.255
            [1987051520, 1988034559], //118.112.0.0-118.126.255.255
            [2035023872, 2035154943], //121.76.0.0-121.77.255.255
            [2078801920, 2079064063], //123.232.0.0-123.235.255.255
            [-1950089216, -1948778497], //139.196.0.0-139.215.255.255
            [-1425539072, -1425014785], //171.8.0.0-171.15.255.255
            [-1236271104, -1235419137], //182.80.0.0-182.92.255.255
            [-770113536, -768606209], //210.25.0.0-210.47.255.255
            [-569376768, -564133889], //222.16.0.0-222.95.255.255
        ];
        $rand_key = mt_rand(0, 14);
        return long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    }


    /**
     * 随机 显示次数
     *
     * @return string
     */
    static public function rand_views()
    {
        $ip_long  = [
            [1000, 100000],
            [5000, 500000],
            [20000, 100000],
            [30000, 150000],
            [20000, 200000],
            [20000, 250000],
            [20000, 300000],
            [50000, 350000],
            [20000, 400000],
            [20000, 500000],
            [20000, 550000],
            [20000, 600000],
            [20000, 650000],
            [20000, 700000],
            [20000, 750000],
        ];
        $rand_key = mt_rand(0, 14);
        return mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]);
    }

    /**
     * 获取html代码
     *
     * @param $url
     * @param null $headers 键值对形式
     * @param array $options
     * @param string $from_encode
     * @param null $post_data 通过isset判断是否是post模式
     * @return bool|string|null
     */
    static public function html_get($url, $headers = null, $options = [], $from_encode = 'auto', $post_data = null)
    {
        $headers = is_array($headers) ? $headers : [];
        $options = is_array($options) ? $options : [];
        if (!isset($options['useragent'])) {
            $options['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70 Safari/537.36';
        }
        $options['timeout'] = $options['timeout'] > 0 ? $options['timeout'] : 30;
        $curl_headers       = [];
        foreach ($headers as $k => $v) {
            $curl_headers[] = $k . ': ' . $v;
        }
        $headers = $curl_headers;
        unset($curl_headers);
        if (!preg_match('/^\w+:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        $curl = null;
        try {
            if (!isset($post_data)) {
                $allow_get = true;
                if (!empty($options['max_bytes'])) {
                    $max_bytes = intval($options['max_bytes']);
                    unset($options['max_bytes']);
                    $curl = proxy::head($url, $headers, $options);
                    if (preg_match('/\bContent-Length\s*:\s*(\d+)/i', $curl->header, $contLen)) {
                        $contLen = intval($contLen[1]);
                        if ($contLen >= $max_bytes) {
                            $allow_get = false;
                        }
                    }
                }
                if ($allow_get) {
                    $curl = proxy::get($url, $headers, $options);
                } else {
                    $curl = null;
                }
            } else {

                if (!empty($post_data) && !empty($from_encode) && !in_array(strtolower($from_encode), array('auto', 'utf-8', 'utf8'))) {

                    if (!is_array($post_data)) {
                        if (preg_match_all('/([^\&]+?)\=([^\&]*)/', $post_data, $m_post_data)) {
                            $new_post_data = array();
                            foreach ($m_post_data[1] as $k => $v) {
                                $new_post_data[$v] = rawurldecode($m_post_data[2][$k]);
                            }
                            $post_data = $new_post_data;
                        } else {
                            $post_data = array();
                        }
                    }
                    $post_data = is_array($post_data) ? $post_data : array();
                    foreach ($post_data as $k => $v) {
                        $post_data[$k] = mb_convert_encoding($v, 'utf-8//IGNORE', $from_encode);
                        // $post_data[$k] = iconv ( 'utf-8', $fromEncode.'//IGNORE', $v );
                    }
                }

                $curl = proxy::post($url, $headers, $options, $post_data);
            }
        } catch (\Exception $e) {
            $curl = null;
        }
        $html = null;

        if (!empty($curl)) {
            if ($curl->is_ok) {

                $html = $curl->body;
                if ($from_encode == 'auto') {
                    $htmlCharset = [];
                    if (preg_match('/<meta[^<>]*?content=[\'\"]text\/html\;\s*charset=(?P<charset>[^\'\"\<\>]+?)[\'\"]/i', $html, $htmlCharset) || preg_match('/<meta[^<>]*?charset=[\'\"](?P<charset>[^\'\"\<\>]+?)[\'\"]/i', $html, $htmlCharset)) {
                        $htmlCharset = strtolower(trim($htmlCharset['charset']));
                        if ('utf8' == $htmlCharset) {
                            $htmlCharset = 'utf-8';
                        }
                    } else {
                        $htmlCharset = '';
                    }
                    $headerCharset = [];
                    if (preg_match('/\bContent-Type\s*:[^\r\n]*charset=(?P<charset>[\w\-]+)/i', $curl->header, $headerCharset)) {
                        $headerCharset = strtolower(trim($headerCharset['charset']));
                        if ('utf8' == $headerCharset) {
                            $headerCharset = 'utf-8';
                        }
                    } else {
                        $headerCharset = '';
                    }
                    if (!empty($htmlCharset) && !empty($headerCharset) && strcasecmp($htmlCharset, $headerCharset) !== 0) {

                        $zhCharset = array('gb18030', 'gbk', 'gb2312');
                        if (in_array($htmlCharset, $zhCharset) && in_array($headerCharset, $zhCharset)) {
                            $from_encode = 'gb18030';
                        } else {
                            $autoEncode = mb_detect_encoding($html, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5']);
                            if (strcasecmp($htmlCharset, $autoEncode) == 0) {
                                $from_encode = $htmlCharset;
                            } elseif (strcasecmp($headerCharset, $autoEncode) == 0) {
                                $from_encode = $headerCharset;
                            } else {
                                $from_encode = $autoEncode;
                            }
                        }
                    } elseif (!empty($htmlCharset)) {
                        $from_encode = $htmlCharset;
                    } elseif (!empty($headerCharset)) {
                        $from_encode = $headerCharset;
                    } else {
                        $from_encode = mb_detect_encoding($html, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                    }
                    $from_encode = empty($from_encode) ? null : $from_encode;
                }
                $from_encode = trim($from_encode);

                if (!empty($from_encode)) {
                    $from_encode = strtolower($from_encode);
                    switch ($from_encode) {
                        case 'utf8'   :
                            $from_encode = 'utf-8';
                            break;
                        case 'cp936'  :
                            $from_encode = 'gbk';
                            break;
                        case 'cp20936':
                            $from_encode = 'gb2312';
                            break;
                        case 'cp950'  :
                            $from_encode = 'big5';
                            break;
                    }
                    if ($from_encode != 'utf-8') {
                        $html = mb_convert_encoding($html, 'utf-8//IGNORE', $from_encode);
                        // $html = iconv ( $fromEncode, 'utf-8//IGNORE', $html );
                    }
                }
            }
        }
        return isset($html) ? $html : false;
    }
}
