<?php
namespace BL\Slumen\Database;

use Swoole\Coroutine\MySQL;

class CoMySqlClient extends MySQL
{
    protected $last_used_at;
    protected $fetch_mode;

    public function connect(array $server_info)
    {
        if (array_key_exists('fetch_mode', $server_info)) {
            $this->fetch_mode = !!$server_info['fetch_mode'];
        } else {
            $this->fetch_mode = false;
        }
        return parent::connect($server_info);
    }

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
        return $this->fetch_mode;
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
