<?php
namespace BL\Slumen\Database;

use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as CoChannel;

class CoMySqlManager
{
    protected $max_number;
    protected $min_number;
    protected $config;
    protected $channel;
    protected $number;
    private $is_recycling = false;
    private $last_insert_id = 0;

    public function __construct(array $config, $max_number = 120, $min_number = 0)
    {
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->config     = $config;
        $this->channel    = new CoChannel($max_number);
        $this->number    = 0;
    }

    public function isFull()
    {
        return $this->number >= $this->max_number;
    }

    public function isEmpty()
    {
        return $this->number <= 0;
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
            $mysql = new CoMySqlClient();
            $mysql->connect($this->config);
            $mysql->setLastUsedAt(time());
            return $mysql;
        }
        return false;
    }

    protected function rebuild(CoMySqlClient $mysql)
    {
        $mysql->connect($this->config);
        $mysql->setLastUsedAt(time());
        return $mysql;
    }

    protected function destroy(CoMySqlClient $mysql)
    {
        if (!$this->isEmpty()) {
            $this->decrease();
            return true;
        }
        return false;
    }

    public function push(CoMySqlClient $mysql)
    {
        if (!$this->channel->isFull()) {
            $this->channel->push($mysql);
            return;
        }
    }

    public function pop()
    {
        if ($mysql = $this->build()) {
            return $mysql;
        }
        $mysql = $this->channel->pop();
        $now   = time();
        if (!$mysql->isConnected()) {
            return $this->rebuild($mysql);
        }
        $mysql->setLastUsedAt($now);
        return $mysql;
    }

    public function autoRecycling($timeout = 120, $sleep = 20)
    {
        if (!$this->is_recycling) {
            $this->is_recycling = true;
            while (1) {
                Co::sleep($sleep);
                if ($this->shouldRecover()) {
                    $mysql = $this->channel->pop();
                    $now   = time();
                    if ($now - $mysql->getLastUsedAt() > $timeout) {
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($mysql);
                    }
                }
            }
        }
    }

    public function getLastInsertId()
    {
        return $this->last_insert_id;
    }

    public function setLastInsertId($id)
    {
        $this->last_insert_id = $id;
    }

    public function getNumber()
    {
        return $this->number;
    }

}
