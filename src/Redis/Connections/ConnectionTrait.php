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
        $name   = $this->getSuitName();
        $result = null;
        $suit   = null;
        try {
            $manager    = app(RedisServiceProvider::PROVIDER_NAME_REDIS);
            $suit       = $manager->popConnection($name);
            $connection = $suit->getConnection();
            $result     = $connection->parentCommand($method, $parameters);
            $manager->pushConnection($name, $suit);
        } catch (Exception $e) {
            if ($suit) {
                $manager->destroyConnection($suit);
            }
            throw $e;
        }
        return $result;
    }
}
