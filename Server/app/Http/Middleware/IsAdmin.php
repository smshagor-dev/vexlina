<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsAdmin
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
        if (
            Auth::check() &&
            Auth::user()->user_type == 'staff' &&
            optional(optional(Auth::user()->staff)->pick_up_point)->id
        ) {
            return redirect()->route('pickup-point.dashboard');
        }

        if (Auth::check() && (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff')) {
            return $next($request);
        }
        else{
            abort(404);
        }
    }
}
