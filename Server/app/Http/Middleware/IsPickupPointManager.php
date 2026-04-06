<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class IsPickupPointManager
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (
            $user &&
            $user->user_type === 'staff' &&
            optional(optional($user->staff)->pick_up_point)->id
        ) {
            return $next($request);
        }

        Auth::logout();
        session(['link' => url()->current()]);
        flash(translate('You are not assigned to any pickup point.'))->error();

        return redirect()->route('deliveryboy.login');
    }
}
