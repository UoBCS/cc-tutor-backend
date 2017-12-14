<?php

namespace App\Api\Algorithms;

use App\Core\Inspector;
use Illuminate\Support\ServiceProvider;

class AlgorithmServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('App\Core\Inspector', function ($app) {
            return new Inspector(); //$app->make('HttpClient')
        });
    }
}
