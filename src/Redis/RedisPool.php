<?php

namespace BL\Slumen\Redis;

use BL\Slumen\Redis\Connections\ConnectionSuit;
use Swoole\Coroutine\Channel as CoChannel;

class RedisPool
{
    protected $max_number;
    protected $min_number;
    protected $timeout;
    protected $number;
    protected $expire;
    protected $channel;

    public function __construct($max_number = 50, $min_number = 0, $timeout = 10, $expire = 180)
    {
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->timeout    = $timeout;
        $this->expire     = $expire;
        $this->number     = 0;
        $this->channel    = new CoChannel($max_number);
    }

    /**
     * [isFull]
     * @return boolean
     */
    public function isFull()
    {
        return $this->number >= $this->max_number;
    }

    /**
     * [isEmpty]
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->number <= 0;
    }

    public function isExpire(ConnectionSuit $connection)
    {
        return $connection->getLastUsedAt() < time() - $this->expire;
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

    /**
     * [found]
     * @param  ConnectionSuit $connection
     * @return ConnectionSuit
     */
    public function found(ConnectionSuit $connection)
    {
    	if (!$this->isFull()) {
            $this->increase();
        }
        return $connection;
    }

    /**
     * [destroy]
     * @param  ConnectionSuit $connection
     * @return boolean
     */
    public function destroy(ConnectionSuit $connection)
    {
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }
        return false;
    }

    /**
     * [push]
     * @param  ConnectionSuit $connection
     * @return void
     */
    public function push(ConnectionSuit $connection)
    {
        if (!$this->channel->isFull()) {
            $this->channel->push($connection);
            return;
        }
    }

    /**
     * [pop]
     * @param  float|null $timeout
     * @return ConnectionSuit
     */
    public function pop($timeout = 0)
    {
        $connection = $this->channel->pop($timeout);
        if ($connection === false) {
            throw new FetchTimeoutException('Error Fetch Redis Connection Timeout.');
        }
        return $connection;
    }
}
