<?php

namespace BL\Slumen\Events;

use swoole_http_server as SwooleHttpServer;

class ServerStarted
{
    public $server;
    public function __construct(SwooleHttpServer $server)
    {
        $this->$server = $server;
    }
}
