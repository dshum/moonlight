<?php

namespace Moonlight\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        if ($request->ajax()) {
            if (! Auth::guard('moonlight')->check()) {
                return response()->json(['error' => 'Пользователь не авторизован.'], 403);
            }

            $user = Auth::guard('moonlight')->user();

            if (! $user->hasAccess('admin')) {
                return response()->json(['error' => 'У вас нет прав на управление пользователями.'], 403);
            }
        } else {
            if (! Auth::guard('moonlight')->check()) {
                return redirect()->route('moonlight.login');
            }

            $user = Auth::guard('moonlight')->user();

            if (! $user->hasAccess('admin')) {
                return redirect()->route('moonlight.home');
            }
        }

        return $next($request);
    }
}
