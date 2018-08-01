<?php
namespace BL\Slumen\Database;

use BL\Slumen\Database\Query\Processors\MySqlProcessor;
use Illuminate\Database\MySqlConnection;

class CoMySqlPoolConnection extends MySqlConnection
{
    public function __construct(CoMySqlManager $pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \BL\Slumen\Database\Query\Processors\MySqlProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new MySqlProcessor;
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

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $bindings = $this->prepareBindings($bindings);
            $client = $this->pdo->pop();
            $statement = $client->fetchSql($query, $bindings);
            $this->pdo->push($client);
            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield json_encode(json_encode($record));
        }
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            $bindings = $this->prepareBindings($bindings);
            $client   = $this->pdo->pop();
            $result   = $client->runSql($query, $bindings);

            $this->recordsHaveBeenModified(
                ($count = $client->affectedRowCount()) > 0
            );
            $this->pdo->push($client);
            return $count;
        });
    }

    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $bindings = $this->prepareBindings($bindings);
            $client   = $this->pdo->pop();
            $result   = $client->runSql($query, $bindings);
            $last_id  = $client->lastInsertId();
            $this->recordsHaveBeenModified();
            $this->pdo->push($client);
            $this->pdo->setLastInsertId($last_id);
            return $result;
        });
    }

    public function autoRecycling($timeout, $sleep)
    {
        $this->pdo->autoRecycling($timeout, $sleep);
    }

    public function getClientNumber()
    {
        return $this->pdo->getNumber();
    }
}
