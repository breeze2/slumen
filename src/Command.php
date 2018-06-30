<?php
namespace BL\Slumen;

class Command
{
    const VERSION                     = 'slumen 0.6.0';
    const CONFIG_PREFIX               = 'SLUMEN_';
    const DEFAULT_BOOTSTRAP_FILE_NAME = 'slumen.php';
    const CONFIG_KEY                  = 'slumen';

    protected $bootstrap;
    protected $config;
    protected $setting;
    protected $pidFile;

    private function __construct()
    {
        if ($this->checkBootstrap()) {
            require $this->bootstrap;
            $this->mergeLumenConfig(self::CONFIG_KEY, __DIR__ . '/../config/config.php');
            $this->config  = $this->initializeConfig();
            $this->setting = $this->initializeSetting();
            $this->pidFile = $this->config['pid_file'];
        }

    }

    private function checkBootstrap($file = self::DEFAULT_BOOTSTRAP_FILE_NAME)
    {
        $bootstrap_path = dirname(SLUMEN_COMPOSER_INSTALL) . '/../bootstrap/';
        $bootstrap_file = $bootstrap_path . $file;
        if (file_exists($bootstrap_file)) {
            $this->bootstrap = $bootstrap_file;
            return true;
        } else {
            echo 'please copy ' . realpath(dirname(SLUMEN_COMPOSER_INSTALL) . '/breeze2/slumen/bootstrap/' . $file) . PHP_EOL;
            echo 'to ' . realpath($bootstrap_path) . '/' . PHP_EOL;
            exit(1);
        }
        return false;
    }

    private function mergeLumenConfig($key, $path)
    {
        $app    = app();
        $config = $app['config']->get($key, []);
        $app['config']->set($key, array_merge(require $path, $config));
    }

    private function initializeSetting()
    {
        $app     = app();
        $slumen  = $app['config']->get(self::CONFIG_KEY, []);
        $setting = $slumen['swoole_server'];
        // $params = array(
        //     'reactor_num',
        //     // 'worker_num',
        //     // 'max_request',
        //     // 'max_conn',
        //     'task_worker_num',
        //     'task_ipc_mode',
        //     'task_max_request',
        //     'task_tmpdir',
        //     'dispatch_mode',
        //     'dispatch_func',
        //     'message_queue_key',
        //     // 'daemonize',
        //     'backlog',
        //     // 'log_file',
        //     'log_level',
        //     'heartbeat_check_interval',
        //     'heartbeat_idle_time',
        //     'open_eof_check',
        //     'open_eof_split',
        //     'package_eof',
        //     'open_length_check',
        //     'package_length_type',
        //     'package_length_func',
        //     'package_max_length',
        //     'open_cpu_affinity',
        //     'cpu_affinity_ignore',
        //     'open_tcp_nodelay',
        //     'tcp_defer_accept',
        //     'ssl_cert_file',
        //     'ssl_method',
        //     'ssl_ciphers',
        //     'user',
        //     'group',
        //     'chroot',
        //     'pid_file',
        //     'pipe_buffer_size',
        //     'buffer_output_size',
        //     'socket_buffer_size',
        //     'enable_unsafe_event',
        //     'discard_timeout_request',
        //     'enable_reuse_port',
        //     'ssl_ciphers',
        //     'enable_delay_receive',
        //     'open_http_protocol',
        //     'open_http2_protocol',
        //     'open_websocket_protocol',
        //     'open_mqtt_protocol',
        //     'reload_async',
        //     'tcp_fastopen',
        // );
        // $setting = array();
        // foreach ($params as $param) {
        //     $key   = self::CONFIG_PREFIX . strtoupper($param);
        //     $value = env($key);
        //     if ($value !== null) {
        //         $setting[$param] = $value;
        //     }
        // }

        // $setting['max_request'] = env(self::CONFIG_PREFIX . 'MAX_REQUEST', 0);
        // $setting['worker_num']  = env(self::CONFIG_PREFIX . 'WORKER_NUM', 1);
        // $setting['daemonize']   = env(self::CONFIG_PREFIX . 'DAEMONIZE', true);
        // $setting['log_file']    = env(self::CONFIG_PREFIX . 'LOG_FILE', storage_path('logs/slumen.log'));
        // $setting['max_conn']    = env(self::CONFIG_PREFIX . 'MAX_CONNECTION') ?: env(self::CONFIG_PREFIX . 'MAX_CONN', 1024);

        return $setting;
    }

