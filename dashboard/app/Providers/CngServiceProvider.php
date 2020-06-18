<?php

namespace App\Providers;

use View;
use App\Core\Config as Cng;
use Illuminate\Support\ServiceProvider;

class CngServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('cng', Cng::getInstance());
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
