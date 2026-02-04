<?php

namespace App\Http\Middleware;
use App;
use Closure;

class localization
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
        // Check header request and determine localizaton
        if (session()->has('local')) {
            $local = session('local');
        } elseif ($request->hasHeader('X-localization')) {
            $local = $request->header('X-localization');
        } else {
            $local = app()->getLocale();
        }

        // set laravel localization
        App::setLocale($local);
        // continue request
        return $next($request);
    }
}
