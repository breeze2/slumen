<?php

namespace BL\Slumen\Provider;
use BL\Slumen\Http\Handler;

class HookServiceProvider extends ServiceProvider
{
    const PROVIDER_NAME = 'SlumenHook';
    
    public function register()
    {
        $this->app->singleton(self::PROVIDER_NAME, function ($app) {
            return new Handler();
        });
    }

}
