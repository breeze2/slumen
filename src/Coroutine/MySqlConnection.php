<?php
namespace BL\Slumen\Coroutine;

use Exception;
use Closure;
use Illuminate\Database\MySqlConnection as IlluminateMySqlConnection;

class MySqlConnection extends IlluminateMySqlConnection
{

    /**
     * The name of Provider
     * @var string
     */
    protected $provider_name;

    /**
     * The last time used connection.
     * @var int
     */
    protected $last_used_at;

    /**
     * Is connection destroyed?
     * @var boolean
     */
    protected $is_destroyed = false;

    /**
     * The active MySqlClient connection.
     * @var MySqlClient
     */
    protected $pdo;

    public function __construct(MySqlClient $pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        if (isset($config['provider_name'])) {
            $this->provider_name = $config['provider_name'];
        }
        parent::__construct(function () use ($pdo) {
            return $pdo;
        }, $database, $tablePrefix, $config);
        $this->pdo = $pdo;
    }

    /**
     * Get the MySqlClient connection.
     * @return MySqlClient
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    public function isDestroyed()
    {
        return $this->is_destroyed;
    }

    public function destroy()
    {
        $this->is_destroyed = true;
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }
            $bindings = $this->prepareBindings($bindings);
            return $this->getPdo()->runSql($query, $bindings);
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            if ($useReadPdo) { // TODO: use read PDO
            }

            $bindings = $this->prepareBindings($bindings);
            $statement = $this->getPdo()->fetchSql($query, $bindings);

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
            $this->getPdo()->runSql($query, $bindings);

            $this->recordsHaveBeenModified(
                ($count = $this->getPdo()->affectedRowCount()) > 0
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
            $result = $this->getPdo()->runSql($query, $bindings);
            $this->recordsHaveBeenModified();

            return $result;
        });
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     *
     * @throws Exception
     */
    protected function run($query, $bindings, Closure $callback)
    {
        try {
            return parent::run($query, $bindings, $callback);
        } catch (Exception $e) {
            if ($this->provider_name) {
                app($this->provider_name)->destroy($this);
            }
            throw $e;
        }
    }
}
