## \*XHGUI性能分析

### XHGUI
[XHGUI](https://github.com/perftools/xhgui)，是一个XHProf数据可视化工具，数据存储基于MongoDB。

### XHProf
[XHProf](http://pecl.php.net/package/xhprof)，是一个PHP性能跟踪工具。目前PHP7对应的版本是[tideways/php-xhprof-extension](https://github.com/tideways/php-xhprof-extension)。

### 在Slumen中使用XHGUI

#### 安装XHProf扩展
在当前环境（PHP7）安装XHProf扩展：
```cmd
$ git clone https://github.com/tideways/php-xhprof-extension.git
$ cd php-xhprof-extension
$ phpize
$ ./configure
$ make
$ sudo make install
```
在PHP配置中添加
```
extension=tideways_xhprof.so
```

#### 安装XHGUI数据收集器
在项目路径下，安装XHGUI数据收集器：
```cmd
$ cd /PATH/TO/PROJECT
$ composer require breeze2/slumen-xhgui-collector
```
注意：XHGUI数据收集器要求当前PHP安装[mongo](http://pecl.php.net/package/mongo)扩展（PHP7对应[mongodb](http://pecl.php.net/package/mongodb)）。

#### 配置xhgui.php
在项目路径下，添加`config/xhgui.php`文件，内容大概如下，主要是MongoDB的连接信息：
```php
<?php

return [
    'debug' => false,
    'mode' => 'development',

    'save.handler' => 'mongodb',
    'db.host' => 'mongodb://127.0.0.1:27017',
    'db.db' => 'xhprof',

    'db.options' => array(),
    'templates.path' => dirname(__DIR__) . '/src/templates',
    'date.format' => 'M jS H:i:s',
    'detail.count' => 6,
    'page.limit' => 25,

    'profiler.enable' => function() {
        return rand(1, 100) === 42;
    },
    'profiler.simple_url' => function($url) {
        return preg_replace('/\=\d+/', '', $url);
    },
    'profiler.options' => array(),
];
```

#### 设置服务回调

在接受请求后，开始收集分析数据；在返回响应后，结束收集分析数据：

```php
<?php
// in app/Tools/SlumenEventSubscriber.php
namespace App\Tools;
use BL\Slumen\Http\EventSubscriber;
use BL\Slumen\Xhgui\Collector as XhguiCollector;

class SlumenEventSubscriber extends EventSubscriber
{
    protected $xhguiCollector = null;
    public function __construct()
    {
        $this->xhguiCollector = new XhguiCollector(base_path('/config/xhgui.php'));
        parent::__construct();
    }

    public function onServerRequested($request, $response)
    {// 接受到请求后开启数据收集
        $this->xhguiCollector->setInfo($request->server['request_uri'], $request->get, $request->server);
        $this->xhguiCollector->collectorEnable();
    }

    public function onServerResponded($request, $response)
    {// 返回响应后关闭数据收集
        $this->xhguiCollector->collectorDisable();
    }
}
```

在启动文件`bootstrap/slumen.php`中，将该类单里注入服务容器：

```php
<?php
// in bootstrap/slumen.php
$app->singleton(BL\Slumen\Provider\HttpEventSubscriberServiceProvider::PROVIDER_NAME, App\Tools\SlumenEventSubscriber::class);

```

重新启动**Slumen**，每个请求处理的性能追踪数据都会保存到MongoDB中。

### 查看分析XHProf数据
下载XHGUI源码：
```cmd
$ git clone https://github.com/perftools/xhgui.git
```

修改配置文件（配置应该和上面的`xhgui.php`一样）：
```cmd
$ cd xhgui
$ cp config/config.default.php config/config.php
$ vi config/config.php
```

然后启动XHGUI服务，（浏览器访问`http://127.0.0.1:8000/`）便能查看MongoDB里的性能追踪数据：
```cmd
$ php -S 127.0.0.1:8000 -t webroot
```

查看分析`/`路由请求处理的火焰图：

![火焰图](/media/xhgui_flamegraph.png)

### 最后

没必要在正式运行环境中进行性能追踪分析，在本地或者测试机上进行即可。<br>
注意：使用Swoole异步协程时，不能使用XHProf收集性能分析数据。
