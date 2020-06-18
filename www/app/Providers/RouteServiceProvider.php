<?php

namespace App\Providers;

use App\LandingPageModel as LPModel;
use App\CategoriesModel as CatModel;

use Illuminate\Support\Facades\Route;
use App\Exceptions\Custom;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();

        //Route::model('slug', \App\LandingPageModel::class);
        Route::bind('slug', function ($slug) {
            $page = LPModel::whereSlug($slug)->first();
            if (empty($page)) {
                $page = CatModel::getCategory($slug);
                if (empty($page)) {
                    //$page = LPModel::where('slug', "404")->first();
                    throw new \App\Exceptions\Custom('Page Not Found');
                }
            }
            return $page;
        });

        Route::bind('category', function ($slug) {
            $category =  CatModel::getCategory($slug);
            if (empty($category)) {
                throw new \App\Exceptions\Custom('Category Not Found');
            }
            return $category;
        });
        Route::bind('brand', function ($slug) {
            $brand = \App\BrandsModel::whereSlug($slug)->first();
            if (empty($brand)) {
                if (CatModel::whereSlug($slug)->exists()) {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: /{$slug}", 301);
                    exit;
                } else {
                    throw new \App\Exceptions\Custom('Brand Not Found');
                }
            }
            return $brand;
        });
        Route::bind('lineup', function ($slug) {
            $lineup = \App\LineupModel::whereSlug($slug)->first();
            if (empty($lineup)) {
                throw new \App\Exceptions\Custom('Lineup Not Found');
            }
            return $lineup;
        });
        Route::bind('motor', function ($motor_id) {
            $motor = \App\MotorModel::find($motor_id);
            if (empty($motor)) {
                throw new \App\Exceptions\Custom('Motor Not Found');
            }
            return $motor;
        });

        Route::bind('product', function($slug){
            $slugItems = explode("-", $slug);
            $item = \App\ProductCardModel::find(array_pop($slugItems));
            if (empty($item)) {
                $item = null;
            }
            return $item;
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
