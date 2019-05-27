## 服务日志

### Slumen运行日志

> Swoole HTTP服务器进程可以在当前终端运行，运行日志直接输出在当前终端；也可以转入后台作为守护进程运行，运行日志输出到指定文件，详情参见Swoole文档[daemonize](https://wiki.swoole.com/wiki/page/278.html)。

**Slumen**默认在后台运行，日志输出到Lumen项目中`storage/logs/slumen.log`文件，可以在`.env`文件中设置：

```env
SLUMEN_DAEMONIZE=true # 是否以后台守护进程方式运行
SLUMEN_LOG_FILE=/PATH/TO/PROJECT/storage/logs/slumen.log # 日志文件
SLUMEN_LOG_LEVEL=0 # 日志级别（0:DEBUG; 1:TRACE; 2:INFO; 3:NOTICE; 4:WARNING; 5:ERROR）
```

### HTTP访问日志

> Slumen 1.0.3起后，不在记录HTTP访问日志；如有需要，请用NginX代理记录。
