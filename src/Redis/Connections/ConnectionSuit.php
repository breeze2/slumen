<?php

namespace BL\Slumen\Redis\Connections;
use Illuminate\Redis\Connections\Connection;

class ConnectionSuit
{
    protected $name;
	protected $connection;
	protected $last_used_at;
    protected $is_destroyed = false;
	
	public function __construct($name, Connection $connection)
	{
		$this->name = $name;
		$this->connection = $connection;
		$this->last_used_at = time();
        $this->is_destroyed = false;
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

    /**
     * [isDestroyed]
     * @param  boolean|null $destroyed
     * @return boolean
     */
    public function isDestroyed($destroyed = null)
    {
        if($destroyed) {
            $this->is_destroyed = true;
        }
        return $this->is_destroyed;
    }
}

