<?php
namespace BL\Slumen\Database;

use Swoole\Coroutine\MySQL;

class CoMySqlClient extends MySQL
{
    protected $last_used_at;
    protected $is_fetch_mode;

    public function connect(array $server_info)
    {
        if (array_key_exists('fetch_mode', $server_info)) {
            $this->is_fetch_mode = !!$server_info['fetch_mode'];
        } else {
            $this->is_fetch_mode = false;
        }
        return parent::connect($server_info);
    }

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function isFetchMode()
    {
        return $this->is_fetch_mode;
    }

    public function affectedRowCount()
    {
        return $this->affected_rows;
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

    public function fetchSql($query, array $bindings = [])
    {
        $statement = $this->prepare($query);
        $result    = $statement->execute($bindings);
        return $statement;
    }

    public function lastInsertId()
    {
        return $this->insert_id;
    }
}
