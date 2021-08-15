<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\plugin;

class rss
{
    /** @var string RSS头 */
    public string $rss_header;

    /** @var string 频道 */
    public string $rss_channel;

    /** @var string 图片 */
    public string $rss_image;

    /** @var string 项目 */
    public string $rss_item;

    /**
     * rss constructor.
     * @param string $xml
     * @param string $rss
     * @param string $encoding
     */
    public function __construct(string $xml = '1.0',string $rss = '2.0',string $encoding = 'utf-8')
    {
        $this->header($xml, $rss, $encoding);
    }

    /**
     * @param string $xml
     * @param string $rss
     * @param string $encoding
     */
    function header($xml = '1.0', $rss = '2.0', $encoding = 'utf-8')
    {
        $this->rss_header = "<?xml version=\"$xml\" encoding=\"$encoding\"?>\n";
        $this->rss_header .= "<rss version=\"$rss\">\n";
    }

    /**
     * @param $channel
     */
    function channel($channel)
    {
        $this->rss_channel = "<channel>\n";
        foreach ($channel as $key => $value) {
            $this->rss_channel .= " <$key><![CDATA[" . $value . "]]></$key>\n";
        }
    }

    /**
     * @param $image
     */
    function image($image)
    {
        $this->rss_image = "  <image>\n";
        foreach ($image as $key => $value) {
            $this->rss_image .= " <$key><![CDATA[" . $value . "]]></$key>\n";
        }
        $this->rss_image .= "  </image>\n";
    }

    /**
     * @param $item
     */
    function item($item)
    {
        $this->rss_item .= "<item>\n";
        foreach ($item as $key => $value) {
            $this->rss_item .= " <$key><![CDATA[" . $value . "]]></$key>\n";
        }
        $this->rss_item .= "</item>\n";
    }

    /**
     * @return string
     */
    function footer()
    {
        $data = $this->rss_header;
        $data .= $this->rss_channel;
        $data .= $this->rss_image;
        $data .= $this->rss_item;
        $data .= "</channel></rss>";
        return $data;
    }
}
