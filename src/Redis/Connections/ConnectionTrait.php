<?php

namespace BL\Slumen\Redis\Connections;

use BL\Slumen\Redis\RedisServiceProvider;
use Exception;

trait ConnectionTrait
{
    protected $suit_name;

    public function setSuitName($name)
    {
        $this->suit_name = $name;
    }

    public function getSuitName()
    {
        return $this->suit_name;
    }

    public function parentCommand($method, array $parameters = [])
    {
        return parent::command($method, $parameters);
    }

    public function command($method, array $parameters = [])
    {
        $name       = $this->getSuitName();
        $manager    = app(RedisServiceProvider::PROVIDER_NAME_REDIS);
        $suit       = $manager->popConnection($name);
        $connection = $suit->getConnection();
        try {
            $result = $connection->parentCommand($method, $parameters);
        } catch (Exception $e) {
            $manager->destroyConnection($suit);
            throw $e;
            $result = null;
        }
        $manager->pushConnection($name, $suit);
        return $result;
    }
}
