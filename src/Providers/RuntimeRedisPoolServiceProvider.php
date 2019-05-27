<?php

namespace BL\Slumen\Providers;

use BL\Slumen\Runtime\RedisConnectionPool;

class RuntimeRedisPoolServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME   = 'rt-redis-pool';
    const CONNECTION_NAME = 'default';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            $config = $app->make('config')->get('database.redis', []);
            if ($config) {
                $db_pool                   = config('slumen.db_pool');
                $config['provider_name']   = self::PROVIDER_NAME;
                $config['connection_name'] = self::CONNECTION_NAME;
                return new RedisConnectionPool($config, $db_pool['max_connection'], $db_pool['min_connection']);
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
