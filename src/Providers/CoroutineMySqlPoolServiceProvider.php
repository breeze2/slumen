<?php

namespace BL\Slumen\Providers;

use BL\Slumen\Coroutine\MySqlConnectionPool;

class CoroutineMySqlPoolServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'co-mysql-pool';

    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            $config = $app->make('config')->get('database.connections.mysql', []);

            if ($config) {
                $db_pool                 = config('slumen.db_pool');
                $config['provider_name'] = self::PROVIDER_NAME;
                return new MySqlConnectionPool($config, $db_pool['max_connection'], $db_pool['min_connection']);
            }
            return null;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [self::PROVIDER_NAME];
    }
}
