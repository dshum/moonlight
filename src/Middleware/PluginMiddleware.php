<?php

namespace Moonlight\Middleware;

use Closure;

class PluginMiddleware
{
    public function handle($request, Closure $next)
    {
        $site = \App::make('site');

        $styles = $site->getStyles();
        $scripts = $site->getScripts();

        view()->share([
            'styles' => $styles,
            'scripts' => $scripts,
        ]);

        return $next($request);
    }
}