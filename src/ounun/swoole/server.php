<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it is under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\swoole;


class server
{
    /** @var \Swoole\Server */
    protected $_server;

    public function __construct($ip = '127.0.0.1', $port = 9503, $type = 'tcp')
    {
        if ($type == 'udp') {
            $this->udp($ip, $port);
        } else {
            $this->tcp($ip, $port);
        }
        $this->start();
    }

    public function start()
    {
        $this->_server->start();
    }

    public function udp($ip, $port)
    {
        $server = new \Swoole\Server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

        $server->on('Packet', function ($server, $data, $clientInfo) {
            $server->sendTo($clientInfo['address'], $clientInfo['port'], "Server " . $data);
            var_dump($clientInfo);
        });
        $this->_server = $server;
    }

    public function tcp($ip, $port)
    {
        $server = new \Swoole\Server($ip, $port);

        $server->on('connect', function ($server, $fd) {
            echo "connection open: {$fd}\n";
        });

        $server->on('receive', function ($server, $fd, $reactor_id, $data) {
            $server->send($fd, "Swoole: {$data}");
            $server->close($fd);
        });

        $server->on('close', function ($server, $fd) {
            echo "connection close: {$fd}\n";
        });

        $this->_server = $server;
    }

    public function task($ip, $port, $task_worker_num = 4)
    {
        $server = new \Swoole\Server($ip, $port);
        $server->set(array('task_worker_num' => $task_worker_num));

        $server->on('receive', function ($server, $fd, $reactor_id, $data) {
            $task_id = $server->task("Async");
            echo "Dispatch AsyncTask: [id=$task_id]\n";
        });

        $server->on('task', function ($server, $task_id, $reactor_id, $data) {
            echo "New AsyncTask[id=$task_id]\n";
            $server->finish("$data -> OK");
        });

        $server->on('finish', function ($server, $task_id, $data) {
            echo "AsyncTask[$task_id] finished: {$data}\n";
        });

        $this->_server = $server;
    }
}
