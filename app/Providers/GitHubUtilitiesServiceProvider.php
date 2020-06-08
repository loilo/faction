<?php

namespace App\Providers;

use App\Helpers\GitHubUtilities;
use Illuminate\Support\ServiceProvider;

/**
 * Register an instance of GitHubUtilities as a service
 * to make it accessible as a Laravel facade.
 */
class GitHubUtilitiesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('github-utilities', function () {
            return new GitHubUtilities();
        });
    }
}
