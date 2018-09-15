## MySQL连接池

**Slumen**在[illuminate/database](https://github.com/illuminate/redis)的基础上封装了一个MySQL连接池。由于在一个MySQL连接上，可能有连续多个操作，底层代码无法施行连接池中自动取出连接、自动放回连接，使用者必须自行实现。

### 注册服务提供者

在`bootstrap/slumen.php`文件中，最后追加：
```php
<?php
...
//注册MySql连接池的服务提供者
$app->register(BL\Slumen\Database\MySqlServiceProvider::class);

```

### 使用

#### 对比`app('db')`

```php
<?php
//原lumen用法
$result = app('db')->table('users')->where('id', 1)->get();

//MySQL连接池用法
$connection = app('mysql')->pop();
$result = $connection->table('users')->where('id', 1)->get();
app('mysql')->push($connection);

```

#### 对比`Eloquent ORM`

```php
<?php
//原lumen用法
$result = App\Models\User::where('id', 1)->get();

//MySQL连接池用法
//注意，这里的App\Models\User须继承自BL\Slumen\Database\Eloquent\Model
$connection = app('mysql')->pop();
$result = App\Models\User::useConnection()->where('id', 1)->get();
app('mysql')->push($connection);

```

### 另外

MySQL连接池的默认容量是50，即一个Server Worker最多建立50个MySQL连接。如需调整，可以自行创建新的MySQL连接池服务提供者，如：

```php
<?php

namespace App\Providers;

use BL\Slumen\Database\MySqlServiceProvider as ServiceProvider;

class MySqlServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME_MYSQL, function ($app) {
            $config         = $app->make('config')->get('database.connections.mysql', []);
            $config['name'] = 'mysql';
            return new MySqlConnectionPool($config, 60);//60是连接池最大容量
        });
    }
}

```

如若需要读写分离的MySQL连接池，可以创建两个MySQL连接池服务提供者，一个只读，一个可读可写。