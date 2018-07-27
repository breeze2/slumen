<?php

namespace BL\Slumen\Provider;

use BL\Slumen\Http\Logger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

class HttpLoggerServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'SlumenHttpLogger';

    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            $file_name       = 'access.log';
            $http_log_path   = config('slumen.http_log_path');
            $http_log_single = config('slumen.http_log_single');
            if (!$http_log_path) {
                $file      = $http_log_path . '/' . $file_name;
                $logger    = new Logger('HTTP_LOG');
                $formatter = new JsonFormatter();
                if ($http_log_single) {
                    $handler = new StreamHandler($file, Logger::INFO);
                } else {
                    $handler = new RotatingFileHandler($file, 0, Logger::INFO);
                }
                $handler->setFormatter($formatter);

                $logger->pushHandler($handler);
                return $logger;
            }
            return null;
        });
    }
}
