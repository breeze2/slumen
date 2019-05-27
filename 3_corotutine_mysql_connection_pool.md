## \*MySQL协程客户端连接池

> 使用MySQL连接池功能，需将swoole扩展升级至4.0以上。实现原理是用[Coroutine\MySQL](https://wiki.swoole.com/wiki/page/p-coroutine_mysql.html)替代[PDO_MYSQL](http://php.net/manual/zh/ref.pdo-mysql.php)

### 服务注入

在启动文件`bootstrap/slumen.php`中，向服务容器注册MySQL连接池服务提供者`BL\Slumen\Providers\CoroutineMySqlPoolServiceProvider`：

```php
<?php
// in bootstrap/slumen.php
$app->register(BL\Slumen\Providers\CoroutineMySqlPoolServiceProvider::class);
```

### 连接配置

连接配置，取自Lumen本身的MySQL配置。

另外可以配置连接池的最大连接数，默认120（不是越大越好，建议100以下）：

.env配置：

```env
SLUMEN_DB_POOL_MAX_CONNECTION = 100
```

或者slumen.php配置：

```php
<?php
return [
    'db_pool' => [
        'max_connection' => env('SLUMEN_DB_POOL_MAX_CONNECTION') ?: env('SLUMEN_DB_POOL_MAX_CONN', 100),
    ],
];
```

### 使用方法

服务注册后，`app('co-mysql-pool')`会返回一个MySQL协程客户端连接池实例，使用方法如下：

```php
<?php
$c = app('co-mysql-pool')->pop();
try {
    $user = $c->table('users')->where('id', 1)->first();
} finally {
    app('co-mysql-pool')->push($c);
}
```

`pop`方法返回一个MySQL连接实例，MySQL连接实例的使用方法跟`app('db')`基本一致。

注意：
* MySQL连接实例用完必须放回连接池，不然越用越少；
* 对于原生SQL的参数绑定，绑定的参数数组必须为数字索引的数组，参数的顺序对应SQL语句中`?`的出现顺序。

`app('co-mysql-pool')`与`app('db')`不会冲突，代码中既可以使用`app('db')`也可以使用`app('co-mysql-pool')`。
从`app('co-mysql-pool')`连接池取出的是非阻塞的MySQL协程客户端连接，高并发时效率会比同步阻塞的PDO连接要好。

> 可以参考`BL\Slumen\Providers\CoroutineMySqlPoolServiceProvider`，自行实现MySQL协程客户端只读连接池、MySQL协程客户端读写连接池。

建议阅读[非阻塞协程](/7_non_blocking_coroutine)和[一个简单的MySQL连接池](/7_a_simple_mysql_connection_pool)