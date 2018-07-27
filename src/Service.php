<?php
namespace BL\Slumen;

use BL\Slumen\Events\ServerRequested;
use BL\Slumen\Events\ServerResponded;
use BL\Slumen\Events\ServerStarted;
use BL\Slumen\Events\ServerStopped;
use BL\Slumen\Events\WorkerError;
use BL\Slumen\Events\WorkerStarted;
use BL\Slumen\Events\WorkerStopped;
use BL\Slumen\Http\Logger;
use BL\Slumen\Http\Worker;
use swoole_http_server as SwooleHttpServer;

class Service
{
    const PROVIDER_MYSQL_POOL = 'SlumenMySqlPool';

    protected $server;
    protected $worker;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->server = new SwooleHttpServer($config['host'], $config['port'], $config['running_mode'], $config['socket_type']);
        if (array_key_exists('swoole_server', $config) && is_array($config['swoole_server'])) {
            $this->server->set($config['swoole_server']);
        }
    }

    public function start()
    {
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('shutdown', [$this, 'onShutdown']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('workerStop', [$this, 'onWorkerStop']);
        $this->server->on('workerError', [$this, 'onWorkerError']);
        $this->server->on('request', [$this, 'onRequest']);

        $this->server->start();
    }

    public function onStart($server)
    {
        $file = $this->config['pid_file'];
        file_put_contents($file, $server->master_pid);

        event(new ServerStarted($server));
    }

    public function onShutdown($server)
    {
        $file = $this->config['pid_file'];
        unlink($file);

        event(new ServerStopped($server));
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->worker = new Worker($server, $worker_id);
        $this->worker->initialize($this->config);
        $this->worker->setLogger($this->makeLogger($worker_id));

        event(new WorkerStarted($server, $worker_id));
    }

    public function onWorkerStop($server, $worker_id)
    {
        unset($this->worker);

        event(new WorkerStopped($server, $worker_id));
    }

    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code, $signal)
    {
        event(new WorkerError($server, $worker_id, $worker_pid, $exit_code, $signal));
    }

    public function onRequest($request, $response)
    {
        event(new ServerRequested($request, $response));

        if ($this->worker->handle($request, $response) !== false) {
            event(new ServerResponded($request, $response));
        }
    }

    protected function makeLogger($worker_id)
    {
        $http_log_path = $this->config['http_log_path'];
        if ($http_log_path) {
            $http_log_single = $this->config['http_log_single'];
            $file_name       = $http_log_single ? 'http-server.log' : date('Y-m-d') . '_' . $worker_id . '.log';

            $file = $http_log_path . '/' . $file_name;
            return new Logger($file, $http_log_single);
        }
        return null;
    }

}
