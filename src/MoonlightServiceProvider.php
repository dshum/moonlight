<?php

namespace Moonlight;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Moonlight\Main\Site;

class MoonlightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $site = \App::make('site');

        $site->initMicroTime();

        if (file_exists($path = __DIR__.'/helpers.php')) {
			include $path;
		}

		if (file_exists($path = app_path().'/Http/site.php')) {
			include $path;
		}
        
        $this->loadViewsFrom(__DIR__.'/resources/views', 'moonlight');
        
        $this->publishes([
            __DIR__.'/database/migrations' => $this->app->databasePath().'/migrations',
            __DIR__.'/database/seeds' => $this->app->databasePath().'/seeds',
            __DIR__.'/resources/assets' => public_path('packages/moonlight'),
        ], 'moonlight');
        
        DB::enableQueryLog();

        $authGuards = Config::get('auth.guards');
        $authProviders = Config::get('auth.providers');

        $authGuards['moonlight'] = [
            'driver' => 'session',
            'provider' => 'moonlight',
        ];

        $authProviders['moonlight'] = [
            'driver' => 'eloquent',
            'model' => \Moonlight\Models\User::class,
        ];

        Config::set('auth.guards', $authGuards);
        Config::set('auth.providers', $authProviders);
        
        include __DIR__.'/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        \App::singleton('site', function($app) {
			return new Site;
		}); 
    }
}
