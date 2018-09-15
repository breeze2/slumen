<?php

namespace BL\Slumen\Redis;

use BL\Slumen\Redis\Connections\ConnectionSuit;
use Closure;
use Illuminate\Redis\RedisManager as BaseRedisManager;
use LogicException;

class RedisManager extends BaseRedisManager
{
    protected $connection_pools      = [];
    protected $connection_pool_maker = null;

    public function setConnectionPoolMaker(Closure $maker)
    {
        $this->connection_pool_maker = $maker;
    }

    public function makerConnectionPool()
    {
        if (is_callable($this->connection_pool_maker)) {
            return call_user_func($this->connection_pool_maker);
        }
        throw new LogicException('No redis connection pool maker available.');
    }

    /**
     * [getConnectionPool]
     * @param  string|null $name
     * @return RedisPool
     */
    protected function getConnectionPool($name = null)
    {
        $name = $name ?: 'default';
        if (isset($this->connection_pools[$name])) {
            return $this->connection_pools[$name];
        }
        return $this->connection_pools[$name] = $this->makerConnectionPool();
    }

    /**
     * [popConnection]
     * @param  string|null $name
     * @return ConnectionSuit
     */
    public function popConnection($name = null)
    {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        if (!$pool->isFull()) {
            $connection = $this->resolve($name);
            $suit       = new ConnectionSuit($name, $connection);
            return $pool->found($suit);
        }
        $suit = $pool->pop();
        if ($pool->isExpired($suit)) {
            $connection = $this->resolve($name);
            $suit       = new ConnectionSuit($name, $connection);
        } else {
            $suit->setLastUsedAt(time());
        }
        return $suit;
    }

    public function destroyConnection($name = null, ConnectionSuit $connection)
    {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        return $pool->destroy($connection);
    }

    public function pushConnection($name = null, ConnectionSuit $connection)
    {
        $name = $name ?: 'default';
        $pool = $this->getConnectionPool($name);
        $pool->push($connection);
    }

    public function connection($name = null)
    {
        $name       = $name ?: 'default';
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
