<?php
namespace BL\Slumen\Database\Query\Processors;

use BL\Slumen\Database\CoMySqlClient;
use BL\Slumen\Database\CoMySqlManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\MySqlProcessor as Processor;

class CoMySqlProcessor extends Processor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        // $id = $query->getConnection()->getPdo()->lastInsertId($sequence);
        $pdo = $query->getConnection()->getPdo();
        if ($pdo instanceof CoMySqlManager) {
            $id = $pdo->getLastInsertId();
        } else if ($pdo instanceof CoMySqlClient) {
            $id = $pdo->lastInsertId();
        } else {
            $id = $pdo->lastInsertId($sequence);
        }

        return is_numeric($id) ? (int) $id : $id;
    }
}
