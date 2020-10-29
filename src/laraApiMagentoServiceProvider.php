<?php

namespace lucenarenato\laraApiMagento;

use Illuminate\Support\ServiceProvider;

class laraApiMagentoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/resources/database/migrations');
        $this->publishes(
            [__DIR__ . '/resources/database/migrations' => base_path('database/migrations')],
            'migrations'
        );
        // $this->loadViewsFrom(__DIR__ . '/../../resources/views/codecategory', 'codecategory');
        //$this->loadRoutesFrom(__DIR__.'/../../resources/routes/api.php');
        //require __DIR__ . '/../routes/api.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
