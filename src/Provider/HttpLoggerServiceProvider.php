<?php

namespace BL\Slumen\Provider;

use BL\Slumen\Http\Logger;

class HttpLoggerServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'SlumenHttpLogger';

    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            $file_name       = 'access.log';
            $http_log_path   = config('slumen.http_log_path');
            $http_log_single = config('slumen.http_log_single');
            if ($http_log_path) {
                $file      = $http_log_path . '/' . $file_name;
                $logger = new Logger($http_log_path, $file_name, $http_log_single);
                return $logger;
            }
            return null;
        });
    }
}
