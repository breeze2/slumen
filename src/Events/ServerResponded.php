<?php

namespace BL\Slumen\Events;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;

class ServerResponded
{
    public $request;
    public $response;
    public function __construct(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        $this->$request  = $request;
        $this->$response = $response;
    }
}
