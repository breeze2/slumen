<?php
namespace BL\Slumen\Coroutine;

use BL\Slumen\Exceptions\PrepareException;
use Swoole\Coroutine\MySQL;

class MySqlClient extends MySQL
{
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
        if ($statement === false) {
            throw new PrepareException('Query Prepare Error. No.' . $this->errno . ': ' . $this->error);
        }
        $result = $statement->execute($bindings);
        if ($this->isFetchMode() && $result) {
            $result = $statement->fetchAll();
        }
        return json_decode((string) json_encode($result));
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
