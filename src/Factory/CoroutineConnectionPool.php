<?php
namespace BL\Slumen\Factory;
use BL\Slumen\Exceptions\FetchTimeoutException;

abstract class CoroutineConnectionPool {
    protected $max_number;
    protected $min_number;
    protected $channel;
    protected $expire;
    protected $number;
    protected $is_recycling = false;

    public function isFull()
    {
        return $this->number >= $this->max_number;
    }

    public function isEmpty()
    {
        return $this->number <= 0;
    }

    public function isExpiredConnection(CoroutineConnectionInterface $connection)
    {
        return $connection->getLastUsedAt() < time() - $this->expire;
    }

    public function shouldRecover()
    {
        return $this->number > $this->min_number;
    }

    protected function increase()
    {
        return $this->number += 1;
    }

    protected function decrease()
    {
        return $this->number -= 1;
    }

    abstract protected function buildConnection();
    abstract protected function rebuildConnection($connection);

    /**
     * [foundConnection]
     * @param  CoroutineConnectionInterface $connection
     * @return CoroutineConnectionInterface
     */
    public function foundConnection(CoroutineConnectionInterface $connection)
    {
        if (!$this->isFull()) {
            $this->increase();
        }
        return $connection;
    }

    /**
     * [destroyConnection]
     * @param  CoroutineConnectionInterface $connection
     * @return boolean
     */
    public function destroyConnection(CoroutineConnectionInterface $connection)
    {
        $connection->destroy();
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }
        return false;
    }

    /**
     * [push]
     * @param  CoroutineConnectionInterface $connection
     * @return void
     */
    public function push(CoroutineConnectionInterface $connection)
    {
        if (!$this->channel->isFull() && !$connection->isDestroyed()) {
            $this->channel->push($connection);
            return;
        }
    }

    /**
     * [pop]
     * @param  integer $timeout
     * @return CoroutineConnectionInterface
     */
    public function pop($timeout = 0)
    {
        if ($connection = $this->buildConnection()) {
            return $connection;
        }
        $connection = $this->channel->pop($timeout);
        if ($connection === false) {
            throw new FetchTimeoutException('Error fetch MySQL connection in pool timeout.');
        }

        if ($this->isExpiredConnection($connection)) {
            return $this->rebuildConnection($connection);
        }
        $connection->setLastUsedAt(time());
        return $connection;
    }

    public function autoRecycling($timeout = 120, $sleep = 20)
    {
        if (!$this->is_recycling) {
            $this->is_recycling = true;
            while (1) {
                Coroutine::sleep($sleep);
                if ($this->shouldRecover()) {
                    $connection = $this->channel->pop();
                    $now = time();
                    if ($now - $connection->getLastUsedAt() > $timeout) {
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($connection);
                    }
                }
            }
        }
    }
}