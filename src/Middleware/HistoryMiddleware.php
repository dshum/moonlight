<?php

namespace Moonlight\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HistoryMiddleware
{
    public function handle($request, Closure $next)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        Cache::put("history_url_{$loggedUser->id}", $request->getRequestUri(), 86400);

        return $next($request);
    }
}
