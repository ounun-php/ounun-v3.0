<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\swoole;


class http
{
    /** @var \Swoole\Http\Server  */
    protected $_http;

    public function __construct($ip = '127.0.0.1', $port = 9501)
    {
        //高性能HTTP服务器
        $http = new \Swoole\Http\Server($ip, $port);

        $http->on("start", function ($server) {
            echo "Swoole http server is started at http://127.0.0.1:9501\n";
        });

        $http->on("request", function ($request, $response) {
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });

        $this->_http = $http;
        $this->_http->start();
    }

}
