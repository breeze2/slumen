<?php

namespace BL\Slumen\Provider;

use BL\Slumen\Http\EventSubscriber;

class HttpEventSubscriberServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'SlumenHttpEventSubscriber';

    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            return new EventSubscriber;
        });
    }
}
