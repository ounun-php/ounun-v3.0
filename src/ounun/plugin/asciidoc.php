<?php

namespace ounun\plugin;

class asciidoc
{
    protected string $_html_str;

    public function __construct(?string $html_str = null)
    {
        $this->_html_str = $html_str;
    }

    /**
     * @param string $html_str
     * @return void
     */
    public function html_str(string $html_str)
    {
        $this->_html_str = $html_str;
    }

    /**
     * @param string|null $html_str
     * @return string
     */
    public function to_asciidoc(?string $html_str = null): string
    {
        if ($html_str) {
            $this->html_str($html_str);
        }

        return $this->strip($this->_html_str);
    }

    /**
     * @param $html
     * @return string
     */
    public function strip($html): string
    {
        $search  = ['/<[\/]?(meta|span|div|section|i|html|body)[^><]*>/', '/(&gt;)/', '/(&lt;)/', '/(&amp;)/', '/(\u2014)/', '/(\u2009)/'];
        $replace = ['', '>', '<', '&', '--', ' '];
        return str_replace($search, $replace, $html);
    }

}