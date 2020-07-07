<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\restful;

use \ounun\restful;

abstract class driver
{
    /** @var array */
    protected array $_url_mods;

    /** @var restful */
    protected restful $_restful;

    public function __construct(array $url_mods = [], ?restful $restful = null)
    {
        $this->_url_mods = $url_mods;
        $this->_restful  = $restful ?? new restful($url_mods);

        $m = $this->_restful->method_get();
        if ('GET' == $m || 'POST' == $m || 'PUT' == $m || 'DELETE' == $m) {
            $this->$m();
        } else {
            $this->GET();
        }
    }

    /** GET 返回资源信息 */
    public function GET()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        $rs = succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
        out($rs);
    }

    /** POST 创建资源信息 */
    public function POST()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        $rs = succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
        out($rs);
    }

    /** PUT 更新资源信息 */
    public function PUT()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        $rs = succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
        out($rs);
    }

    /** DELETE 删除资源信息 */
    public function DELETE()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        $rs = succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
        out($rs);
    }
}
