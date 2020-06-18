<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Core\Config as Cng;

Auth::routes();


Route::any('/{module?}/{method?}/{args?}', function (Request $request, $module, $method = null, $args = null) {
	$module = $module ?? config('app.default_module');

	View::share('module', $module);

	Cng::join(\Auth::user()->config);
	$cng = Cng::merge("../app/{$module}/config.json");

	$userGroup = \Auth::user()->group;

	if (in_array($userGroup, $cng->access)) {
		if ($args) {
			$args = explode('/', $args);
			foreach ($args as $i => $arg) {
				define("ARG_{$i}", $arg);
			}
		}
		$controller = app("\App\\{$module}\index");
		if ($method && method_exists($controller, $method)) {
			return $controller->{$method}($request);
		} else {
			return $controller->index($method);
		}
	} else {
		return view('welcome');
	}
})
	->middleware(['auth', 'IsModule'])
	->where('args', '(.*)');
