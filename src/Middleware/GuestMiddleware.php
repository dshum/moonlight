<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Session;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\LoggedUser;
use Moonlight\Models\User;

class GuestMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('moonlight')->check()) {
            return redirect()->route('moonlight.home');
        }

        return $next($request);
    }
}