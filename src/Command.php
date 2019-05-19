<?php
namespace BL\Slumen;

use RuntimeException;

class Command
{
    const VERSION             = 'slumen 0.8.0';
    const BOOTSTRAP_FILE_NAME = 'slumen.php';

    protected $autoload_file;
    protected $bootstrap_file;
    protected $pid_file;

    private function __construct()
    {
        if (defined('SLUMEN_COMPOSER_INSTALL')) {
            $this->pid_file = dirname(SLUMEN_COMPOSER_INSTALL) . '/../storage/slumen.pid';
            $this->autoload_file = SLUMEN_COMPOSER_INSTALL;
            $this->checkBootstrap();
            require $this->bootstrap_file;
        } else {
            throw new RuntimeException('Slumen is not installed');
        }
    }

    private function checkBootstrap($file = self::BOOTSTRAP_FILE_NAME)
    {
        $bootstrap_path = dirname($this->autoload_file) . '/../bootstrap/';
        $bootstrap_file = $bootstrap_path . $file;
        if (file_exists($bootstrap_file)) {
            $this->bootstrap_file = $bootstrap_file;
        } else {
            echo 'please copy ' . realpath(dirname($this->autoload_file) . '/breeze2/slumen/bootstrap/' . $file) . PHP_EOL;
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

        $service = new Service($this->autoload_file, $this->bootstrap_file, $this->pid_file);
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
        if ($this->pid_file && file_exists($this->pid_file)) {
            $pid = file_get_contents($this->pid_file);
            $pid = (int) $pid;
            if ($pid && posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($this->pid_file);
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
