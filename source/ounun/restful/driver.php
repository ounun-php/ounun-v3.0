<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
namespace ounun\restful;


abstract class driver
{
    /** @var array */
    protected $_url_mods;
    /** @var \ounun\restful */
    protected $_restful;

    public function __construct(array $url_mods = [], ?\ounun\restful $restful = null)
    {
        $this->_url_mods = $url_mods;
        $this->_restful = $restful??new \ounun\restful($url_mods);

        $m = $this->_restful->method_get();
        if('GET' == $m || 'POST' ==  $m || 'PUT' == $m || 'DELETE' == $m){
            $this->$m();
        }else {
            $this->GET();
        }
    }

    /** GET 返回资源信息 */
    abstract public function GET();
    /** POST 创建资源信息 */
    abstract public function POST();
    /** PUT 更新资源信息 */
    abstract public function PUT();
    /** DELETE 删除资源信息 */
    abstract public function DELETE();
}
