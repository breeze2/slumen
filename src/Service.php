<?php
namespace BL\Slumen;

use BL\Slumen\SwooleHttp\Handler;
use BL\Slumen\SwooleHttp\Logger;
use BL\Slumen\SwooleHttp\Worker;
use Exception;
use swoole_http_server as SwooleHttpServer;

class Service
{
    const PROVIDER_HANDLER = 'SlumenHandler';

    protected $server;
    protected $worker;
    protected $config;
    protected $handler;

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
        $this->handler = $this->makeHandler();

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

        $this->handler->handle('onServerStarted', [$server]);
    }

    public function onShutdown($server)
    {
        $file = $this->config['pid_file'];
        unlink($file);

        $this->handler->handle('onServerStopped', [$server]);
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->worker = new Worker($server, $worker_id);
        $this->worker->setHandler($this->handler);
        $this->worker->setLogger($this->makeLogger($worker_id));

        $this->handler->handle('onWorkerStarted', [$server, $worker_id]);
    }

    public function onWorkerStop($server, $worker_id)
    {
        unset($this->worker);

        $this->handler->handle('onWorkerStopped', [$server, $worker_id]);
    }

    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code, $signal)
    {
        $this->handler->handle('onWorkerError', [$server, $worker_id, $worker_pid, $exit_code, $signal]);
    }

    public function onRequest($request, $response)
    {
        if ($this->handler->handle('onRequested', [$request, $response]) === false) {
            return false;
        }

        if ($this->worker->handle($request, $response) !== false) {
            $this->handler->handle('onResponded', [$request, $response]);
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

    protected function makeHandler()
    {
        try {
            $handler = app(self::PROVIDER_HANDLER);
            if ($handler instanceof Handler) {
                return $handler;
            }
        } catch (Exception $error) {
            // do nothing
        }
        return new Handler();
    }

}
