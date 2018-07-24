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

            return $this->pdo->runSql($query, $bindings);
        });
    }
}
