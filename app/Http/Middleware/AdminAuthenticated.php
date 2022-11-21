<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AdminAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if($guard == 'admin' && Auth::guard($guard)->check())
        {
            return $next($request);
        }
       
        return redirect(route('admin-login'));
    }
}