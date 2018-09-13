<?php

namespace BL\Slumen\Database;

use Illuminate\Support\ServiceProvider;

class MySqlServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME_MYSQL = 'mysql';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME_MYSQL, function ($app) {
            $config         = $app->make('config')->get('database.connections.mysql', []);
            $config['name'] = 'mysql';
            return new MySqlConnectionPool($config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [self::PROVIDER_NAME_MYSQL];
    }
}
