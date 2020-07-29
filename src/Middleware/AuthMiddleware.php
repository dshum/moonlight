<?php

namespace Moonlight\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        header('Cache-Control: no-store, must-revalidate');

        if (! Auth::guard('moonlight')->check()) {
            return redirect()->route('moonlight.login');
        }

        $user = Auth::guard('moonlight')->user();

        view()->share(['loggedUser' => $user]);

        return $next($request);
    }
}
