<?php

namespace BL\Slumen\Events;

use swoole_http_server as SwooleHttpServer;

class WorkerError
{
    public $server;
    public $worker_id;
    public $worker_pid;
    public $exit_code;
    public $signal;
    public function __construct(SwooleHttpServer $server, $work_id, $worker_pid, $exit_code, $signal)
    {
        $this->server     = $server;
        $this->worker_id  = $worker_id;
        $this->worker_pid = $worker_pid;
        $this->exit_code  = $exit_code;
        $this->signal     = $signal;
    }
}
