<?php
namespace BL\Slumen\Runtime;
use PDO;
use Closure;
use Exception;
use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

use BL\Slumen\Factory\CoroutineConnectionInterface;
use BL\Slumen\Factory\CoroutineConnectionTrait;

class MySqlConnection extends BaseMySqlConnection implements CoroutineConnectionInterface
{
    use CoroutineConnectionTrait;

    /**
     * The name of Provider
     * @var string
     */
    protected $provider_name;

    /**
     * Create a new mysql connection instance.
     * @return void
     */
    public function __construct(PDO $pdo, string $database = '', string $tablePrefix = '', array $config = [])
    {
        if (isset($config['provider_name'])) {
            $this->getProviderName($config['provider_name']);
        }
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $this->pdo = $pdo;
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
            if ($provider = $this->getProviderName()) {
                app($provider)->destroyConnection($this);
            }
            throw $e;
        }
    }
}
