<?php

namespace BL\Slumen\Database\Eloquent;

use BL\Slumen\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected $runtime_connection;

    public function setConnection(ConnectionInterface $connection)
    {
        $this->runtime_connection = $connection;
    }

    public function getConnection()
    {
    	if($this->runtime_connection) {
        	return $this->runtime_connection;
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

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method === 'useConnection') {
            return $this->setConnection(...$parameters);
        } else {
            return parent::__call($method, $parameters);
        }
    }
}
