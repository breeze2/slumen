## 服务日志

### Slumen运行日志

> Swoole HTTP服务器进程可以在当前终端运行，运行日志直接输出在当前终端；也可以转入后台作为守护进程运行，运行日志输出到指定文件，详情参见Swoole文档[daemonize](https://wiki.swoole.com/wiki/page/278.html)。

**Slumen**默认在后台运行，日志输出到Lumen项目中`storage/logs/slumen.log`文件，可以在`.env`文件中设置：

```env
SLUMEN_DAEMONIZE=true # 是否以后台守护进程方式运行
SLUMEN_LOG_FILE=/PATH/TO/PROJECT/storage/logs/slumen.log # 日志文件
SLUMEN_LOG_LEVEL=0 # 日志级别（0:DEBUG; 1:TRACE; 2:INFO; 3:NOTICE; 4:WARNING; 5:ERROR）
```

### HTTP访问日志

> Slumen 1.0.3起后，不在记录HTTP访问日志；如有需要，请用NginX代理记录。

默认情况下，**Slumen**不会记录HTTP请求信息，特别是前端还有 **NginX**代理时，可以直接使用 **NginX**的日志功能。

~~当然，**Slumen**自身也有完善的HTTP访问日志记录功能，可以在`.env`文件中设置：~~

```env
SLUMEN_HTTP_LOG_PATH=/PATH/TO/PROJECT/storage/logs/http # 日志文件的保存路径
SLUMEN_HTTP_LOG_SINGLE=true # 是否以单文件形式记录
```

~~并且，在启动文件`bootstrap/slumen.php`中，向服务容器注册日志服务提供者`BL\Slumen\Provider\HttpLoggerServiceProvider`：~~

```php
<?php
// in bootstrap/slumen.php
$app->register(BL\Slumen\Provider\HttpLoggerServiceProvider::class);
```
~~如有需要可以改写日志服务提供者，定制日志格式。~~

~~另外，若是日志文件的保存路径留空，则不会记录HTTP访问日志。若是以单文件形式记录，日志会记录在指定的保存路径下`access.log`文件中。~~

> Swoole HTTP服务器会有多个worker。多个worker进程写入同一个文件，高并发时会产生严重的IO阻塞，建议不同worker进程写入不同文件，Slumen默认以多文件形式记录HTTP访问日志。

~~HTTP访问日志格式：~~

```json
{
    "REQUEST_METHOD": "GET",
    "REQUEST_URI": "\/",
    "PATH_INFO": "\/",
    "REQUEST_TIME": 1532688286,
    "REQUEST_TIME_FLOAT": 1532688287.424407,
    "SERVER_PORT": 9080,
    "REMOTE_PORT": 55207,
    "REMOTE_ADDR": "127.0.0.1",
    "MASTER_TIME": 1532688286,
    "SERVER_PROTOCOL": "HTTP\/1.0",
    "SERVER_SOFTWARE": "swoole-http-server",
    "CONTENT_SIZE": 0,
    "REMOTE_USER": "",
    "HTTP_USER_AGENT": "ApacheBench\/2.3",
    "HTTP_REFERER": "",
    "HTTP_X_FORWARDED_FOR": "",
    "STATUS": 200,
    "BODY_BYTES_SENT": 1,
    "RESPONSE_TIME_FLOAT": 1532688287.436749
}
```
