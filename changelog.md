## 更新日志

### v1.3.0
_2019/05/27_

#### 💥Breaking changes
* new coroutine client based mysql connection pool
* new runtime coroutine based mysql connectoin pool
* new runtime coroutine based redis connectoin pool

### v1.2.1
_2018/12/15_

#### 🐛Fixes
* response status code

### v1.2.0
_2018/09/15_

#### ✨Improvements
* Be Compatible with Swoole4.1.0
* MySQL Connection Pool Service Provider
* Redis Connection Pool Service Provider
* Adjust Code of MySQL Coroutine Client Pool

### v1.0.3
_2018/08/16_

#### ✨Improvements
* Adjust `HttpEventSubscriberServiceProvider.php`
* Adjust `Command.php` & `Service.php`
* Suit phpstan

#### 🐛Fixes
* `slumen reload` not working
* `Class SlumenHttpEventSubscriber does not exist`
* MySQL pool client number unlimit

#### 💥Breaking changes
* remove `HttpLoggerServiceProvider`

### v1.0.0
_2018/07/27_

#### ✨Improvements

* Http Access Info Logger Service Provider
* Swoole Http Server Event Subscriber Service Provider
* Swoole Coroutine MySQL Client Service Provider
* MySQL Connection Pool

### v0.8.0
_2018/07/20_

* Optimize Service Hook
* Support Service Container

### v0.6.0
_2018/06/15_

* Optimize Server Log
* Support Service Hook

### v0.2.0
_2018/06/08_

* Swoole Http Server

### init
_2018/06/03_

* Derived from [lumen-swoole-http](https://github.com/breeze2/lumen-swoole-http)
