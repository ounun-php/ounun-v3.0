<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\restful;

use \ounun\restful;

abstract class driver
{
    /** @var array */
    protected array $_url_mods;

    /** @var restful */
    protected restful $_restful;

    public function __construct(array $url_mods, restful $restful)
    {
        $this->_url_mods = $url_mods;
        $this->_restful  = $restful;

        $m = $this->_restful->method_get();
        if ('GET' == $m || 'POST' == $m || 'PUT' == $m || 'DELETE' == $m) {
            $rs = $this->$m();
        } else {
            $rs = $this->GET();
        }
        out($rs);
    }

    /** GET 返回资源信息 */
    public function GET(): array
    {
        header('HTTP/1.1 405 Method Not Allowed');
        return succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
    }

    /** POST 创建资源信息 */
    public function POST(): array
    {
        header('HTTP/1.1 405 Method Not Allowed');
        return succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
    }

    /** PUT 更新资源信息 */
    public function PUT(): array
    {
        header('HTTP/1.1 405 Method Not Allowed');
        return succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
    }

    /** DELETE 删除资源信息 */
    public function DELETE(): array
    {
        header('HTTP/1.1 405 Method Not Allowed');
        return succeed([static::class.'::'.explode(':',__METHOD__)[2], $this->_restful->input_get(), $this->_restful->gets_get(), $this->_restful->post_get()]);
    }
}
