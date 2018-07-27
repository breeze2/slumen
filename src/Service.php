<?php
namespace BL\Slumen;

use BL\Slumen\Http\EventSubscriber;
use BL\Slumen\Provider\HttpEventSubscriberServiceProvider;
use BL\Slumen\Http\Worker;
use swoole_http_server as SwooleHttpServer;

class Service
{
    const PROVIDER_MYSQL_POOL = 'SlumenMySqlPool';

    protected $server;
    protected $worker;
    protected $config;
    protected $publisher;

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

        $this->publisher && $this->publisher->publish('ServerStarted', [$server]);
    }

    public function onShutdown($server)
    {
        $file = $this->config['pid_file'];
        unlink($file);

        $this->publisher && $this->publisher->publish('ServerStopped', [$server]);
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->worker = new Worker($server, $worker_id);
        $this->worker->initialize($this->config);
        $this->worker->setPublisher($this->publisher);
        $this->publisher && $this->publisher->publish('WorkerStarted', [$server, $worker_id]);
    }

    public function onWorkerStop($server, $worker_id)
    {
        unset($this->worker);

        $this->publisher && $this->publisher->publish('WorkerStopped', [$server, $worker_id]);
    }

    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code, $signal)
    {
        $this->publisher && $this->publisher->publish('WorkerError', [$server, $worker_id, $worker_pid, $exit_code, $signal]);
    }

    public function onRequest($request, $response)
    {
        $this->publisher && $this->publisher->publish('ServerRequested', [$request, $response]);

        if ($this->worker->handle($request, $response) !== false) {
            $this->publisher && $this->publisher->publish('ServerResponded', [$request, $response]);
        }
    }

    protected function makePublisher()
    {
        try {
            $publisher = app(HttpEventSubscriberServiceProvider::PROVIDER_NAME);
            if ($publisher instanceof EventSubscriber) {
                return $publisher;
            }
        } catch (Exception $e) {
            // do nothing
        }
        return null;
    }

}
