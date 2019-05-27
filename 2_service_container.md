## 服务容器

**Slumen**中提供了一个`slumen`函数，功能与Lumen中的`app`函数一样，使用方法可以参考Lumen文档[Service Container](https://lumen.laravel.com/docs/container)。

Swoole Http Server使用的是异步编程方式，这里可以借助服务容器做载体，在不同代码块中传递非全局变量，例如：

* 绑定一个实例

    ```php
    <?php
    class A () {
        public $v = 1;
    }
    $a = new A();
    $a->v += 1;
    echo $a->v; // 2
    slumen()->instance('MyA', $a);
    ```

* 解析一个实例

    ```php
    <?php
    $a = slumen('MyA');
    echo $a->v; // 2
    ```

另外，**Slumen**提供的其他功能都是以服务提供者Service Provider的方式注入服务容器的，尽量降低代码耦合度。
