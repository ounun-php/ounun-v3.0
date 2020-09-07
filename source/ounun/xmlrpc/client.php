<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

namespace ounun\xmlrpc;


class client
{
    protected string $url;
    protected string $method;

    protected array $output_options;

    protected int $error_code;
    protected string $error_msg;

    /**
     * xmlrpc_client constructor.
     * @param $url
     * @param string $method
     */
    public function __construct($url, $method = 'POST')
    {
        $this->url    = $url;
        $this->method = $method;
    }

    function request($method, $params, $output_options = [])
    {
        $request = xmlrpc_encode_request($method, $params, $output_options);
        $options = ['http' => ['method' => $this->method, 'header' => "Content-Type: text/xml", 'content' => $request]];
        $context = stream_context_create($options);
        $data    = file_get_contents($this->url, false, $context);
        if (!$data) {
            $this->error_msg = 'can not get webservice response';
            return false;
        }
        $response = xmlrpc_decode($data);
        if (is_array($response) && xmlrpc_is_fault($response)) {
            $this->error_msg  = $response['faultString'];
            $this->error_code = $response['faultCode'];
            return false;
        }
        return $response;
    }
}
