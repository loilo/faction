<?php

namespace App\Providers;

use Cache;
use Illuminate\Support\ServiceProvider;

/**
 * Register an instance of the in-memory array cache store
 * as a service to make it accessible as a Laravel facade.
 */
class MemoizeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('memoize', function () {
            return Cache::driver('array');
        });
    }
}
