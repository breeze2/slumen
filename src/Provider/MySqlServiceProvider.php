<?php

namespace BL\Slumen\Provider;

use BL\Slumen\Database\CoMySqlManager;

class HookServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'SlumenMySql';

    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            app('db');
            $config = app()['config']['database.connections.mysql'];
            if ($config) {
                return new CoMySqlManager([
                    'host'        => $config['host'],
                    'port'        => $config['port'],
                    'user'        => $config['username'],
                    'password'    => $config['password'],
                    'database'    => $config['database'],
                    'charset'     => $config['database'],
                    'strict_type' => $config['strict'],
                    'fetch_mode'  => false,
                    'timeout'     => -1,
                ]);
            }
            return null;
        });
    }

}
