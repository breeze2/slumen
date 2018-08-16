<?php

namespace BL\Slumen\Http;

use Exception;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleHttpServer;

class EventSubscriber
{
    protected $events = [];

    public function __construct()
    {
        $this->subscribe();
    }

    // public function onAppError(Exception $error)
    // {}

    public function onServerRequested(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {}

    public function onServerResponded(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {}

    public function onServerStarted(SwooleHttpServer $server)
    {}

    public function onServerStopped(SwooleHttpServer $server)
    {}

    public function onWorkerError(SwooleHttpServer $server, $work_id, $worker_pid, $exit_code, $signal)
    {}

    public function onWorkerStarted(SwooleHttpServer $server, $work_id)
    {}

    public function onWorkerStopped(SwooleHttpServer $server, $work_id)
    {}

    public function subscribe()
    {
        // $this->events['AppError'] = 'onAppError';

        $this->events['ServerRequested'] = 'onServerRequested';

        $this->events['ServerResponded'] = 'onServerResponded';

        $this->events['ServerStarted'] = 'onServerStarted';

        $this->events['ServerStopped'] = 'onServerStopped';

        $this->events['WorkerError'] = 'onWorkerError';

        $this->events['WorkerStarted'] = 'onWorkerStarted';

        $this->events['WorkerStopped'] = 'onWorkerStopped';
    }

    public function getEvents()
    {
        return $this->events;
    }
}
