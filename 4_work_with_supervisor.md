# 与Supervisor
> 在正式环境中，使用Slumen时，最好用[Supervisor](http://supervisord.org/)，守护Swoole HTTP服务器，而不是简单的`vendor/bin/slumen start`。

以ubuntu系统为例。

### 最大文件打开数
首先要调整系统的最大文件打开数，编辑`/etc/security/limits.conf`，追加：
```conf
* soft nofile 102400
* hard nofile 204800
```
重启系统，可以用`ulimit -n`命令查看是否修改成功。

### 安装Supervisor
```cmd
$ sudo apt install supervisor
```

### 配置
假设Lumen项目路径是`/PATH/TO/LUMEN_PROJECT`，关闭Swoole HTTP服务器自带的进程守护——编辑`.env`，添加：
```env
SWOOLE_HTTP_DAEMONIZE=0
```

新建`/etc/supervisor/conf.d/slumen.conf`，添加：
```conf
[program:slumen]
user=www-data
directory=/PATH/TO/LUMEN_PROJECT/
command=/PATH/TO/LUMEN_PROJECT/vendor/bin/slumen start

numprocs=1
autostart=true
autorestart=true

stdout_logfile=/PATH/TO/LUMEN_PROJECT/storage/logs/slumen.log
```

注意：

* `numprocs`的值只能是1！
* 执行用户必须对项目路径有对应的读写权限。

保存文件后执行命令：
```cmd
$ sudo supervisorctl reread
$ sudo supervisorctl update
```

### 常用命令
```cmd
$ sudo supervisorctl status                     # 查看supervisor状态
$ sudo supervisorctl stop slumen:    # 关闭slumen
$ sudo supervisorctl start slumen:   # 启动slumen
$ sudo supervisorctl restart slumen: # 重启slumen
```