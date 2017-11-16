<?php

namespace Domatskiy\Exchange1C;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class Exchange1CServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        # routes
        $this->loadRoutesFrom(__DIR__.'/../publish/routes.php');

        # migrations
        # $this->loadMigrationsFrom(__DIR__.'/../publish/migrations/');

        # config
        $this->publishes([__DIR__.'/../publish/config/' => config_path()], 'config');
    }
    /**
     * Register the application services.
     */
    public function register()
    {

    }
}