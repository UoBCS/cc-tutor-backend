<?php

namespace App\Infrastructure\Http\Providers;

use File;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        // Namespaces to search for routes.php files
        $namespaces = [
            'App\Api',
            'App\Infrastructure',
        ];

        // Get the namespaces directories
        $highLevelParts = array_map(function ($namespace) {
            return File::directories(app_path(explode('\\', $namespace)[1]));
        }, $namespaces);

        foreach ($highLevelParts as $part => $partComponents) {

            // Get the top level directories for each namespace
            foreach ($partComponents as $segment) {

                // Define array to determine level of protection in the routes
                $fileNames = [
                    'routes'            => true,
                    'routes_protected'  => true,
                    'routes_public'     => false,
                ];

                // Protection middleware if routes_protected.php or routes.php
                $middleware = ['auth:api'];
                $publicMiddleware = [];

                foreach ($fileNames as $fileName => $protected) {
                    $path = "$segment/$fileName.php";

                    // No routes*.php existent
                    if (!File::exists($path)) {
                        continue;
                    }

                    // Add middleware and namespace to router
                    $router = Route::middleware($protected ? $middleware : $publicMiddleware)
                                    ->namespace($namespaces[$part] . '\\' . basename($segment) . '\\Controllers');

                    // Checks whether .routes_no_prefix exists
                    // if true don't add any prefix
                    // if false add prefix
                    if (!File::exists("$segment/.route_settings")) {
                        $router->prefix(strtolower(basename($segment)));
                    } else {
                        $contents = File::get("$segment/.route_settings");
                        $props = json_decode($contents, true);

                        if (!isset($props['default_prefix']) || $props['default_prefix']) {
                            $router->prefix(strtolower(basename($segment)));
                        }
                    }

                    // Finally wrap the routes
                    $router->group($path);
                }
            }
        }
    }
}
