## Redis连接池

**Slumen**在[illuminate/redis](https://github.com/illuminate/redis)的基础上封装了一个Redis连接池。

### 配置

在项目的`config`目录下（没有则新建），新建一个`database.php`文件，内容大致如下：

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
            'options' => [
            ],
        ],
    ],
    'migrations' => 'migrations',

    'redis' => [
        'client' => 'predis',
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
    ],
];

```
主要是补充redis连接的默认配置。

然后在`bootstrap/slumen.php`文件中，在`$app`变量声明后，插入代码以加载`config/database.php`的配置：

```php
<?php
...
$app = new BL\Slumen\Application(
    realpath(__DIR__ . '/../')
);
//加载`config/database.php`的配置
$app->configure('database');

```

### 注册服务提供者

在`bootstrap/slumen.php`文件中，最后追加：
```php
<?php
...
//注册Redis连接池的服务提供者
$app->register(BL\Slumen\Providers\RuntimeRedisPoolServiceProvider::class);

```

### 使用

在连接池中取出的Redis连接，使用方法与Laravel的[Redis](https://laravel.com/docs/5.5/redis)部分文档介绍一样。

例如：

```php
<?php

$c = app('rt-redis-pool')->pop();
try {
    $c->set('name', 'Taylor');
} finally {
    app('rt-redis-pool')->push($c);
}

```

### 另外

Redis连接池的默认容量是50，即一个Server Worker最多建立50个Redis连接。如需调整，可以参考`BL\Slumen\Providers\RuntimeRedisPoolServiceProvider`自行创建新的Redis连接池服务提供者。

