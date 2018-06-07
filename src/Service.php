<?php
namespace BL\Slumen;

use ErrorException;
use Illuminate\Http\Request as IlluminateRequest;
use swoole_http_server as SwooleHttpServer;
use BL\Slumen\SwooleHttp\Worker;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
        // if ($this->config['stats_uri'] && $request->server['request_uri'] === $this->config['stats_uri']) {
        //     if ($this->statsJson($request, $response)) {
        //         return;
        //     }
        // }

        // if ($this->config['static_resources']) {
        //     if ($this->staticResource($request, $response)) {
        //         return;
        //     }
        // }
        // try {
        //     $this->lumenResponse($request, $response);

        // } catch (ErrorException $e) {
        //     $this->logServerError($e);
        // }
    }

    // protected function parseRequest($request)
    // {
    //     $get     = isset($request->get) ? $request->get : array();
    //     $post    = isset($request->post) ? $request->post : array();
    //     $cookie  = isset($request->cookie) ? $request->cookie : array();
    //     $server  = isset($request->server) ? $request->server : array();
    //     $header  = isset($request->header) ? $request->header : array();
    //     $files   = isset($request->files) ? $request->files : array();
    //     $fastcgi = array();

    //     $new_server = array();
    //     foreach ($server as $key => $value) {
    //         $new_server[strtoupper($key)] = $value;
    //     }
    //     foreach ($header as $key => $value) {
    //         $new_server['HTTP_' . str_to_upper($key)] = $value;
    //     }

    //     $content = $request->rawContent() ?: null;

    //     $http_request = new IlluminateRequest($get, $post, $fastcgi, $cookie, $files, $new_server, $content);

    //     return $http_request;
    // }

    // public function makeResponse($response, $http_response, $accept_gzip)
    // {
    //     // status
    //     $response->status($http_response->getStatusCode());
    //     // headers
    //     foreach ($http_response->headers->allPreserveCase() as $name => $values) {
    //         foreach ($values as $value) {
    //             $response->header($name, $value);
    //         }
    //     }
    //     // cookies
    //     foreach ($http_response->headers->getCookies() as $cookie) {
    //         $response->rawcookie(
    //             $cookie->getName(),
    //             $cookie->getValue(),
    //             $cookie->getExpiresTime(),
    //             $cookie->getPath(),
    //             $cookie->getDomain(),
    //             $cookie->isSecure(),
    //             $cookie->isHttpOnly()
    //         );
    //     }
    //     // content
    //     $content = $http_response->getContent();

    //     // check gzip
    //     if ($accept_gzip && isset($response->header['Content-Type'])) {
    //         $mime = $response->header['Content-Type'];
    //         if (strlen($content) > $this->config['gzip_min_length'] && $this->checkGzipMime($mime)) {
    //             $response->gzip($this->config['gzip']);
    //         }
    //     }

    //     // send content
    //     $response->end($content);
    // }

    // protected function lumenResponse($request, $response)
    // {

    //     $http_request  = $this->parseRequest($request);
    //     $http_response = $this->app->dispatch($http_request);
    //     return $this->directLumenResponse($request, $response, $http_response);
    // }

    // public function directLumenResponse($request, $response, $http_response)
    // {
    //     if ($http_response instanceof SymfonyBinaryFileResponse) {
    //         $response->sendfile($http_response->getFile());

    //     } else if ($http_response instanceof SymfonyResponse) {
    //         // gzip handle
    //         $accept_gzip = $this->config['gzip'] > 0 && isset($request->header['accept-encoding']) && stripos($request->header['accept-encoding'], 'gzip') !== false;

    //         $this->makeResponse($response, $http_response, $accept_gzip);
    //         if (count($this->app->getMiddleware()) > 0) {
    //             $this->app->callTerminableMiddleware($http_response);
    //         }
    //     } else {
    //         $response->end((string) $http_response);
    //     }
    //     $this->logHttpRequest($request, $http_response->getStatusCode());
    // }

    // protected function staticResource($request, $response)
    // {
    //     $public_dir = $this->config['public_dir'];
    //     $uri        = $request->server['request_uri'];
    //     $file       = realpath($public_dir . $uri);
    //     $status     = 200;
    //     if (is_file($file)) {
    //         if (!strncasecmp($file, $uri, strlen($public_dir))) {
    //             $status = 403;
    //             $response->status($status);
    //             $response->end();
    //         } else {
    //             $status = 200;
    //             $response->status($status);
    //             $response->header('Content-Type', mime_content_type($file));
    //             $response->sendfile($file);
    //         }
    //         return true;
    //         $this->logHttpRequest($request, $status);
    //     }
    //     return false;
    // }

    // protected function statsJson($request, $response)
    // {
    //     $stats                  = $this->server->stats();
    //     $stats['coroutine_num'] = $this->coroutineNum;
    //     $response->header('Content-Type', 'application/json');
    //     $response->end(json_encode($stats));
    //     $this->logHttpRequest($request, 200);
    //     return true;
    // }

    // protected function checkGzipMime($mime)
    // {
    //     static $mimes = [
    //         'text/plain'             => true,
    //         'text/html'              => true,
    //         'text/css'               => true,
    //         'application/javascript' => true,
    //         'application/json'       => true,
    //         'application/xml'        => true,
    //     ];
    //     if ($pos = strpos($mime, ';')) {
    //         $mime = substr($mime, 0, $pos);
    //     }
    //     return isset($mimes[strtolower($mime)]);
    // }

    // public function logHttpRequest($request, $status)
    // {
    //     if ($this->workLogFileStream) {
    //         $log = array_merge($request->header, $request->server, array('status' => $status));
    //         @fwrite($this->workLogFileStream, json_encode($log) . "\n");
    //     }
    // }

    // public function logServerError(ErrorException $e)
    // {
    //     $prefix = sprintf("[%s #%d *%d]\tERROR\t", date('Y-m-d H:i:s'), $this->server->master_pid, $this->server->worker_id);
    //     fwrite(STDOUT, sprintf('%s%s(%d): %s', $prefix, $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
    // }

    // protected function reloadApplication()
    // {
    //     $root_dir  = $this->config['root_dir'];
    //     $bootstrap = $this->config['bootstrap'];
    //     require $root_dir . '/vendor/autoload.php';
    //     $this->app = require $bootstrap;
    // }

}
