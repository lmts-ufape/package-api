<?php

namespace Lmts\src;

use Illuminate\Support\ServiceProvider;
// use Lmts\src\controller\LmtsApiController;

class LmtsApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->make('Lmts\src\controller\LmtsApi');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->publishes([
        __DIR__ . '/config' => config_path(),
        __DIR__ . '/middleware' => base_path('app/Http/Middleware'),
        ]);
        // $api = new LmtsApiController();

        // $this->app->instance('api', $api);
    }
}
