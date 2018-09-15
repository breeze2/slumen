## 配置

### `.env`文件配置
可以在Lumen项目`.env`文件中配置Slumen和Swoole HTTP Server的运行信息。

### Slumen参数选项

```env
# SLUMEN
SLUMEN_RUNNING_MODE=SWOOLE_BASE           # Swoole服务器的运行模式，默认SWOOLE_PROCESS
SLUMEN_SOCKET_TYPE=SWOOLE_SOCK_TCP        # Swoole服务器的Socket类型，默认SWOOLE_SOCK_TCP

SLUMEN_HOST=127.0.0.1                     # Swoole服务器IP地址，默认127.0.0.1
SLUMEN_PORT=9080                          # Swoole服务器监听端口，默认9080

SLUMEN_STATIC_RESOURCES=false             # 是否可以访问public目录下静态资源，默认false，若设为true，请删除public目录下敏感文件
SLUMEN_STATS_URI=/slumen-stats            # Swoole服务器统计信息的uri，默认/slumen-stats，留空则不可访问

SLUMEN_MAX_REQUEST=1024                   # 每个Worker最多处理请求数，之后销毁重生，可以避免内存泄漏，留空则不限制Worker最多处理请求数，默认留空

SLUMEN_ENABLE_RUNTIME_COROUTINE=true      # 开启运行时协程，默认不开启
```

