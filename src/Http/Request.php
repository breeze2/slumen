<?php

namespace BL\Slumen\Http;

use Illuminate\Http\Request as IlluminateHttpRequest;
use swoole_http_request as SwooleHttpRequest;

class Request
{
    protected $request = null;

    public $get   = null;
    public $post  = null;
    public $files = null;

    public $cookie = null;
    public $server = null;
    public $header = null;

    public $fastcgi = null;
    public $content = null;

    public function __construct(SwooleHttpRequest $request)
    {
        $this->request = $request;

        $this->get    = isset($request->get) ? $request->get : [];
        $this->post   = isset($request->post) ? $request->post : [];
        $this->files  = isset($request->files) ? $request->files : [];
        $this->cookie = isset($request->cookie) ? $request->cookie : [];

        $this->server  = [];
        $this->header  = [];
        $this->fastcgi = [];
        $this->content = $request->rawContent() ?: null;

        foreach ($request->server as $key => $value) {
            $this->server[strtoupper($key)] = $value;
        }
        foreach ($request->header as $key => $value) {
            $this->header['HTTP_' . str_to_upper($key)] = $value;
        }

        $this->tidyServerData();

    }

    protected function tidyServerData()
    {
        // CONTENT_SIZE
        $this->server['CONTENT_SIZE'] = strlen($this->content);

        // REMOTE_USER
        if (isset($this->server['REMOTE_USER'])) {
            // nothing
        } else if (isset($this->header['HTTP_REMOTE_USER'])) {
            $this->server['REMOTE_USER'] = $this->header['HTTP_REMOTE_USER'];
        } else {
            $this->server['REMOTE_USER'] = '';
        }

        // HTTP_USER_AGENT
        if (isset($this->header['HTTP_USER_AGENT'])) {
            $this->server['HTTP_USER_AGENT'] = $this->header['HTTP_USER_AGENT'];
        } else {
            $this->server['HTTP_USER_AGENT'] = '';
        }

        // HTTP_REFERER
        if (isset($this->header['HTTP_REFERER'])) {
            $this->server['HTTP_REFERER'] = $this->header['HTTP_REFERER'];
        } else {
            $this->server['HTTP_REFERER'] = '';
        }

        // HTTP_X_FORWARDED_FOR
        if (isset($this->header['HTTP_X_FORWARDED_FOR'])) {
            $this->server['HTTP_X_FORWARDED_FOR'] = $this->header['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->server['HTTP_X_FORWARDED_FOR'] = '';
        }
    }

    public function parseIlluminateRequest()
    {

        $http_request = new IlluminateHttpRequest(
            $this->get,
            $this->post,
            $this->fastcgi,
            $this->cookie,
            $this->files,
            array_merge($this->server, $this->header),
            $this->content
        );

        return $http_request;
    }

}
