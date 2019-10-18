<?php
namespace ounun\mvc;


abstract class restful
{
    /** @var array */
    protected $_mod;
    /** @var \v */
    protected $_v;
    /** @var \ounun\restful */
    protected $_restful;

    public function __construct(array $mod = [],?\ounun\restful $restful = null)
    {
        $this->_mod = $mod;
        $this->_restful = $restful??new \ounun\restful($mod);

        $m = $this->_restful->method_get();
        if('GET' == $m || 'POST' ==  $m || 'PUT' == $m || 'DELETE' == $m){
            $this->$m();
        }else {
            $this->GET();
        }
    }

    /** GET 返回资源信息 */
    public function GET()
    {
        $rs = succeed([__METHOD__,$this->_restful->input_get(),$this->_restful->gets_get(),$this->_restful->post_get()]);
        out($rs);
    }

    /** POST 创建资源信息 */
    public function POST()
    {
        $rs = succeed([__METHOD__,$this->_restful->input_get(),$this->_restful->gets_get(),$this->_restful->post_get()]);
        out($rs);
    }

    /** PUT 更新资源信息 */
    public function PUT()
    {
        $rs = succeed([__METHOD__,$this->_restful->input_get(),$this->_restful->gets_get(),$this->_restful->post_get()]);
        out($rs);
    }

    /** DELETE 删除资源信息 */
    public function DELETE()
    {
        $rs = succeed([__METHOD__,$this->_restful->input_get(),$this->_restful->gets_get(),$this->_restful->post_get()]);
        out($rs);
    }
}