    private function initializeConfig()
    {
        $app    = app();
        $slumen = $app['config']->get(self::CONFIG_KEY, []);

        $config                     = array();
        $config['running_mode']     = $slumen['running_mode'];
        $config['socket_type']      = $slumen['socket_type'];
        $config['host']             = $slumen['host'];
        $config['port']             = $slumen['port'];
        $config['gzip']             = $slumen['gzip'];
        $config['gzip_min_length']  = $slumen['gzip_min_length'];
        $config['static_resources'] = $slumen['static_resources'];
        $config['pid_file']         = $slumen['pid_file'];
        $config['stats_uri']        = $slumen['stats_uri'];
        $config['http_log_path']    = $slumen['http_log_path'];
        $config['http_log_path']    = $config['http_log_path'] ? realpath($config['http_log_path']) : false;
        $config['http_log_single']  = $slumen['http_log_single'];
        $config['root_dir']         = $slumen['root_dir'];
        $config['public_dir']       = $slumen['public_dir'];
        $config['service_hook']     = $slumen['service_hook'];

        $config['bootstrap'] = $this->bootstrap;
        // $config = array();
        // $config['running_mode']     = constant(env(self::CONFIG_PREFIX . 'RUNNING_MODE', 'SWOOLE_PROCESS'));
        // $config['socket_type']      = constant(env(self::CONFIG_PREFIX . 'SOCKET_TYPE', 'SWOOLE_SOCK_TCP'));

        // $config['host']             = env(self::CONFIG_PREFIX . 'HOST', '127.0.0.1');
        // $config['port']             = env(self::CONFIG_PREFIX . 'PORT', 9080);
        // $config['gzip']             = env(self::CONFIG_PREFIX . 'GZIP', 1);
        // $config['gzip_min_length']  = env(self::CONFIG_PREFIX . 'GZIP_MIN_LENGTH', 1024);
        // $config['static_resources'] = env(self::CONFIG_PREFIX . 'STATIC_RESOURCES', false);
        // $config['pid_file']         = env(self::CONFIG_PREFIX . 'PID_FILE', storage_path('app/slumen.pid'));
        // // $config['stats']            = env(self::CONFIG_PREFIX . 'STATS', true);
        // $config['stats_uri']         = env(self::CONFIG_PREFIX . 'STATS_URI', '/slumen-stats');
        // $config['http_log_path']     = env(self::CONFIG_PREFIX . 'HTTP_LOG_PATH', false);
        // $config['http_log_path']     = $config['http_log_path'] ? realpath($config['http_log_path']) : false;
        // $config['http_log_single']   = env(self::CONFIG_PREFIX . 'HTTP_LOG_SINGLE', false);
        // $config['root_dir']          = base_path();
        // $config['public_dir']        = base_path('public');
        // $config['bootstrap']         = $this->bootstrap;
        // $config['service_hook']      = env(self::CONFIG_PREFIX . 'SERVICE_HOOK');
        return $config;
    }

    protected function run($argv)
    {
        switch ($argv[1]) {
            case 'start':
                $this->startService();
                break;

            case 'status':
                $this->checkService();
                break;

            case 'stop':
                $this->stopService();
                break;

            case 'restart':
                $this->restartService();
                break;

            case 'reload':
                $this->reloadService();
                break;

            case 'auto-reload':
                $this->autoReloadService();
                break;

            default:
                echo 'slumen start | stop | restart | reload | status | auto-reload' . PHP_EOL;
                exit(1);
                break;
        }
    }

    protected function startService()
    {
        if ($this->getPid()) {
            echo 'slumen is already running' . PHP_EOL;
            exit(1);
        }

        $service = new Service($this->config, $this->setting);
        $service->start();
    }

    protected function restartService()
    {
        $time = 0;
        $pid  = $this->getPid();
        $this->sendSignal(SIGTERM);
        while (posix_getpgid($pid) && $time <= 10) {
            sleep(1);
            $time++;
        }
        if ($time > 10 && posix_getpgid($pid)) {
            echo 'slumen stop timeout' . PHP_EOL;
            exit(1);
        }
        $this->startService();
    }

    protected function reloadService()
    {
        $this->sendSignal(SIGUSR1);
    }

    protected function autoReloadService()
    {
        $pid = $this->getPid();
        if ($pid) {
            $kit = new AutoReload($pid);
            $kit->watch(base_path());
            $kit->addFileType('.php');
            $kit->run();
        } else {
            echo 'slumen is not running!' . PHP_EOL;
            exit(1);
        }

    }

    protected function stopService()
    {
        $time = 0;
        $pid  = $this->getPid();
        $this->sendSignal(SIGTERM);
        while (posix_getpgid($pid) && $time <= 10) {
            sleep(1);
            $time++;
        }
        if ($time > 10 && posix_getpgid($pid)) {
            echo 'slumen stop timeout' . PHP_EOL;
            exit(1);
        }
        exit(0);
    }

    protected function checkService()
    {
        $pid = $this->getPid();
        if ($pid) {
            echo 'slumen is running!' . PHP_EOL;
        } else {
            echo 'slumen is not running!' . PHP_EOL;
        }
    }

    protected function sendSignal($signal)
    {
        if ($pid = $this->getPid()) {
            posix_kill($pid, $signal);
            return true;
        } else {
            echo 'slumen is not running!' . PHP_EOL;
            exit(1);
        }
        return false;
    }

    protected function getPid()
    {
        if ($this->pidFile && file_exists($this->pidFile)) {
            $pid = file_get_contents($this->pidFile);
            if (posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($this->pidFile);
            }
        }
        return false;
    }

    public static function main($argv)
    {
        $command = new static;
        return $command->run($argv);
    }
}
