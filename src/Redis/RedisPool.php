<?php

namespace BL\Slumen\Redis;

use BL\Slumen\Redis\Connections\Connection as RedisConnection;
use Swoole\Coroutine\Channel as CoChannel;

class RedisPool
{
    protected $max_number;
    protected $min_number;
    protected $timeout;
    protected $number;
    protected $expire;
    protected $channel;

    public function __construct($max_number = 20, $min_number = 0, $timeout = 10, $expire = 120)
    {
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->timeout    = $timeout;
        $this->expire     = $expire;
        $this->number     = 0;
        $this->channel    = new CoChannel($max_number);
    }

    public function isFull()
    {
        return $this->number >= $this->max_number;
    }

    public function isEmpty()
    {
        return $this->number <= 0;
    }

    public function shouldRecover()
    {
        return $this->number > $this->min_number;
    }

    private function increase()
    {
        return $this->number += 1;
    }

    private function decrease()
    {
        return $this->number -= 1;
    }

    public function found(RedisConnection $connection)
    {
    	if (!$this->isFull()) {
            $this->increase();
        }
        return $connection;
    }

    public function destroy(RedisConnection $connection)
    {
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }
        return false;
    }

    public function push(RedisConnection $connection)
    {
        if (!$this->channel->isFull()) {
            $this->channel->push($connection);
            return;
        }
    }

    public function pop($timeout = 0)
    {
        $connection = $this->channel->pop($timeout);
        if ($connection === false) {
            throw new FetchTimeoutException('Error Fetch Connection Timeout.');
            return false;
        }
        return $connection;
    }
}