<?php
namespace BL\Slumen\Coroutine;

use Illuminate\Database\Connectors\MySqlConnector;
use Swoole\Coroutine as Coroutine;
use RuntimeException;
use Swoole\Coroutine\Channel as CoroutineChannel;

class MySqlConnectionPool
{
    protected $max_number;
    protected $min_number;
    protected $channel;
    protected $config;
    protected $expire;
    protected $number;
    private $is_recycling = false;
    private $last_insert_id = 0;

    protected $client_config;

    public function __construct(array $config, $max_number = 50, $min_number = 0, $expire = 120)
    {
        if (!isset($config['provider_name'])) {
            throw new RuntimeException('MySqlConnectionPool need provider name.');
        }
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->channel = new CoroutineChannel($max_number);
        $this->config = $config;
        $this->expire = $expire;
        $this->number = 0;

        $this->client_config = [
            'host'        => $config['host'],
            'port'        => $config['port'],
            'user'        => $config['username'],
            'password'    => $config['password'],
            'database'    => $config['database'],
            'charset'     => $config['charset'],
            'strict_type' => $config['strict'],
            'fetch_mode'  => true,
            'timeout'     => -1,
        ];
    }

    public function isFull()
    {
        return $this->number >= $this->max_number;
    }

    public function isEmpty()
    {
        return $this->number <= 0;
    }

    public function isExpired(MySqlConnection $connection)
    {
        return $connection->getLastUsedAt() < time() - $this->expire;
    }

    public function shouldRecover()
    {
        return $this->number > $this->min_number;
    }

    private function increase()
    {
        return $this->number += 1;
    }

    private function decrease()
    {
        return $this->number -= 1;
    }

    /**
     * [build]
     * @return MySqlConnection|false
     */
    protected function build()
    {
        if (!$this->isFull()) {
            $this->increase();

            $pdo = $this->makePdo();
            $connection = new MySqlConnection($pdo, $this->config['database'], $this->config['prefix'], $this->config);
            $connection->setLastUsedAt(time());
            return $connection;
        }
        return false;
    }

    protected function rebuild(MySqlConnection $connection)
    {
        $connection->getPdo()->connect($this->client_config);
        $connection->setLastUsedAt(time());
        return $connection;
    }

    protected function makePdo()
    {
        $mysql_client = new MySqlClient();
        $mysql_client->connect($this->client_config);
        return $mysql_client;
    }

    /**
     * [found]
     * @param  MySqlConnection $connection
     * @return MySqlConnection
     */
    public function found(MySqlConnection $connection)
    {
        if (!$this->isFull()) {
            $this->increase();
        }
        return $connection;
    }

    /**
     * [destroy]
     * @param  MySqlConnection $connection
     * @return boolean
     */
    public function destroy(MySqlConnection $connection)
    {
        $connection->destroy();
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }
        return false;
    }

    /**
     * [push]
     * @param  MySqlConnection $connection
     * @return void
     */
    public function push(MySqlConnection $connection)
    {
        if (!$this->channel->isFull() && !$connection->isDestroyed()) {
            $this->channel->push($connection);
            return;
        }
    }

    /**
     * [pop]
     * @param  integer $timeout
     * @return MySqlConnection
     */
    public function pop($timeout = 0)
    {
        if ($connection = $this->build()) {
            return $connection;
        }
        $connection = $this->channel->pop($timeout);
        if ($connection === false) {
            throw new FetchTimeoutException('Error fetch MySQL connection in pool timeout.');
        }

        if ($this->isExpired($connection)) {
            return $this->rebuild($connection);
        }
        $connection->setLastUsedAt(time());
        return $connection;
    }

    public function autoRecycling($timeout = 120, $sleep = 20)
    {
        if (!$this->is_recycling) {
            $this->is_recycling = true;
            while (1) {
                Coroutine::sleep($sleep);
                if ($this->shouldRecover()) {
                    $connection = $this->channel->pop();
                    $now = time();
                    if ($now - $connection->getLastUsedAt() > $timeout) {
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($connection);
                    }
                }
            }
        }
    }
}
