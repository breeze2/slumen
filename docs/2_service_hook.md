## 服务回调

Swoole服务器提供多个[事件回调函数](https://wiki.swoole.com/wiki/page/41.html)，**Slumen**将其中常用的几个回调接口开放给开发者，分别是：

1. `onServerStarted`，服务器启动后；
2. `onServerStopped`，服务器停止后；
3. `onWorkerStarted`，Worker启动后；
4. `onWorkerStopped`，Worker停止后；
5. `onServerRequested`，接收请求后；
6. `onServerResponded`，返回响应后；
7. `onAppError`，应用发生错误后；
8. ...

### 使用方法

先定义一个类，继承自`BL\Slumen\Http\EventSubscriber`，再定义公有方法，比如`onServerStarted`：

```php
<?php
// in app/Tools/SlumenEventSubscriber.php
namespace App\Tools;
use BL\Slumen\Http\EventSubscriber;

class SlumenEventSubscriber extends EventSubscriber
{
    public function onServerStarted($server)
    {
        var_dump($server);
    }
}
```

然后，在启动文件`bootstrap/slumen.php`中，将该类单里注入服务容器：

```php
<?php
// in bootstrap/slumen.php
$app->singleton(BL\Slumen\Provider\HttpEventSubscriberServiceProvider::PROVIDER_NAME, App\Tools\SlumenEventSubscriber::class);
```

最后重启Swoole服务器时，程序便会`var_dump`出`$server`变量的值。

其他回调函数也是如此定义，不过彼此参数并不相同。

### 参数说明

各个回调函数的传入参数，不尽相同，这里简单说明一下：

```php
<?php 
public function onServerStarted (swoole_http_server $server);
public function onServerStopped (swoole_http_server $server);
public function onWorkerStarted (swoole_http_server $server, int $worker_id);
public function onWorkerStopped (swoole_http_server $server, int $worker_id);
public function onRequested (swoole_http_request $request, swoole_http_response $response);
public function onResponded (swoole_http_request $request, swoole_http_response $response);
public function onAppError (Exception $error);
```

利用这些回调函数，开发者可以实现很多自需的功能，比如[XHGUI性能分析](/7_profiling_with_xhgui)
