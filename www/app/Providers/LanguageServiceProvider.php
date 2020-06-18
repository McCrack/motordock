<?php

namespace App\Providers;

use App;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $locales = config('app.locales');
        $locale = $request->segment(1);

        if (isset($locales[$locale])) {
            define('USER_LOCALE', $locale);
            define('USER_LANG', $locales[$locale]);
        } else {
            $locale = "";
            define('USER_LOCALE', config('app.locale'));
            define('USER_LANG', config('app.language'));
        }
        App::setLocale(USER_LOCALE);
        Config::set('locale', $locale);
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
