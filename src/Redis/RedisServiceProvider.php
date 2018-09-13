<?php

namespace BL\Slumen\Redis;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use ReflectionMethod;

class RedisServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME_REDIS            = 'redis';
    const PROVIDER_NAME_REDIS_CONNECTION = 'redis.connection';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME_REDIS, function ($app) {
            $config = $app->make('config')->get('database.redis', []);

            $reflection = new ReflectionMethod(RedisManager::class, '__construct');

            if ($reflection->getNumberOfParameters() === 3) {
                return new RedisManager($app, Arr::pull($config, 'client', 'predis'), $config);
            }
            return new RedisManager(Arr::pull($config, 'client', 'predis'), $config);
        });

        $this->app->bind(self::PROVIDER_NAME_REDIS_CONNECTION, function ($app) {
            return $app[self::PROVIDER_NAME_REDIS]->connection();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [self::PROVIDER_NAME_REDIS, self::PROVIDER_NAME_REDIS_CONNECTION];
    }
}
