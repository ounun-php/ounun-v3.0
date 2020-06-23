<?php


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
