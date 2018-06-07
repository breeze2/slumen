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
        $this->server->on('start', array($this, 'onStart'));
        $this->server->on('shutdown', array($this, 'onShutdown'));
        $this->server->on('workerStart', array($this, 'onWorkerStart'));
        $this->server->on('workerStop', array($this, 'onWorkerStop'));
        $this->server->on('request', array($this, 'onRequest'));

        $this->server->start();
    }

    public function onStart($serv)
    {
        $file = $this->config['pid_file'];
        file_put_contents($file, $serv->master_pid);
    }

    public function onShutdown()
    {
        $file = $this->config['pid_file'];
        unlink($file);
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->worker = new Worker($server, $worker_id, $this->config);
    }

    public function onWorkerStop($server, $worker_id)
    {
        unset($this->worker);
    }

    public function onRequest($request, $response)
    {
        $this->worker->handle($request, $response);
    }

}
