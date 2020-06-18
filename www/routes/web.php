<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix(
    Config::get("locale")
)->group(function(){

    Route::prefix('ajax')->group(function(){

        Route::get("/message/{slug}", 'SiteMap@message');

        Route::get("/models/{id}", function($id){
            $models = [];
            $list = \App\VehicleModel::where('BrandID', $id)->get();
            foreach ($list as $model) {
                $key = strtolower($model->model);
                $models[$key] = [
                    'id'  => $model->model_id,
                    'slug'  => $model->slug,
                    'name'  => $model->model
                ];
            }
            return json_encode($models);
        });

        Route::get("/item/{id}", 'Showcase@item');

        Route::post("/cart", function(){
            $cart = json_decode(Request::instance()->getContent(), true);
            $catalog = App\StoreModel::find(array_keys($cart));

            return view('components.cart', [
                'catalog'   => $catalog,
            ]);
        });

        Route::post("/order",'Orders@createOrder');
        Route::post("/callback",'Orders@callback');
    });


    Route::get('/motor/{motor}', 'Motors@index');

    Route::get('/{product}', 'Showcase@card')
        ->where('product', '^[a-z0-9_-]+-\d+$');

    Route::get('/{slug}', 'SiteMap@index');
    Route::get('/{category}/{brand}', 'SiteMap@category');
    Route::get('/{category}/{brand}/{lineup}/{item?}', 'Showcase@lineup');

    Route::get('/', 'SiteMap@home');
});