<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\swoole;


class websocket
{
    /** @var \Swoole\Websocket\Server  */
    protected $_server;

    public function __construct($ip = '127.0.0.1', $port = 9502)
    {
        $server = new \Swoole\Websocket\Server($ip, $port);

        $server->on('open', function($server, $req) {
            echo "connection open: {$req->fd}\n";
        });

        $server->on('message', function($server, $frame) {
            echo "received message: {$frame->data}\n";
            $server->push($frame->fd, json_encode(["hello", "world"]));
        });

        $server->on('close', function($server, $fd) {
            echo "connection close: {$fd}\n";
        });

        $this->_server=$server;
        $this->_server->start();
    }
}
