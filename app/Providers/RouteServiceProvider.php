<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapComposerRoutes();
        $this->mapWebHookRoutes();
        $this->mapWebRoutes();
        $this->mapOAuthRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "oauth" routes for the application.
     *
     * They routes are reserved exclusively for handling
     * communication with the OAuh provider.
     *
     * @return void
     */
    protected function mapOAuthRoutes()
    {
        Route::middleware('oauth')
            ->namespace($this->namespace)
            ->group(base_path('routes/oauth.php'));
    }

    /**
     * Define the "composer" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapComposerRoutes()
    {
        Route::middleware('composer')
            ->namespace($this->namespace)
            ->group(base_path('routes/composer.php'));
    }

    /**
     * Define the "webhook" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapWebHookRoutes()
    {
        Route::middleware('webhook')
            ->namespace($this->namespace)
            ->group(base_path('routes/webhook.php'));
    }
}
