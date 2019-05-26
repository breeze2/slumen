<?php
namespace BL\Slumen\Coroutine;

use BL\Slumen\Database\FetchTimeoutException;
use Illuminate\Database\Connectors\MySqlConnector;
use Swoole\Coroutine as Coroutine;
use RuntimeException;
use Swoole\Coroutine\Channel as CoroutineChannel;
use BL\Slumen\Factory\CoroutineConnectionPool;

class MySqlConnectionPool extends CoroutineConnectionPool
{
    protected $config;
    protected $number;
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

    /**
     * [rebuild]
     * @param MySqlConnection $connection
     * @return MySqlConnection|false
     */
    protected function rebuild($connection)
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

}
