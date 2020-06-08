<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Highlight\Highlighter;

/**
 * Register an instance of Highlighter as a service
 * to make it accessible as a Laravel facade.
 */
class HighlightServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('highlight', function () {
            return new Highlighter();
        });
    }
}
