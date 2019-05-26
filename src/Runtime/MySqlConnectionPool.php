<?php
namespace BL\Slumen\Runtime;

use BL\Slumen\Database\FetchTimeoutException;
use Illuminate\Database\Connectors\MySqlConnector;
use Swoole\Coroutine as Coroutine;
use Swoole\Coroutine\Channel as CoroutineChannel;
use BL\Slumen\Factory\CoroutineConnectionPool;

class MySqlConnectionPool extends CoroutineConnectionPool
{
    protected $config;
    private $last_insert_id = 0;

    public function __construct(array $config, $max_number = 50, $min_number = 0, $expire = 120)
    {
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->channel    = new CoroutineChannel($max_number);
        $this->config     = $config;
        $this->expire     = $expire;
        $this->number     = 0;
    }

    /**
     * [buildConnection]
     * @return MySqlConnection|false
     */
    protected function buildConnection()
    {
        if (!$this->isFull()) {
            $this->increase();

            $pdo = $this->makePdo();
            $connection = new MySqlConnection($pdo, $this->config['database'], $this->config['prefix'], $this->config);
            $connection->setLastUsedAt(time());
            $connection->setReconnector(function ($connection) {
                $connection->setPdo($this->makePdo());
            });
            return $connection;
        }
        return false;
    }

    /**
     * [rebuildConnection]
     * @param MySqlConnection $connection
     * @return MySqlConnection|false
     */
    protected function rebuildConnection($connection)
    {
        $pdo = $this->makePdo();
        $connection->setPdo($pdo);
        $connection->setLastUsedAt(time());
        return $connection;
    }

    protected function makePdo()
    {
        $connector = new MySqlConnector();
        $pdo       = $connector->connect($this->config);
        return $pdo;
    }
}
