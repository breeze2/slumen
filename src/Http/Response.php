<?php

namespace BL\Slumen\Http;

use swoole_http_response as SwooleHttpResponse;

class Response
{
    protected $response = null;

    public function __construct(SwooleHttpResponse $response)
    {
        $this->response = $response;
    }

    public function end($string)
    {
        $this->response->end($string);
    }

    public function header(string $key, string $value, bool $ucwords = true)
    {
        $this->response->header($key, $value, $ucwords);
    }

    public function status($code)
    {
        $this->response->status($code);
    }

    public function sendFile($file, $offset = 0, $length = 0)
    {
        $this->response->sendfile($file, $offset, $length);
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $values) {
            $this->response->header($name, implode(',', $values));
        }
    }

    public function setCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->response->rawcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    public function getHeaders()
    {
        return $this->response->header;
    }

}
