<?php

return array(


    'running_mode'     => constant(env('SLUMEN_RUNNING_MODE', 'SWOOLE_PROCESS')),
    'socket_type'      => constant(env('SLUMEN_SOCKET_TYPE', 'SWOOLE_SOCK_TCP')),

    'host'             => env('SLUMEN_HOST', '127.0.0.1'),
    'port'             => env('SLUMEN_PORT', 9080),
    'gzip'             => env('SLUMEN_GZIP', 1),
    'gzip_min_length'  => env('SLUMEN_GZIP_MIN_LENGTH', 1024),
    'static_resources' => env('SLUMEN_STATIC_RESOURCES', false),
    'pid_file'         => env('SLUMEN_PID_FILE', storage_path('app/slumen.pid')),
    'stats_uri'        => env('SLUMEN_STATS_URI', '/slumen-stats'),
    'http_log_path'    => env('SLUMEN_HTTP_LOG_PATH', false),
    'http_log_single'  => env('SLUMEN_HTTP_LOG_SINGLE', false),

    'root_dir'         => base_path(),
    'public_dir'       => base_path('public'),

    'service_hook'     => env('SLUMEN_SERVICE_HOOK'),

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
        'ssl_ciphers'              => env('SLUMEN_SSL_CIPHERS'),
        'enable_delay_receive'     => env('SLUMEN_ENABLE_DELAY_RECEIVE'),
        'open_http_protocol'       => env('SLUMEN_OPEN_HTTP_PROTOCOL'),
        'open_http2_protocol'      => env('SLUMEN_OPEN_HTTP2_PROTOCOL'),
        'open_websocket_protocol'  => env('SLUMEN_OPEN_WEBSOCKET_PROTOCOL'),
        'open_mqtt_protocol'       => env('SLUMEN_OPEN_MQTT_PROTOCOL'),
        'reload_async'             => env('SLUMEN_RELOAD_ASYNC'),
        'tcp_fastopen'             => env('SLUMEN_TCP_FASTOPEN'),
    ],
);
