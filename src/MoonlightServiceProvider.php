<?php

namespace Moonlight;

use App;
use Config;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Moonlight\Main\Site;
use Moonlight\Models\User;
use Route;

class MoonlightServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Moonlight\Controllers';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (file_exists(__DIR__.'/helpers.php')) {
            include __DIR__.'/helpers.php';
        }

        $this->loadViewsFrom(__DIR__.'/resources/views', 'moonlight');

        $this->publishes([
            __DIR__.'/database/migrations' => $this->app->databasePath().'/migrations',
            __DIR__.'/database/seeds' => $this->app->databasePath().'/seeds',
            __DIR__.'/resources/assets' => public_path('packages/moonlight'),
        ], 'moonlight');

        if (file_exists($path = app_path('Http/site.php'))) {
            include $path;
        }

        $this->setGuard();

        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('site', function (): Site {
            return new Site;
        });

        parent::register();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        Route::prefix('/moonlight')
            ->name('moonlight.')
            ->namespace($this->namespace)
            ->group(__DIR__.'/routes.php');
    }

    protected function setGuard()
    {
        $authGuards = Config::get('auth.guards');
        $authProviders = Config::get('auth.providers');

        $authGuards['moonlight'] = [
            'driver' => 'session',
            'provider' => 'moonlight',
        ];

        $authProviders['moonlight'] = [
            'driver' => 'eloquent',
            'model' => User::class,
        ];

        Config::set('auth.guards', $authGuards);
        Config::set('auth.providers', $authProviders);
    }
}
