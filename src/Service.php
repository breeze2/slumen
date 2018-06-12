<?php
namespace BL\Slumen;

use BL\Slumen\SwooleHttp\Worker;
use swoole_http_server as SwooleHttpServer;

class Service
{
    protected $server;
    protected $worker;
    protected $config;
    protected $setting;
    protected $hook;

    public function __construct(array $config, array $setting)
    {
        $this->config  = $config;
        $this->setting = $setting;
        $this->server  = new SwooleHttpServer($config['host'], $config['port']);
        if (isset($setting) && !empty($setting)) {
            $this->server->set($setting);
        }
    }

    public function start()
    {
        $this->makeHook($this->config['service_hook']);

        $this->server->on('start', array($this, 'onStart'));
        $this->server->on('shutdown', array($this, 'onShutdown'));
        $this->server->on('workerStart', array($this, 'onWorkerStart'));
        $this->server->on('workerStop', array($this, 'onWorkerStop'));
        $this->server->on('request', array($this, 'onRequest'));

        $this->server->start();
    }

    public function onStart($server)
    {
        $file = $this->config['pid_file'];
        file_put_contents($file, $server->master_pid);

        $this->hook && $this->hook->startedHandle($server);
    }

    public function onShutdown($server)
    {
        $file = $this->config['pid_file'];
        unlink($file);

        $this->hook && $this->hook->stoppedHandle($server);
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->worker = new Worker($server, $worker_id, $this->config);
        if ($this->hook) {
            $this->worker->setServiceHook($this->hook);
            $this->hook->workStartedHandle($server, $worker_id);
        }
    }

    public function onWorkerStop($server, $worker_id)
    {
        unset($this->worker);

        $this->hook && $this->hook->workStoppedHandle($server, $worker_id);
    }

    public function onRequest($request, $response)
    {
        if($this->hook) {
            if($this->hook->requestedHandle() === false) {
                return false;
            }
        }

        $this->worker->handle($request, $response);
    }

    protected function makeHook($class)
    {
        if($class && class_exists($class)) {
            $hook = new $class;
            if($hook instanceof ServiceHook) {
                $this->hook =$hook;
            }
        }
    }

}