### Swoole服务器参数选项
请参考[Swoole Server配置选项](https://wiki.swoole.com/wiki/page/274.html)，以下参数改为大写，加上前缀`SLUMEN_`，在项目`.env`设置即可：

```plain
reactor_num
worker_num                               # 默认1
max_request
max_conn                                 # 默认1024
task_worker_num
task_ipc_mode
task_max_request
task_tmpdir
dispatch_mode
dispatch_func
message_queue_key
daemonize                                # 默认true
backlog
log_file                                 # 填写绝对路径，默认当前项目下storage/logs/slumen.log
log_level
heartbeat_check_interval
heartbeat_idle_time
open_eof_check
open_eof_split
package_eof
open_length_check
package_length_type
package_length_func
package_max_length
open_cpu_affinity
cpu_affinity_ignore
open_tcp_nodelay
tcp_defer_accept
ssl_cert_file
ssl_method
ssl_ciphers
user
group
chroot
pid_file
pipe_buffer_size
buffer_output_size
socket_buffer_size
enable_unsafe_event
discard_timeout_request
enable_reuse_port
enable_delay_receive
open_http_protocol
open_http2_protocol
open_websocket_protocol
open_mqtt_protocol
reload_async
tcp_fastopen
request_slowlog_file
enable_coroutine
max_coroutine

upload_tmp_dir
http_parse_post
document_root
http_compression
```


### `config`文件配置

在Lumen项目的`config`目录下新建`slumen.php`文件，完整内容如下（只需用到的键值即可，其余是默认值）：
```php
<?php

return [

    'running_mode'     => constant(env('SLUMEN_RUNNING_MODE', 'SWOOLE_PROCESS')),
    'socket_type'      => constant(env('SLUMEN_SOCKET_TYPE', 'SWOOLE_SOCK_TCP')),

    'host'             => env('SLUMEN_HOST', '127.0.0.1'),
    'port'             => env('SLUMEN_PORT', 9080),
    'static_resources' => env('SLUMEN_STATIC_RESOURCES', false),
    'stats_uri'        => env('SLUMEN_STATS_URI', '/slumen-stats'),

    'db_pool'          => [
        'max_connection'    => env('SLUMEN_DB_POOL_MAX_CONNECTION', env('SLUMEN_DB_POOL_MAX_CONN', 120)),
        'min_connection'    => env('SLUMEN_DB_POOL_MIN_CONNECTION', env('SLUMEN_DB_POOL_MIN_CONN', 0)),
        'wait_timeout'      => env('SLUMEN_DB_POOL_WAIT_TIMEOUT', 120),
        'checking_interval' => env('SLUMEN_DB_POOL_CHECKING_INTERVAL', 20),
    ],

    /*
    Swoole Http Server
     */
    'swoole_server'    => [
        'worker_num'               => env('SLUMEN_WORKER_NUM', 1),
        'max_request'              => env('SLUMEN_MAX_REQUEST', 0),
        'daemonize'                => env('SLUMEN_DAEMONIZE', true),
        'log_file'                 => env('SLUMEN_LOG_FILE', storage_path('logs/slumen.log')),
        'max_conn'                 => env('SLUMEN_MAX_CONN', 1024),
        'reactor_num'              => env('SLUMEN_REACTOR_NUM'),
        'task_worker_num'          => env('SLUMEN_TASK_WORKER_NUM'),
        'task_ipc_mode'            => env('SLUMEN_TASK_IPC_MODE'),
        'task_max_request'         => env('SLUMEN_TASK_MAX_REQUEST'),
        'task_tmpdir'              => env('SLUMEN_TASK_TMPDIR'),
        'dispatch_mode'            => env('SLUMEN_DISPATCH_MODE'),
        'dispatch_func'            => env('SLUMEN_DISPATCH_FUNC'),
        'message_queue_key'        => env('SLUMEN_MESSAGE_QUEUE_KEY'),
        'backlog'                  => env('SLUMEN_BACKLOG'),
        'log_level'                => env('SLUMEN_LOG_LEVEL'),
        'heartbeat_check_interval' => env('SLUMEN_HEARTBEAT_CHECK_INTERVAL'),
        'heartbeat_idle_time'      => env('SLUMEN_HEARTBEAT_IDLE_TIME'),
        'open_eof_check'           => env('SLUMEN_OPEN_EOF_CHECK'),
        'open_eof_split'           => env('SLUMEN_OPEN_EOF_SPLIT'),
        'package_eof'              => env('SLUMEN_PACKAGE_EOF'),
        'open_length_check'        => env('SLUMEN_OPEN_LENGTH_CHECK'),
        'package_length_type'      => env('SLUMEN_PACKAGE_LENGTH_TYPE'),
        'package_length_func'      => env('SLUMEN_PACKAGE_LENGTH_FUNC'),
        'package_max_length'       => env('SLUMEN_PACKAGE_MAX_LENGTH'),
        'open_cpu_affinity'        => env('SLUMEN_OPEN_CPU_AFFINITY'),
        'cpu_affinity_ignore'      => env('SLUMEN_CPU_AFFINITY_IGNORE'),
        'open_tcp_nodelay'         => env('SLUMEN_OPEN_TCP_NODELAY'),
        'tcp_defer_accept'         => env('SLUMEN_TCP_DEFER_ACCEPT'),
        'ssl_cert_file'            => env('SLUMEN_SSL_CERT_FILE'),
        'ssl_method'               => env('SLUMEN_SSL_METHOD'),
        'ssl_ciphers'              => env('SLUMEN_SSL_CIPHERS'),
        'user'                     => env('SLUMEN_USER'),
        'group'                    => env('SLUMEN_GROUP'),
        'chroot'                   => env('SLUMEN_CHROOT'),
        'pid_file'                 => env('SLUMEN_PID_FILE'),
        'pipe_buffer_size'         => env('SLUMEN_PIPE_BUFFER_SIZE'),
        'buffer_output_size'       => env('SLUMEN_BUFFER_OUTPUT_SIZE'),
        'socket_buffer_size'       => env('SLUMEN_SOCKET_BUFFER_SIZE'),
        'enable_unsafe_event'      => env('SLUMEN_ENABLE_UNSAFE_EVENT'),
        'discard_timeout_request'  => env('SLUMEN_DISCARD_TIMEOUT_REQUEST'),
        'enable_reuse_port'        => env('SLUMEN_ENABLE_REUSE_PORT'),
        'enable_delay_receive'     => env('SLUMEN_ENABLE_DELAY_RECEIVE'),
        'open_http_protocol'       => env('SLUMEN_OPEN_HTTP_PROTOCOL'),
        'open_http2_protocol'      => env('SLUMEN_OPEN_HTTP2_PROTOCOL'),
        'open_websocket_protocol'  => env('SLUMEN_OPEN_WEBSOCKET_PROTOCOL'),
        'open_mqtt_protocol'       => env('SLUMEN_OPEN_MQTT_PROTOCOL'),
        'reload_async'             => env('SLUMEN_RELOAD_ASYNC'),
        'tcp_fastopen'             => env('SLUMEN_TCP_FASTOPEN'),
        'request_slowlog_file'     => env('SLUMEN_REQUEST_SLOWLOG_FILE'),
        'enable_coroutine'         => env('SLUMEN_ENABLE_COROUTINE'),
        'max_coroutine'            => env('SLUMEN_MAX_COROUTINE'),

        'upload_tmp_dir'           => env('SLUMEN_UPLOAD_TMP_DIR'),
        'http_parse_post'          => env('SLUMEN_HTTP_PARSE_POST'),
        'document_root'            => env('SLUMEN_DOCUMENT_ROOT'),
        'http_compression'         => env('SLUMEN_HTTP_COMPRESSION'),
    ],
];

```

然后，在`bootstrap/slumen.php`中引入配置即可：

```php
<?php
...
    $app->configure('slumen');
```
