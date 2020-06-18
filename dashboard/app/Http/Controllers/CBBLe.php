<?php

namespace App\Http\Controllers;

use App\Core\Config AS Cng;
use Illuminate\Http\Request;

class CBBLe extends Controller
{
    public function __invoke(Request $request, $module, $method = null, $args = null)
    {
        $module = $module ?? config('app.default_module');

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
                return $controller->{$method}($request, $cng);
            } else {
                return $controller->index($method, $cng);
            }
        } else {
            return view('welcome');
        }
    }
}
