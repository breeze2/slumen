<?php

namespace BL\Slumen\Http;

use Exception;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleHttpServer;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Worker
{
    public $app;

    protected $id;
    protected $server;
    protected $logger;
    protected $publisher;

    private $stats_uri;
    private $static_resources;
    private $public_dir;
    private $gzip;
    private $gzip_min_length;

    public function __construct(SwooleHttpServer $server, $worker_id)
    {
        $this->id     = $worker_id;
        $this->server = $server;
        $this->app    = app();
    }

    public function initialize()
    {
        $this->gzip             = config('slumen.gzip');
        $this->gzip_min_length  = config('slumen.gzip_min_length');
        $this->stats_uri        = config('slumen.stats_uri');
        $this->static_resources = config('slumen.static_resources');
        $this->public_dir       = base_path('public');
    }

    public function setPublisher(EventSubscriber $subscriber)
    {
        $this->publisher = $subscriber;
    }

    public function handle(SwooleHttpRequest $req, SwooleHttpResponse $res)
    {
        $request  = new Request($req);
        $response = new Response($res);

        if ($this->stats_uri && $request->server['REQUEST_URI'] === $this->stats_uri) {
            if ($this->sendStatsJson($request, $response)) {
                return true;
            }
        }

        if ($this->static_resources && $request->server['REQUEST_METHOD'] === 'GET') {
            if ($this->sendStaticResource($request, $response)) {
                return true;
            }
        }

        try {
            $this->sendLumenResponse($request, $response);
            return true;
        } catch (Exception $e) {
            $this->logAppError($e);
        }
        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    protected function sendStatsJson(Request $request, Response $response)
    {
        $data              = $this->server->stats();
        $data['worker_id'] = $this->id;
        $stats             = json_encode($data);
        $response->header('Content-Type', 'application/json');
        $response->end($stats);

        return true;
    }

    protected function sendStaticResource(Request $request, Response $response)
    {
        $public_dir = $this->public_dir;
        $uri        = $request->server['REQUEST_URI'];
        $file       = realpath($public_dir . $uri);
        $status     = 200;
        if ($file && is_file($file)) {
            if (!strncasecmp($file, $uri, strlen($public_dir))) {
                $status = 403;
                $response->status($status);
                $response->end('');
            } else {
                $status = 200;
                $response->status($status);
                $response->header('Content-Type', mime_content_type($file));
                $response->sendFile($file);
            }

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
            $accept_gzip = $this->gzip > 0 && isset($request->header['HTTP_ACCEPT_ENCODING']) && stripos($request->header['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;

            if ($accept_gzip && $body_bytes_sent > $this->gzip_min_length && $response->checkGzipMime()) {
                $response->gzip($this->gzip);
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
    }

    public function logAppError(Exception $e)
    {
        $this->publisher && $this->publisher->publish('AppError', [$e]);

        $prefix = sprintf("[%s #%d *%d]\tERROR\t", date('Y-m-d H:i:s'), $this->server->master_pid, $this->id);
        fwrite(STDOUT, sprintf('%s%s(%d): %s', $prefix, $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
    }
}
