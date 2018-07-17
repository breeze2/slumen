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
        if ($pid) {
            $this->sendSignal(SIGTERM);
            while (posix_getpgid($pid) && $time <= 10) {
                sleep(1);
                $time++;
            }
            if ($time > 10 && posix_getpgid($pid)) {
                echo 'slumen stop timeout' . PHP_EOL;
                exit(1);
            }
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
