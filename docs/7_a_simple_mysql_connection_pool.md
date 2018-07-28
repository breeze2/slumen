## \*一个简单的MySQL连接池
利用Swoole Coroutine可以轻松实现一个简单的数据库连接池。

### 实现代码

```php
<?php
// mysql_connection_pool.php
use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as CoChannel;
use Swoole\Coroutine\MySQL as CoMySQL;
use Swoole\Http\Server as HttpServer;

class MySqlCoroutine extends CoMySQL
{
    protected $used_at = null;
    public function getUsedAt()
    {
        return $this->used_at;
    }
    public function setUsedAt($time)
    {
        $this->used_at = $time;
    }
    public function isConnected()
    {
        return $this->connected;
    }
}

class MySqlManager
{
    protected $max_number;
    protected $min_number;
    protected $config;
    protected $channel;
    protected $numbser;
    private $is_recycling = false;

    /**
     * [__construct 构造函数]
     * @param array   $config         [MySQL服务器信息]
     * @param integer $max_number     [最大连接数]
     * @param integer $min_number     [最小连接数]
     */
    public function __construct(array $config, $max_number = 150, $min_number = 50)
    {
        $this->max_number     = $max_number;
        $this->min_number     = $min_number;
        $this->config         = $config;
        $this->channel        = new CoChannel($max_number);
        $this->numbser        = 0;
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
            printf("%d do %s\n", time(), 'build one');
            $this->increase();
            $mysql = new MySqlCoroutine();
            $mysql->connect($this->config);
            $mysql->setUsedAt(time());
            return $mysql;
        }
        return false;
    }

    protected function rebuild(MySqlCoroutine $mysql)
    {
        printf("%d do %s\n", time(), 'rebuild one');
        $mysql->connect($this->config);
        $mysql->setUsedAt(time());
        return $mysql;
    }

    protected function destroy(MySqlCoroutine $mysql)
    {
        if (!$this->isEmpty()) {
            printf("%d do %s\n", time(), 'destroy one');
            $this->decrease();
            return true;
        }
        return false;
    }

    public function push(MySqlCoroutine $mysql)
    {
        if (!$this->channel->isFull()) {
            printf("%d do %s\n", time(), 'push one');
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
        printf("%d do %s\n", time(), 'pop one');
        if (!$mysql->isConnected()) {
            return $this->rebuild($mysql);
        }
        $mysql->setUsedAt($now);
        return $mysql;
    }

    /**
     * [autoRecycling 自动回收连接]
     * @param  integer $timeout [连接空置时限]
     * @param  integer $sleep   [循环检查的时间间隔]
     * @return null             [null]
     */
    public function autoRecycling($timeout = 200, $sleep = 20)
    {
        if (!$this->is_recycling) {
            $this->is_recycling = true;
            while (1) {
                Co::sleep($sleep);
                if ($this->shouldRecover()) {
                    $mysql = $this->channel->pop();
                    $now   = time();
                    if ($now - $mysql->getUsedAt() > $timeout) {
                        printf("%d do %s\n", time(), 'recover one');
                        $this->decrease();
                    } else {
                        !$this->channel->isFull() && $this->channel->push($mysql);
                    }
                }
            }
        }
    }

}

$server = new HttpServer('127.0.0.1', 9501, SWOOLE_BASE);

$server->set([
    'worker_num' => 1,

]);

$manager = new MySqlManager([
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'user'     => 'root',
    'password' => '',
    'database' => 'test1',
    'timeout'  => -1,

], 4, 2);

$server->on('workerStart', function ($server) use ($manager) {
    $manager->autoRecycling(4, 2); // 启动自动回收
});

$server->on('request', function ($request, $response) use ($server, $manager) {
    $mysql = $manager->pop(); // 取出一个MySQL连接
    $mysql->query('select sleep(1)');
    $manager->push($mysql); // 返回一个MySQL连接
    $response->end(json_encode($server->stats()));

});

$server->start();

```

### 运行效果
运行脚本：
```bash
$ php mysql_connection_pool.php
```

ab测试（耗时2秒）：
```bash
$ ab -c 8 -n 8 http://127.0.0.1:9501/
```

间隔10秒后，再次测试（耗时2秒）：
```bash
$ ab -c 8 -n 8 http://127.0.0.1:9501/
```

输出结果：
```bash
1532083141 do build one
1532083141 do build one
1532083141 do build one
1532083141 do build one
1532083142 do push one
1532083142 do push one
1532083142 do push one
1532083142 do pop one
1532083142 do pop one
1532083142 do pop one
1532083142 do push one
1532083142 do pop one
1532083143 do push one
1532083143 do push one
1532083143 do push one
1532083143 do push one
1532083147 do recover one
1532083149 do recover one
1532083160 do build one
1532083160 do build one
1532083160 do pop one
1532083160 do rebuild one
1532083160 do pop one
1532083160 do rebuild one
1532083161 do push one
1532083161 do pop one
1532083161 do push one
1532083161 do push one
1532083161 do push one
1532083161 do pop one
1532083161 do pop one
1532083161 do pop one
1532083162 do push one
1532083162 do push one
1532083162 do push one
1532083162 do push one
1532083166 do recover one
1532083168 do recover one
```

