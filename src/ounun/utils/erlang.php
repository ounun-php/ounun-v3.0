<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\utils;

use ounun\client\httpc;

class erlang
{
    protected string $_key;
    protected int $_hub_id = 0; // hub_id
    protected string $_host;
    protected int $_port;

    /**
     * 例子:
     *   $dir     = "<<".private_string_binary($dir).">>";
     *   $cmd     = "<<".private_string_binary($cmd).">>";
     *   $data   = "[{$dir},{$cmd}]";
     * @param $string
     * @return string
     */
    public static function string2binary($string): string
    {
        $i      = 0;
        $number = [];
        while (isset($string[$i])) {
            $number[] = ord($string[$i]);
            $i++;
        }
        return "<<" . implode(',', $number) . ">>";
    }

    /**
     * erlang constructor.
     * @param string $key
     * @param int $sid_hub_id
     * @param string $host
     * @param int $port
     */
    public function __construct(string $key, int $sid_hub_id, string $host, int $port)
    {
        $this->config_set($sid_hub_id, $host, $port, $key);
    }

    /**
     * @param int $sid_hub_id
     * @param string $host
     * @param int $port
     * @param string $key
     */
    public function config_set(int $sid_hub_id = 0, string $host = '', int $port = 0, string $key = '')
    {
        if ($key) {
            $this->_key = $key;
        }
        if ($sid_hub_id) {
            $this->_hub_id = $sid_hub_id;
        }
        if ($host) {
            $this->_host = $host;
        }
        if ($port) {
            $this->_port = $port;
        }
    }

    /**
     * 统一调用调用
     * @param string $node_type
     * @param string $mod
     * @param string $fun
     * @param string $arg_data
     * @return array string
     */
    protected function _erlang_call(string $node_type, string $mod, string $fun, string $arg_data): array
    {
        $time = time();
        $fun  = substr($fun, 0, 3) == 'gm_' ? substr($fun, 3) : $fun;
        $mod  = substr($mod, -4, 4) == '_api' ? substr($mod, 0, -4) : $mod;
        $md5  = md5("{$this->_hub_id}_{$node_type}_{$mod}_{$fun}_{$arg_data}_{$time}_{$this->_key}");
        // echo "{$this->_hub_id}_{$node_type}_{$mod}_{$fun}_{$arg_data}_{$time}_{$this->_key}<br />\n";
        return $this->_port($mod, $fun, "{ {$this->_hub_id},{$node_type},\"{$md5}\",{$time},{$arg_data}}");
    }

    /**
     * 统一调用调用  返回:Ret
     * @param string $node_type
     * @param string $mod
     * @param string $fun
     * @param string $arg_data
     * @return array
     */
    protected function _erlang_call_result(string $node_type, string $mod, string $fun, string $arg_data): array
    {
        $rs = $this->_erlang_call($node_type, $mod, $fun, $arg_data);
        if ($rs[0]) {
            // echo $rs[1]."<br />\n";
            $data = json_decode_array($rs[1]);
            if ($data['ret']) {
                return succeed($data['data'], $data['ret']);
            }
            return error($data['msg']);
        }
        return error($rs[1]);
    }


    /**
     * @param string $mod
     * @param string $fun 方法
     * @param string $data 数据
     * @return array
     */
    protected function _port(string $mod, string $fun, string $data = "[]"): array
    {
        $url   = "http://{$this->_host}:{$this->_port}/";
        $model = "{{$mod},{$fun},{$data}}";
        return httpc::stream_post($url, $model, [], 600);
    }
}

