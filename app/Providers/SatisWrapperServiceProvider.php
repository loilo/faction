<?php

namespace App\Providers;

use App\Library\Satis\SatisWrapper;
use Illuminate\Support\ServiceProvider;

/**
 * Register an instance of SatisWrapper as a service
 * to make it accessible as a Laravel facade.
 */
class SatisWrapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('satis-wrapper', function () {
            return new SatisWrapper();
        });
    }
}
