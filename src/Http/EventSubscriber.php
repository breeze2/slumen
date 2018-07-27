<?php

namespace BL\Slumen\Http;

use Exception;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleHttpServer;

class Subscriber
{
    protected $events = [];

    public function __construct()
    {
        $this->subscribe();
    }

    public function onAppError(Exception $error) {}

    public function onServerRequested(SwooleHttpRequest $request, SwooleHttpResponse $response) {}

    public function onServerResponded(SwooleHttpRequest $request, SwooleHttpResponse $response) {}

    public function onServerStarted(SwooleHttpServer $server) {}

    public function onServerStopped(SwooleHttpServer $server) {}

    public function onWorkerError(SwooleHttpServer $server, $work_id, $worker_pid, $exit_code, $signal) {}

    public function onWorkerStarted(SwooleHttpServer $server, $work_id) {}

    public function onWorkerStopped(SwooleHttpServer $server, $work_id) {}

    public function subscribe()
    {
        $this->events['AppErrorEvent'] ='onAppError';

        $this->events['ServerRequestedEvent'] ='onServerRequested';

        $this->events['ServerRespondedEvent'] ='onServerResponded';

        $this->events['ServerStartedEvent'] ='onServerStarted';

        $this->events['ServerStoppedEvent'] ='onServerStopped';

        $this->events['WorkerErrorEvent'] ='onWorkerError';

        $this->events['WorkerStartedEvent'] ='onWorkerStarted';

        $this->events['WorkerStoppedEvent'] ='onWorkerStopped';
    }

    public function publish($event, array $params = [])
    {
        if(array_key_exists($event, $this->events) && method_exists($this, $this->events['event'])) {
            return call_user_func_array([$this, $this->events['event']], $params);
        }
    }
}
