<?php

namespace BL\Slumen\Database\Eloquent;

use BL\Slumen\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected $the_connection;

    public function useConnection(ConnectionInterface $connection)
    {
        $this->the_connection = $this->connection;
    }

    public function getConnection()
    {
    	if($this->the_connection) {
        	return $this->the_connection;
    	} else {
    		return parent::getConnection();
    	}
    }

    /**
     * Save the model to the database using transaction.
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function saveOrFail(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            return $this->save($options);
        });
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }
}
