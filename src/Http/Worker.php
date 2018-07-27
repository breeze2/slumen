<?php

namespace BL\Slumen\Http;

use BL\Slumen\Events\AppError;
use BL\Slumen\Provider\HttpLoggerServiceProvider;
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
        $this->logger = $this->makeLogger();
    }

    public function initialize(array $config)
    {
        $this->stats_uri        = $config['stats_uri'];
        $this->static_resources = $config['static_resources'];
        $this->public_dir       = $config['public_dir'];
        $this->gzip             = $config['gzip'];
        $this->gzip_min_length  = $config['gzip_min_length'];
    }

    protected function makeLogger()
    {
        try {
            $logger = app(HttpLoggerServiceProvider::PROVIDER_NAME);
            if ($logger instanceof Logger) {
                $logger->initialize(['worker_id' => $this->id]);
                $logger->open();
                return $logger;
            }
        } catch (Exception $e) {
            // do nothing
        }
        return null;
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

        $this->logHttpAccess($request->server, [
            'STATUS'          => 200,
            'BODY_BYTES_SENT' => strlen($stats),
        ]);
        return true;
    }

    protected function sendStaticResource(Request $request, Response $response)
    {
        $public_dir = $this->public_dir;
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
            $this->logHttpAccess($request->server, [
                'STATUS'          => $status,
                'BODY_BYTES_SENT' => $status === 200 ? filesize($file) : 0,
            ]);
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

        $this->logHttpAccess($request->server, [
            'STATUS'          => $status,
            'BODY_BYTES_SENT' => $body_bytes_sent,
        ]);
    }

    public function logHttpAccess(array $request_server, array $meta)
    {
        if ($this->logger) {
            $log                        = array_merge($request_server, $meta);
            $log['RESPONSE_TIME_FLOAT'] = microtime(true);
            $this->logger->addAccessInfo($log);
        }
    }

    public function logAppError(Exception $e)
    {
        $this->publisher && $this->publisher->publish('AppError', [$e]);

        $prefix = sprintf("[%s #%d *%d]\tERROR\t", date('Y-m-d H:i:s'), $this->server->master_pid, $this->id);
        fwrite(STDOUT, sprintf('%s%s(%d): %s', $prefix, $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
    }
}
