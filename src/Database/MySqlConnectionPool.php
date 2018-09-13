<?php
namespace BL\Slumen\Database;

use Illuminate\Database\Connectors\MySqlConnector;
use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as CoChannel;

class MySqlConnectionPool
{
    protected $max_number;
    protected $min_number;
    protected $channel;
    protected $config;
    protected $expire;
    protected $number;
    private $is_recycling   = false;
    private $last_insert_id = 0;

    public function __construct(array $config, $max_number = 50, $min_number = 0, $expire = 120)
    {
        // $config['name']   = 'mysql';
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->channel    = new CoChannel($max_number);
        $this->config     = $config;
        $this->expire     = $expire;
        $this->number     = 0;
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

    protected function build()
    {
        if (!$this->isFull()) {
            $this->increase();

            $connector  = new MySqlConnector();
            $pdo        = $connector->connect($this->config);
            $connection = new MySqlConnection($pdo, $this->config['database'], $this->config['prefix'], $this->config);
            $connection->setLastUsedAt(time());
            return $connection;
        }
        return false;
    }

    protected function rebuild(MySqlConnection $connection)
    {
        $connection->reconnect();
        $connection->setLastUsedAt(time());
        return $connection;
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
        if (!$this->channel->isFull()) {
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
            throw new FetchTimeoutException('Error Fetch MySQL Connection Timeout.');
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
                Co::sleep($sleep);
                if ($this->shouldRecover()) {
                    $connection = $this->channel->pop();
                    $now        = time();
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
