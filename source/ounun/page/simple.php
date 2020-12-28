<?php


namespace ounun\page;


class simple
{
    /** @var Callable 计算总量的功能函数 */
    protected $_fn_total;

    /** @var string  提示串 */
    protected string $_config_note = '总共有{total}条数据,共{total_page}页,第{page}页';
    /** @var array   默认页 */
    protected array $_config_page_tag_default = ['<li>', '</li>', ''];
    /** @var array   当前页面时 */
    protected array $_config_page_tag_curr = ['<li class="now">', '</li>', ''];
    /** @var array   第一页 上一页 下一页 最后一页   ['|&lt;','&lt;','&gt;','&gt;|']; */
    protected array $_config_page_tag_name = ['第一页', '上一页', '下一页', '最后一页'];
    /** @var array   第一页 */
    protected array $_config_index = [];
    /** @var int     最多显示几页 */
    protected int $_config_show_max = 9;
    /** @var int     一页显示几条数据 */
    protected int $_config_rows = 20;
    /** @var bool 是否最后一页为首页  false:第一页为首页  true:最后一页为首页 */
    protected bool $_page_end_index = false;
    /** @var string */
    protected string $_url;

    /** @var int 数量总量 */
    protected int $_total;
    /** @var int 页数总量(除去首页) */
    protected int $_total_page = 0;
    /** @var int 页数总量(总数) */
    protected int $_total_page_real = 1;

    /** @var int 当前所在页数 */
    protected int $_page_curr = 1;

    /**
     * 创建一个分页类
     * page constructor.
     *
     * @param string $url
     * @param array $where
     * @param array $config
     */
    public function __construct(string $url, array $config = [])
    {
        $this->_url = $url;
        $this->config_set($config);
    }

    /**
     * 设定总接口
     *
     * @param array $config
     * @return self
     */
    public function config_set(array $config): self
    {
        if ($config && is_array($config)) {
            foreach (['note', 'page_tag_default', 'page_tag_curr', 'page_tag_name', 'show_max', 'rows', 'index'] as $key) {
                if (isset($config[$key])) {
                    $m        = "_config_{$key}";
                    $this->$m = $config[$key];
                }
            }
        }
        return $this;
    }

