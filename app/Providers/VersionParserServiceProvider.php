<?php

namespace App\Providers;

use Composer\Semver\VersionParser;
use Illuminate\Support\ServiceProvider;

/**
 * Register an instance of VersionParser as a service
 * to make it accessible as a Laravel facade.
 */
class VersionParserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('version-parser', function () {
            return new VersionParser();
        });
    }
}
