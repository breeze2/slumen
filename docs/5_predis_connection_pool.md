## Redis连接池

**Slumen**在[illuminate/redis](https://github.com/illuminate/redis)的基础上封装了一个Redis连接池，调用方法与原本无异。

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
$app->register(BL\Slumen\Redis\RedisServiceProvider::class);

```

### 使用

使用方法与Laravel的[Redis](https://laravel.com/docs/5.5/redis)部分文档介绍一样，在连接池中取出连接、放回连接等操作，**Slumen**底层代码实现，使用者无需关心。

### 另外

Redis连接池的默认容量是50，即一个Server Worker最多建立50个Redis连接。如需调整，可以自行创建新的Redis连接池服务提供者，如：

```php
<?php

namespace App\Providers;

use BL\Slumen\Redis\RedisServiceProvider as ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME_REDIS, function ($app) {
            $config = $app->make('config')->get('database.redis', []);

            $reflection = new ReflectionMethod(RedisManager::class, '__construct');

            $manager = null;
            if ($reflection->getNumberOfParameters() === 3) {//兼容illuminate/redis 5.7版本
                $manager = new RedisManager($app, Arr::pull($config, 'client', 'predis'), $config);
            } else {
                $manager = new RedisManager(Arr::pull($config, 'client', 'predis'), $config);
            }
            $manager->setConnectionPoolMaker(function () {
                return new RedisPool(60);//60是连接池最大容量
            });
            return $manager;
        });

        $this->app->bind(self::PROVIDER_NAME_REDIS_CONNECTION, function ($app) {
            return $app[self::PROVIDER_NAME_REDIS]->connection();
        });
    }
}

```

