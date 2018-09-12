<?php

namespace BL\Slumen\Redis;

use BL\Slumen\Redis\Connections\ConnectionSuit;
use Illuminate\Redis\RedisManager as BaseRedisManager;

class RedisManager extends BaseRedisManager
{
    protected $connection_pools = [];

    protected function getConnectionPool($name = null) {
        $name = $name ?: 'default';
        if (isset($this->connection_pools[$name])) {
            return $this->connection_pools[$name];
        }
        return $this->connection_pools[$name] = new RedisPool();
    }

    public function popConnection($name = null) {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        if(!$pool->isFull()) {
            $connection = $this->resolve($name);
            $connection = new ConnectionSuit($name, $connection);
            return $pool->found($connection);
        }
        $connection = $pool->pop();
        if($connection->getLastUsedAt() < time() - 120) {
            $connection = $this->resolve($name);
        }
        return $connection;
    }

    public function destroyConnection($name = null, ConnectionSuit $connection) {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        return $pool->destroy($connection);
    }

    public function pushConnection($name = null, ConnectionSuit $connection) {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        $pool->push($connection);
    }

    public function connection($name = null)
    {
        $name = $name ?: 'default';
        $connection = parent::connection($name);
        $connection->setSuitName($name);
        return $connection;
    }

    /**
     * Get the connector instance for the current driver.
     *
     * @return \BL\Slumen\Redis\Connectors\PhpRedisConnector|\BL\Slumen\Redis\Connectors\PredisConnector
     */
    protected function connector()
    {
        switch ($this->driver) {
            case 'predis':
                return new Connectors\PredisConnector;
            case 'phpredis':
                return new Connectors\PhpRedisConnector;
        }
    }
}
