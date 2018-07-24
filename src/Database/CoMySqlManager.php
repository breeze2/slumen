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
    protected $numbser;
    private $is_recovering = false;

    public function __construct(array $config, $max_number = 150, $min_number = 50)
    {
        $this->max_number = $max_number;
        $this->min_number = $min_number;
        $this->config     = $config;
        $this->channel    = new CoChannel($max_number);
        $this->numbser    = 0;
    }

    private function isFull()
    {
        return $this->numbser === $this->max_number;
    }

    private function isEmpty()
    {
        return $this->numbser === 0;
    }

    private function shouldRecover()
    {
        return $this->numbser > $this->min_number;
    }

    private function increase()
    {
        return $this->numbser += 1;
    }

    private function decrease()
    {
        return $this->numbser -= 1;
    }

    protected function build()
    {
        if (!$this->isFull()) {
            $this->increase();
            $mysql = new CoMySqlClient();
            $mysql->connect($this->config);
            $mysql->setUsedAt(time());
            return $mysql;
        }
        return false;
    }

    protected function rebuild(CoMySqlClient $mysql)
    {
        $mysql->connect($this->config);
        $mysql->setUsedAt(time());
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
        $mysql->setUsedAt($now);
        return $mysql;
    }

    public function autoRecover($timeout = 200, $sleep = 20)
    {
        if (!$this->is_recovering) {
            $this->is_recovering = true;
            while (1) {
                Co::sleep($sleep);
                if ($this->shouldRecover()) {
                    $mysql = $this->channel->pop();
                    $now   = time();
                    if ($now - $mysql->getUsedAt() > $timeout) {
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($mysql);
                    }
                }
            }
        }
    }

}
