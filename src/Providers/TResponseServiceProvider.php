<?php

namespace TResponse\Providers;

use Illuminate\Support\ServiceProvider;

class TResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/TResponse.php'          => app_path('Services\TResponse\TResponse.php'),
            dirname(__DIR__) . '/TResponseException.php' => app_path('Services\TResponse\TResponseException.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
