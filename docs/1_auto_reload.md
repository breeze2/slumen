## 热更新

使用[inotify](https://pecl.php.net/package/inotify)监视项目下所有PHP文件，文件更新时自动重载Swoole服务器。

### 安装inotify

```cmd
$ pecl install inotify
```

### 启动监视

```cmd
$ cd /PATH/TO/LUMEN/PROJECT
$ vendor/bin/slumen auto-reload
```