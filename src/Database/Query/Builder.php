<?php
namespace BL\Slumen\Database\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\ConnectionInterface;

class Builder extends QueryBuilder
{
	public function useConnection(ConnectionInterface $connection)
	{
		$this->connection = $connection;
	}
}
