<?php
namespace BL\Slumen\Database;

use Illuminate\Database\MySqlConnection;

class CoMySqlPoolConnection extends MySqlConnection
{
    public function __construct(CoMySqlManager $pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $bindings = $this->prepareBindings($bindings);
            // return $this->pdo->runSql($query, $bindings);
            $client = $this->pdo->pop();
            $result = $client->runSql($query, $bindings);
            $this->pdo->push($client);
            return $result;
        });
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            $bindings = $this->prepareBindings($bindings);
            $client = $this->pdo->pop();
            $result = $client->runSql($query, $bindings);

            $this->recordsHaveBeenModified(
                ($count = $client->affectedRowCount()) > 0
            );
            $this->pdo->push($client);
            return $count;
        });
    }

    public function autoRecycling($timeout, $sleep) {
    	$this->pdo->autoRecycling($timeout, $sleep);
    }
}
