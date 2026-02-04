<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (auth('admin')->check() && auth('admin')->user()->id == 1) {
            return $next($request);
        }

        if (auth('admin')->check() && auth('admin')->user()->hasPermission($permission)) {
            return $next($request);
        }

        Toastr::warning(translate('Access Denied! You do not have permission.'));
        return back();
    }
}
