<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Session;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\LoggedUser;
use Moonlight\Models\User;

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