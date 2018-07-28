## \*非阻塞协程

> Swoole在2.0开始内置协程(Coroutine)的能力，提供了具备协程能力IO接口。<br>
> 单个协程在遇上IO阻塞时，整个进程不会被CPU挂起，可以继续执行其他没阻塞的协程。<br>
> Swoole是在底层封装了协程，对比传统的php层协程框架，开发者不需要使用yield关键词来标识一个协程IO操作，所以不再需要对yield的语义进行深入理解以及对每一级的调用都修改为yield，这极大的提高了开发效率。

更多关于Swoole协程信息，请查看Swoole文档[Coroutine](https://wiki.swoole.com/wiki/page/749.html)。


### MySQL客户端协程

在**Slumen**中使用MySQL客户端协程：

```php
<?php
// in /routes/web.php

$router->get('/test', function () use ($router) {
    $swoole_mysql = new Swoole\Coroutine\MySQL();
    $swoole_mysql->connect([
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'test_db',
        'timeout' => -1,
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    return 1;
});

```

`/test`请求的处理时间至少需要1秒，用`ab`工具测试：

```bash
$ vendor/bin/slumen restart
$ ab -c 100 -n 100 http://127.0.0.1/test
...
Concurrency Level:      10
Time taken for tests:   1.093 seconds
Complete requests:      10
Failed requests:        0
Total transferred:      1820 bytes
HTML transferred:       10 bytes
...
```

1Worker的Swoole HTTP服务器在处理100并发的`/test`请求，总耗时依然是1秒多，因为每个请求都派生了一个MySQL客户端协程，互相没有阻塞，这是一般LNMP架构难以做到的。

### 有数量限制的MySQL客户端协程

当然，MySQL客户端协程不是越多越好，因为MySQL客户端连接太多，会造成MySQL服务器压力剧增，所以MySQL客户端协程需要维持在一定数量下。
利用`Swoole\Coroutine\Channel`可以轻松实现，限制最多150个MySQL客户端协程：

```php
<?php
// in /routes/web.php

$chan = new Swoole\Coroutine\Channel(150);
$router->get('/test', function () use ($router, $chan) {
    $chan->push(1);
    $swoole_mysql = new Swoole\Coroutine\MySQL();
    $swoole_mysql->connect([
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'test_db',
        'timeout' => -1,
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    $chan->pop();
    return 1;
});

```
