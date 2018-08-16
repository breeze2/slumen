## \*MySQL连接池

> 使用MySQL连接池功能，需将swoole扩展升级至4.0以上。

### 服务注入

在启动文件`bootstrap/slumen.php`中，向服务容器注册MySQL连接池服务提供者`BL\Slumen\Providers\MySqlPoolServiceProvider`：

```php
<?php
// in bootstrap/slumen.php
$app->register(BL\Slumen\Providers\MySqlPoolServiceProvider::class);
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
    'db_pool'          => [
        'max_connection'    => env('SLUMEN_DB_POOL_MAX_CONNECTION') ?: env('SLUMEN_DB_POOL_MAX_CONN', 100),
    ],
];
```

### 使用方法

服务注册后，`app('SlumenMySqlPool')`会返回一个MySQL协程连接实例，使用方法与`app('db')`一致，例如：

```php
<?php
$results = app('SlumenMySqlPool')->table('user')->where('id', 1)->get();
```
注意：对于原生SQL的参数绑定，绑定的参数数组必须为数字索引的数组，参数的顺序对应SQL语句中`?`的出现顺序。

`app('SlumenMySqlPool')`与`app('db')`不会冲突，代码中既可以使用`app('db')`也可以使用`app('SlumenMySqlPool')`。
`app('SlumenMySqlPool')`返回的是Swoole提供的MySQL协程连接，高并发时效率会比PDO连接的要好；不过，复杂的数据库读写操作（如事务，读写分离等），请使用`app('db')`，简单的一次性数据库操作才考虑使用`app('SlumenMySqlPool')`，因为每次从连接池中取出的都是不同的连接。

建议阅读[非阻塞协程](/7_non_blocking_coroutine)和[一个简单的MySQL连接池](/7_a_simple_mysql_connection_pool)