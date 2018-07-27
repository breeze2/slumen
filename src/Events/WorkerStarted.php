<?php

namespace BL\Slumen\Events;

use swoole_http_server as SwooleHttpServer;

class WorkerStarted
{
    public $server;
    public $work_id;
    public function __construct(SwooleHttpServer $server, $work_id)
    {
        $this->$server  = $server;
        $this->$work_id = $work_id;
    }
}
