<?php
namespace BL\Slumen\Runtime;
use PDO;
use Closure;
use Exception;
use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class MySqlConnection extends BaseMySqlConnection
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
     * Create a new mysql connection instance.
     * @return void
     */
    public function __construct(PDO $pdo, string $database = '', string $tablePrefix = '', array $config = [])
    {
        if (isset($config['provider_name'])) {
            $this->provider_name = $config['provider_name'];
        }
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $this->pdo = $pdo;
    }

    public function getLastUsedAt()
    {
        return $this->last_used_at;
    }

    public function setLastUsedAt($time)
    {
        $this->last_used_at = $time;
    }

    /**
     * [isDestroyed]
     * @return boolean
     */
    public function isDestroyed()
    {
        return $this->is_destroyed;
    }

    public function destroy()
    {
        $this->is_destroyed = true;
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
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
