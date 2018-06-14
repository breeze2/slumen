<?php

namespace BL\Slumen\SwooleHttp;

use ErrorException;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleHttpServer;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Worker
{
    protected $id     = null;
    protected $server = null;
    protected $logger = null;
    protected $config = null;
    protected $service_hook   = null;

    protected $request_count = 0;
    
    public $app       = null;

    public function __construct(SwooleHttpServer $server, $worker_id, array $config)
    {
        $this->id     = $worker_id;
        $this->server = $server;
        $this->config = $config;
        $this->loadApplication();
        $this->makeLogger();
    }

    public function setServiceHook($service_hook)
    {
        $this->service_hook = $service_hook;
    }

    public function makeLogger()
    {
        unset($this->logger);
        $http_log_path = $this->config['http_log_path'];
        if ($http_log_path) {
            $http_log_single = $this->config['http_log_single'];
            $file_name = $http_log_single ? 'http-server.log' : date('Y-m-d') . '_' . $this->id . '.log';
            
            $file = $http_log_path . '/' . $file_name;
            $this->logger = new Logger($file, $http_log_single);
        }
    }

    public function handle(SwooleHttpRequest $req, SwooleHttpResponse $res)
    {
        $this->incrementRequestCount();
        $request  = new Request($req);
        $response = new Response($res);

        if ($this->config['stats_uri'] && $request->server['REQUEST_URI'] === $this->config['stats_uri']) {
            if ($this->sendStatsJson($request, $response)) {
                return true;
            }
        }

        if ($this->config['static_resources'] && $request->server['REQUEST_METHOD'] === 'GET') {
            if ($this->sendStaticResource($request, $response)) {
                return true;
            }
        }

        try {
            $this->sendLumenResponse($request, $response);
            return true;
        } catch (ErrorException $e) {
            $this->logServerError($e);
        }
        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRequestCount()
    {
        return $this->request_count;
    }

    protected function incrementRequestCount($step = 1)
    {
        $this->request_count += $step;
    }

    protected function sendStatsJson(Request $request, Response $response)
    {
        $stats = json_encode($this->server->stats());
        $response->header('Content-Type', 'application/json');
        $response->end($stats);

        $this->logHttpAccess($request->server, array(
            'STATUS'          => 200,
            'BODY_BYTES_SENT' => strlen($stats),
        ));
        return true;
    }

    protected function sendStaticResource(Request $request, Response $response)
    {
        $public_dir = $this->config['public_dir'];
        $uri        = $request->server['REQUEST_URI'];
        $file       = realpath($public_dir . $uri);
        $status     = 200;
        if (is_file($file)) {
            if (!strncasecmp($file, $uri, strlen($public_dir))) {
                $status = 403;
                $response->status($status);
                $response->end();
            } else {
                $status = 200;
                $response->status($status);
                $response->header('Content-Type', mime_content_type($file));
                $response->sendFile($file);
            }
            $this->logHttpAccess($request->server, array(
                'STATUS'          => $status,
                'BODY_BYTES_SENT' => $status === 200 ? filesize($file) : 0,
            ));
            return true;
        }
        return false;
    }

    protected function sendLumenResponse(Request $request, Response $response)
    {
        $http_request  = $request->parseIlluminateRequest();
        $http_response = $this->app->dispatch($http_request);

        $response->setHeaders($http_response->headers->allPreserveCase());
        $response->setCookies($http_response->headers->getCookies());

        $content         = '';
        $body_bytes_sent = 0;
        $status          = 200;
        if ($http_response instanceof SymfonyBinaryFileResponse) {
            $file = $http_response->getFile()->getPathname();

            $status          = $http_response->getStatusCode();
            $body_bytes_sent = filesize($file);

            $response->sendfile($file);

        } else if ($http_response instanceof SymfonyResponse) {
            $content         = $http_response->getContent();
            $status          = $http_response->getStatusCode();
            $body_bytes_sent = strlen($content);

            // gzip handle
            $accept_gzip = $this->config['gzip'] > 0 && isset($request->header['HTTP_ACCEPT_ENCODING']) && stripos($request->header['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;

            if ($accept_gzip && $body_bytes_sent > $this->config['gzip_min_length'] && $response->checkGzipMime()) {
                $response->gzip($this->config['gzip']);
            }

            $response->end($content);
            if (count($this->app->getMiddleware()) > 0) {
                $this->app->callTerminableMiddleware($http_response);
            }
        } else {
            $content         = (string) $http_response;
            $status          = 200;
            $body_bytes_sent = strlen($content);

            $response->end($content);
        }

        $this->logHttpAccess($request->server, array(
            'STATUS'          => $status,
            'BODY_BYTES_SENT' => $body_bytes_sent,
        ));
    }

    protected function loadApplication()
    {
        unset($this->app);
        $bootstrap = $this->config['bootstrap'];
        $this->app = require $bootstrap;
    }

    public function logHttpAccess(array $request_server, array $meta)
    {
        if ($this->logger) {
            $log                        = array_merge($request_server, $meta);
            $log['RESPONSE_TIME_FLOAT'] = microtime(true);
            $this->logger->accessJSON($log);
        }
    }

    public function logServerError(ErrorException $e)
    {
        if($this->service_hook) {
            if($this->service_hook->serverErrorHandle() === false) {
                return false;
            }
        }

        $prefix = sprintf("[%s #%d *%d]\tERROR\t", date('Y-m-d H:i:s'), $this->server->master_pid, $this->id);
        fwrite(STDOUT, sprintf('%s%s(%d): %s', $prefix, $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
    }
}
