## 运行时协程

> 在Swoole4.1.0版本中，底层增加一个新的特性，可以在运行时动态将基于php_stream实现的扩展、PHP网络客户端代码一键协程化。底层替换了ZendVM Stream的函数指针，所有使用php_stream进行socket操作均变成协程调度的异步IO。

Swoole自[4.1.0](https://wiki.swoole.com/wiki/page/p-4.1.0.html)版本开始，可使用`Swoole\Runtime::enableCorotuine()`将基于php_stream实现的同步阻塞IO操作，变为协程调度的非阻塞IO操作。收到影响的原生PHP函数或PHP扩展有：

* redis
* pdo_mysql
* mysqli
* soap
* file_get_contents
* fopen
* fsockopen
* stream_socket_client
* stream_socket_server

### 优与劣

开启运行时协程后，遇上（基于php_stream）耗时的IO操作时，进程不用再空等，可以继续执行其他操作。基本原理是空间换时间，将当前遇上IO阻塞的运行时上下文存储起来，执行其他操作；等到阻塞结束后再将其恢复，然后往下执行本来操作。所以，在大量并发执行的时候要考虑内存消耗剧增的问题。

因为传统的PHP运行风格时同步阻塞型，所以大多数的PHP应用框架中，数据库连接都是全局单例的，这样可以一次建立多次重复使用。

不过在开启运行时协程后，全局单例的数据库连接就没法重复使用了，多个协程同时使用一个数据连接，必然会出现冲突，Swoole会抛出[一个错误](https://wiki.swoole.com/wiki/page/963.html)：

```
XXXX client has already been bound to another coroutine
```

当然，每个协程各自建立数据库连接自然不会出现冲突，不过，在高并发的情况下，有成千上万的连接涌向数据库服务器，这并不是数据库服务器所能承受的。

在全局单例数据库连接与各自建立数据库连接这两个极端之间，建立一个合理数量的数据库连接池，才是最佳选择。


