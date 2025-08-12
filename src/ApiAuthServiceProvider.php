<?php

namespace Cofa\LaravelAuthenticationFlow;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthServiceProvider as LaravelAuthProvider;
use Illuminate\Routing\RoutingServiceProvider;

class ApiAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/apiauth.php'=> config_path('apiauth.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->register(LaravelAuthProvider::class);
        $this->app->register(RoutingServiceProvider::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/apiauth.php', 'apiauth'
        );
    }
}
