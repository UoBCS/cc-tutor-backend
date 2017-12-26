<?php

namespace App\Core;

use Illuminate\Support\ServiceProvider;

class InspectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('App\Core\Inspector', function ($app) {
            return new Inspector(); //$app->make('HttpClient')
        });
    }
}
