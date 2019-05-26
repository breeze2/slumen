<?php
namespace BL\Slumen\Runtime;

use Closure;
use Exception;

use BL\Slumen\Factory\CoroutineConnectionInterface;
use BL\Slumen\Factory\CoroutineConnectionTrait;
use Illuminate\Redis\Connections\Connection;

/**
 * @mixin Connection
 */
class RedisConnection implements CoroutineConnectionInterface
{
    use CoroutineConnectionTrait;
    /**
     * The name of Provider
     * @var string
     */
    protected $provider_name;

    /**
     * The real connection
     * @var Connection
     */
    private $inner_connection;

    /**
     * Create a new redis connection instance.
     * @return void
     */
    public function __construct(Connection $connection, array $config = [])
    {
        if (isset($config['provider_name'])) {
            $this->getProviderName($config['provider_name']);
        }
        $this->inner_connection = $connection;
    }

    /**
     * Handle dynamic method calls into the connection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->inner_connection->{$method}(...$parameters);
        } catch (Exception $e) {
            if ($provider = $this->getProviderName()) {
                app($provider)->destroyConnection($this);
            }
            throw $e;
        }
    }
}
