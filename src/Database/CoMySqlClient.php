<?php
namespace BL\Slumen\Database;

use Swoole\Coroutine\MySQL;

class CoMySqlClient extends MySQL
{
    protected $last_used_at;

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function isFetchMode()
    {
        if (array_key_exists('fetch_mode', $this->serverInfo)) {
            return $this->serverInfo['fetch_mode'];
        }
        return false;
    }

    public function runSql($query, array $bindings = [])
    {
        $statement = $this->prepare($query);
        $result    = $statement->execute($bindings);
        if ($this->isFetchMode() && $result) {
            $result = $statement->fetchAll();
        }
        return json_decode(json_encode($result));
    }
}
