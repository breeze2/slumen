<?php
namespace BL\Slumen\Database;

use Illuminate\Database\MySqlConnection;

class CoMySqlConnection extends MySqlConnection
{
    public function __construct(CoMySqlClient $pdo, $database = '', $tablePrefix = '', array $config = [])
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
            return $this->pdo->runSql($query, $bindings);
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $bindings = $this->prepareBindings($bindings);
            $statement = $this->pdo->fetchSql($query, $bindings);

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
            $this->pdo->runSql($query, $bindings);

            $this->recordsHaveBeenModified(
                ($count = $this->pdo->affectedRowCount()) > 0
            );

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
            $result = $this->pdo->runSql($query, $bindings);
            $this->recordsHaveBeenModified();

            return $result;
        });
    }
}
