<?php
namespace BL\Slumen;

class Command
{
    const VERSION             = 'slumen 0.8.0';
    const BOOTSTRAP_FILE_NAME = 'slumen.php';

    protected $bootstrap;
    protected $pidFile;

    private function __construct()
    {
        $this->checkBootstrap();
        $this->pidFile = __DIR__ . '/slumen.pid';
    }

    private function checkBootstrap($file = self::BOOTSTRAP_FILE_NAME)
    {
        $bootstrap_path = dirname(SLUMEN_COMPOSER_INSTALL) . '/../bootstrap/';
        $bootstrap_file = $bootstrap_path . $file;
        if (file_exists($bootstrap_file)) {
            $this->bootstrap = $bootstrap_file;
        } else {
            echo 'please copy ' . realpath(dirname(SLUMEN_COMPOSER_INSTALL) . '/breeze2/slumen/bootstrap/' . $file) . PHP_EOL;
            echo 'to ' . realpath($bootstrap_path) . '/' . PHP_EOL;
            exit(1);
        }
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

        $service = new Service($this->bootstrap);
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
        } else {
            echo 'slumen is not running!' . PHP_EOL;
            exit(1);
        }
    }

    protected function getPid()
    {
        if ($this->pidFile && file_exists($this->pidFile)) {
            $pid = file_get_contents($this->pidFile);
            $pid = (int) $pid;
            if ($pid && posix_getpgid($pid)) {
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
