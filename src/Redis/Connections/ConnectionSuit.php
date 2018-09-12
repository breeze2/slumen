<?php

namespace BL\Slumen\Redis\Connections;
use Illuminate\Redis\Connections\Connection;

class ConnectionSuit
{
    protected $name;
	protected $connection;
	protected $last_used_at;
	
	public function __construct($name, Connection $connection)
	{
		$this->name = $name;
		$this->connection = $connection;
		$this->last_used_at = time();
	}

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getConnection()
    {
    	return $this->connection;
    }
}

