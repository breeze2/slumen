## 使用

> Slumen只是帮助Lumen项目，从`NginX & PHP-FPM`HTTP服务器，轻松迁移到更快的`Swoole`HTTP服务器，原Lumen框架下的项目代码不需要过多更改。<br>

### Lumen
[Lumen](https://lumen.laravel.com/)是一个轻便的微框架，具体使用说明请参考[文档](https://lumen.laravel.com/docs/)。

### Swoole
[Swoole](https://www.swoole.com/)是PHP异步网络编程的一个扩展，具体使用说明请参考[文档](https://wiki.swoole.com/)。

了解Lumen框架和Swoole扩展后，下面开始介绍如何通过**Slumen**将两者结合。

### Bootstrap文件
首先，引入**Slumen**自带的启动文件：

```bash
$ cd /PATH/TO/LUMEN/PROJECT
$ cp vendor/breeze2/slumen/bootstrap/slumen.php ./bootstrap/
```

> 注意：Lumen5.8版本应该执行`cp vendor/breeze2/slumen/bootstrap/slumen58.php ./bootstrap/`


`bootstrap/slumen.php`与Lumen的`bootstrap/app.php`唯一区别是：

```php
// bootstrap/slumen.php
$app = new BL\Slumen\Application(
    realpath(__DIR__.'/../')
);

// bootstrap/app.php
$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);
```

*(选)* 将`public/index.php`改为以下内容，则以后只需维护`bootstrap/slumen.php`文件就能使得项目能在NginX服务器与Swoole服务器间无修改切换：

```php
<?php
$app = require __DIR__.'/../bootstrap/slumen.php';
$app->run();
```

### 可用命令

```bash
$ cd /PATH/TO/LUMEN/PROJECT
$ vendor/bin/slumen start   # 启动Swoole HTTP服务器
$ vendor/bin/slumen stop    # 关闭Swoole HTTP服务器
$ vendor/bin/slumen restart # 重启Swoole HTTP服务器
$ vendor/bin/slumen reload  # 重载Swoole HTTP服务器
$ vendor/bin/slumen status  # 查看Swoole HTTP服务器状态
```

`vendor/bin/slumen auto-reload`命令见[热更新](/1_auto_reload)。