    /**
     * 得到分页数据
     *
     * @param int $page
     * @param string $title
     * @param bool $end_index
     * @return array
     */
    public function initialize(int $page = 0, string $title = "", bool $end_index = false): array
    {
        $tag_default = $this->_config_page_tag_default;
        $tag_curr    = $this->_config_page_tag_curr;
        $tag_name    = $this->_config_page_tag_name;

        $title = $title ? "{$title}-" : '';
        $pages = [];

        $data = $this->_data($page, $end_index);
        $note = $this->_note_set();

        $url_prev = '';
        $url_next = '';
        foreach ($data as $v) {
            if ($v['begin']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['begin']) . '" title="' . $title . '第' . $v['begin'] . '页" ' . $tag_default[2] . '>' . htmlspecialchars($tag_name[0]) . '</a>' . $tag_default[1];
            } elseif ($v['previous']) {
                $url_prev = $this->_url_set($v['previous']);
                $pages[]  = $tag_default[0] . '<a href="' . $url_prev . '" title="' . $title . '第' . $v['previous'] . '页" ' . $tag_default[2] . '>' . htmlspecialchars($tag_name[1]) . '</a>' . $tag_default[1];
            } elseif ($v['next']) {
                $url_next = $this->_url_set($v['next']);
                $pages[]  = $tag_default[0] . '<a href="' . $url_next . '" title="' . $title . '第' . $v['next'] . '页" ' . $tag_default[2] . '>' . htmlspecialchars($tag_name[2]) . '</a>' . $tag_default[1];
            } elseif ($v['end']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['end']) . '" title="' . $title . '第' . $v['end'] . '页" ' . $tag_default[2] . '>' . htmlspecialchars($tag_name[3]) . '</a>' . $tag_default[1];
            } elseif ($v['default']) {
                if ($this->_page_curr == $v['default']) {
                    $pages[] = $tag_curr[0] . '<a href="' . $this->_url_set($v['default']) . '" title="' . $title . '第' . $v['default'] . '页" ' . $tag_curr[2] . ' onclick="return false">' . $v['default'] . '</a>' . $tag_curr[1];
                } else {
                    $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['default']) . '" title="' . $title . '第' . $v['default'] . '页" ' . $tag_default[2] . '>' . $v['default'] . '</a>' . $tag_default[1];
                }
            } elseif ($v['index']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set(0) . '" title="' . $title . $tag_name[4] . '" ' . $tag_default[2] . '>' . htmlspecialchars($tag_name[4]) . '</a>' . $tag_default[1];
            }
        }
        return [
            'url_prev' => $url_prev,
            'url_next' => $url_next,

            'page_total' => $this->_total_page,
            'page_curr'  => $this->_page_curr,

            'note' => $note,
            'page' => $pages
        ];
    }

    /**
     * 算出分页数据
     *
     * @param int $page_curr
     * @param bool $end_index
     * @return array
     */
    protected function _data(int $page_curr = 0, bool $end_index = false): array
    {
        $page_middle = ceil($this->_config_show_max / 2);

        $this->_total           = $this->total_size();
        $this->_total_page_real = ceil($this->_total / $this->_config_rows);
        if ($end_index) {
            $this->_total_page = $this->_total_page_real - 1;
        } else {
            $this->_total_page = $this->_total_page_real;
        }

        $page_curr = $end_index ? ($page_curr < 1 ? 0 : $page_curr) : ($page_curr < 1 ? 1 : $page_curr);
        $page_curr = $page_curr > $this->_total_page ? $this->_total_page : $page_curr;

        $this->_page_curr      = $page_curr;
        $this->_page_end_index = $end_index;

        if ($this->_total_page > $this->_config_show_max) {
            $sub_total = $this->_config_show_max;
            $sub_begin = true;
            $sub_end   = true;
            if ($page_curr <= $page_middle) {
                $sub_begin = false;
                $sub_start = 1;
            } elseif ($this->_total_page - $page_curr < $page_middle) {
                $sub_end   = false;
                $sub_start = $this->_total_page - $this->_config_show_max + 1;
            } else {
                $sub_start = $page_curr - $page_middle + 1;
            }
        } else {
            $sub_total = $this->_total_page;
            $sub_begin = false;
            $sub_end   = false;
            $sub_start = 1;
        }
        $sub_index    = $page_curr > 0;
        $sub_next     = $page_curr < $this->_total_page && $this->_total_page > 1;
        $sub_previous = $page_curr > 1 && $this->_total_page > 1;

        // 载入np数据
        $rs = [];
        $sub_index && $rs[] = ['index' => 100000000];
        $sub_begin && $rs[] = ['begin' => 1];
        $sub_previous && $rs[] = ['previous' => $page_curr - 1];
        for ($i = $sub_start; $i < $sub_start + $sub_total; $i++) {
            $rs[] = ['default' => $i];
        }
        $sub_next && $rs[] = ['next' => $page_curr + 1];
        $sub_end && $rs[] = ['end' => $this->_total_page];
        return $rs;
    }

    /**
     * 得到数据总行数
     *
     * @return int
     */
    public function total_size(): int
    {
        if (isset($this->_total)) {
            return $this->_total;
        }
        $this->_total = ($this->_fn_total)();
        return $this->_total;
    }

    /**
     * 得到数据总页数
     *
     * @return int
     */
    public function total_page(): int
    {
        return $this->_total_page;
    }

    /**
     * 当前所在页数
     *
     * @return int
     */
    public function page_curr(): int
    {
        return $this->_page_curr;
    }

    /**
     * 翻页排序  false:1...max  true:max...1
     *
     * @return int
     */
    public function page_end_index(): int
    {
        return $this->_page_end_index;
    }

    /**
     * @return int
     */
    public function limit_length(): int
    {
        return $this->_config_rows;
    }

    /**
     * @return int
     */
    public function limit_offset(): int
    {
        if ($this->_page_end_index && $this->_page_curr == 0) {
            $start = $this->_total - $this->_config_rows;
        } else {
            $start = ($this->_page_curr - 1) * $this->_config_rows;
        }
        return $start < 0 ? 0 : $start;
    }

    /**
     * 设定字符串
     *
     * @return string
     */
    protected function _note_set(): string
    {
        $replace = [$this->_total, $this->_total_page_real, $this->_page_curr];
        return str_replace(['{total}', '{total_page}', '{page}'], $replace, $this->_config_note);
    }

    /**
     * 设定URL串
     *
     * @param int $page
     * @return string
     */
    protected function _url_set(int $page): string
    {
        $search  = [];
        $replace = [];
        $url     = str_replace('{page}', $page, $this->_url);
        if ($this->_config_index) {
            if ($this->_page_end_index) {
                if ($page == 0) {
                    foreach ($this->_config_index as $v) {
                        $search[]  = str_replace('{total_page}', $this->_total_page, $v[0]);
                        $replace[] = $v[1];
                    }
                }
            } else {
                if (1 == $page) {
                    foreach ($this->_config_index as $v) {
                        $search[]  = $v[0];
                        $replace[] = $v[1];
                    }
                }
            }
        }
        if ($search) {
            $url = str_replace($search, $replace, $url);
        }
        return $url;
    }

    /**
     * @param callable $fn
     * @return $this
     */
    public function fn_total_set(callable $fn):self
    {
        $this->_fn_total = $fn;
        return $this;
    }
}
