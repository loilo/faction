<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Service provider for general app-related needs
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set locale of the app to format
        App::setLocale(config('app.locale'));

        // Register Str::unstart(), a method to remove a certain string from
        // the start of another string. This is the opposite to Str::start().
        Str::macro(
            'unstart',
            fn($value, $prefix) => static::substr(
                static::start($value, $prefix),
                static::length($prefix),
            ),
        );

        // Register Str::unfinish(), a method to remove a certain string from
        // the end of another string. This is the opposite to Str::finish().
        Str::macro(
            'unfinish',
            fn($value, $cap) => static::substr(
                static::finish($value, $cap),
                0,
                -static::length($cap),
            ),
        );
    }
}
