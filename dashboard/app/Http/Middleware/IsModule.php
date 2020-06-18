<?php

namespace App\Http\Middleware;

use Closure;

class IsModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $module = $request->route()->parameter('module') ?? config('app.default_module');;

        if (file_exists("../app/{$module}/index.php")) {
            return $next($request);
        }
        return redirect('/');
    }
}
