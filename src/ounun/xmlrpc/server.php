<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\xmlrpc;


class server
{
    protected $_xmlrpc_server;

    public function __construct()
    {
        $this->_xmlrpc_server = xmlrpc_server_create();
    }

    /**
     * @param $method
     * @param $function
     */
    public function register($method, $function)
    {
        xmlrpc_server_register_method($this->_xmlrpc_server, $method, $function);
    }

    /**
     * @param null $output_options
     */
    public function call($output_options = null)
    {
        if (!isset($HTTP_RAW_POST_DATA)) {
            $HTTP_RAW_POST_DATA = file_get_contents('php://input');
        }
        $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
        $response           = xmlrpc_server_call_method($this->_xmlrpc_server, $HTTP_RAW_POST_DATA, $output_options);
        file_put_contents('data.txt', $HTTP_RAW_POST_DATA);
        header('Content-Type: text/xml');
        echo $response;
    }

    public function __destruct()
    {
        xmlrpc_server_destroy($this->_xmlrpc_server);
    }
}
