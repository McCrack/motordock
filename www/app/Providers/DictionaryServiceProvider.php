<?php

namespace App\Providers;

use View;
use App\TranslatorModel;
use Illuminate\Support\ServiceProvider;

class DictionaryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('translate', TranslatorModel::getInstance('dictionary'));
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
