<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Loilo\XFilesystem\XFilesystem;

/**
 * Register an instance of XFilesystem as a service
 * to make it accessible as a Laravel facade.
 */
class XFilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('x-filesystem', function () {
            return new XFilesystem();
        });
    }
}
