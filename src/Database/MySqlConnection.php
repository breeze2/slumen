<?php
namespace BL\Slumen\Database;

use Closure;
use Exception;
use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class MySqlConnection extends BaseMySqlConnection
{
    protected $last_used_at;

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function run($query, $bindings, Closure $callback)
    {
        try {
            return parent::run($query, $bindings, $callback);
        } catch (Exception $e) {
            app(MySqlServiceProvider::PROVIDER_NAME_MYSQL)->destroy($this);
            throw $e;
        }
    }
}
