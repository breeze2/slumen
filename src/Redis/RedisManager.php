<?php

namespace BL\Slumen\Redis;

use BL\Slumen\Redis\Connections\ConnectionSuit;
use Illuminate\Redis\RedisManager as BaseRedisManager;

class RedisManager extends BaseRedisManager
{
    protected $connection_pools = [];

    /**
     * [getConnectionPool]
     * @param  string|null $name
     * @return RedisPool
     */
    protected function getConnectionPool($name = null) {
        $name = $name ?: 'default';
        if (isset($this->connection_pools[$name])) {
            return $this->connection_pools[$name];
        }
        return $this->connection_pools[$name] = new RedisPool();
    }

    /**
     * [popConnection]
     * @param  string|null $name
     * @return ConnectionSuit
     */
    public function popConnection($name = null) {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        if(!$pool->isFull()) {
            $connection = $this->resolve($name);
            $suit = new ConnectionSuit($name, $connection);
            return $pool->found($suit);
        }
        $suit = $pool->pop();
        if($pool->isExpire($suit)) {
            $connection = $this->resolve($name);
            $suit = new ConnectionSuit($name, $connection);
        } else {
            $suit->setLastUsedAt(time());
        }
        return $suit;
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
