<?php

namespace BL\Slumen\Redis\Connections;

use BL\Slumen\Redis\RedisServiceProvider;
use Illuminate\Redis\Connections\Connection as BaseConnection;
use Exception;

/**
 * @mixin \Predis\Client
 */
abstract class Connection extends BaseConnection
{
    protected $last_used_at;

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function parentCommand($method, array $parameters = [])
    {
        return parent::command($method, $parameters);
    }

    public function command($method, array $parameters = [])
    {
        $name = $this->getName();
        $redis_manager = app(RedisServiceProvider::PROVIDER_NAME_REDIS);
        $connection = $redis_manager->popConnection($name);
        try {
            $result = $connection->parentCommand($method, $parameters);
        } catch (Exception $e){
            $redis_manager->destroyConnection($connection);
            throw $e;
            $result = null;
        }
        $redis_manager->pushConnection($name, $connection);
        return $result;
    }
}
