<?php
namespace BL\Slumen\Runtime;

use BL\Slumen\Factory\CoroutineConnectionPool;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Swoole\Coroutine\Channel as CoroutineChannel;

class RedisConnectionPool extends CoroutineConnectionPool
{
    protected $config;
    protected $driver;

    /**
     * Indicates whether event dispatcher is set on connections.
     *
     * @var bool
     */
    protected $events = false;

    public function __construct(array $config, $max_number = 50, $min_number = 0, $expire = 120)
    {
        $this->driver = Arr::pull($config, 'client', 'predis');

        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->channel    = new CoroutineChannel($max_number);
        $this->config     = $config;
        $this->expire     = $expire;
        $this->number     = 0;
    }

    /**
     * Get the connector instance for the current driver.
     *
     * @return PhpRedisConnector|PredisConnector
     */
    protected function connector()
    {
        switch ($this->driver) {
            case 'predis':
                return new PredisConnector;
            case 'phpredis':
                return new PhpRedisConnector;
        }
    }

    /**
     * Resolve the given connection by name.
     *
     * @param  string|null  $name
     * @return Connection
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name = null)
    {
        $name = $name ?: 'default';

        $options = $this->config['options'] ?? [];

        if (isset($this->config[$name])) {
            return $this->connector()->connect($this->config[$name], $options);
        }

        if (isset($this->config['clusters'][$name])) {
            return $this->resolveCluster($name);
        }

        throw new InvalidArgumentException("Redis connection [{$name}] not configured.");
    }

    /**
     * Resolve the given cluster connection by name.
     *
     * @param  string  $name
     * @return Connection
     */
    protected function resolveCluster($name)
    {
        $clusterOptions = $this->config['clusters']['options'] ?? [];

        return $this->connector()->connectToCluster(
            $this->config['clusters'][$name], $clusterOptions, $this->config['options'] ?? []
        );
    }

    /**
     * Configure the given connection to prepare it for commands.
     *
     * @param  Connection  $connection
     * @param  string  $name
     * @return Connection
     */
    protected function configure(Connection $connection, $name)
    {
        if (method_exists($connection, 'setName')) {
            $connection->setName($name);
        }
        if ($this->events && app()->bound('events')) {
            $connection->setEventDispatcher(app()->make('events'));
        }

        return $connection;
    }

    /**
     * [buildConnection]
     * @return RedisConnection|false
     */
    protected function buildConnection()
    {
        if (!$this->isFull()) {
            $this->increase();
            $connection_name  = isset($this->config['connection_name']) ? $this->config['connection_name'] : '';
            $inner_connection = $this->configure($this->resolve($connection_name), $connection_name);

            $connection = new RedisConnection($inner_connection, $this->config);
            $connection->setLastUsedAt(time());
            return $connection;
        }
        return false;
    }

    /**
     * [rebuildConnection]
     * @param RedisConnection $connection
     * @return RedisConnection|false
     */
    protected function rebuildConnection($connection)
    {
        $connection_name  = isset($this->config['connection_name']) ? $this->config['connection_name'] : '';
        $inner_connection = $this->configure($this->resolve($connection_name), $connection_name);
        $connection       = new RedisConnection($inner_connection, $this->config);
        $connection->setLastUsedAt(time());
        return $connection;
    }
}
