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
    protected $pidFile;

    private function __construct()
    {
        self::globalHelpers();
        if ($this->checkBootstrap()) {
            require $this->bootstrap;
            $this->mergeLumenConfig(self::CONFIG_KEY, __DIR__ . '/../config/slumen.php');
            $this->config  = $this->initializeConfig();
            $this->pidFile = $this->config['pid_file'];
        }

    }

    public static function globalHelpers()
    {
        require_once __DIR__ . '/helpers.php';
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

    private function initializeConfig()
    {
        $app                 = app();
        $slumen              = $app['config']->get(self::CONFIG_KEY, []);
        $config              = $slumen;
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

        $service = new Service($this->config);
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
