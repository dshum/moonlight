<?php

namespace Moonlight\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class HistoryMiddleware
{
    public function handle($request, Closure $next)
    {   
        $loggedUser = Auth::guard('moonlight')->user();
        
        $historyUrl = $request->getRequestUri();

        cache()->put("history_{$loggedUser->id}", $historyUrl, 1440);

        return $next($request);
    }
}