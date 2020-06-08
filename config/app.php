<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Faction'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', env('APP_ENV') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Debug Blacklist
    |--------------------------------------------------------------------------
    |
    | Hide well-known secrets from being displayed in debug mode
    |
    */

    'debug_blacklist' => [
        '_ENV' => [
            'APP_KEY',
            'GITHUB_CLIENT_ID',
            'GITHUB_CLIENT_SECRET',
            'REPOSITORY_GITHUB_TOKEN',
            'REPOSITORY_GITHUB_WEBHOOK_SECRET',
        ],
        '_SERVER' => [
            'APP_KEY',
            'GITHUB_CLIENT_ID',
            'GITHUB_CLIENT_SECRET',
            'REPOSITORY_GITHUB_TOKEN',
            'REPOSITORY_GITHUB_WEBHOOK_SECRET',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Package Repository
    |--------------------------------------------------------------------------
    |
    | Configuration related to the private Composer repository itself
    |
    */

    'repository' => [
        'github_org' => env('REPOSITORY_GITHUB_ORG'),
        'package_vendor' => env('REPOSITORY_PACKAGE_VENDOR'),
        'github_token' => env('REPOSITORY_GITHUB_TOKEN'),
        'max_search_results' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Satis
    |--------------------------------------------------------------------------
    |
    | The Satis library creates a Composer repository from a number of GitHub
    | repositories. In this app, it is accompanied by a wrapper which makes it
    | more targeted and improves its efficiency.
    |
    */

    'satis' => [
        'output_path' => storage_path('app/satis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Groups
    |--------------------------------------------------------------------------
    |
    | This allows to group packages by custom package name prefixes and to
    | visually keep them closer together through logos and distinct colors.
    |
    | A full group definition could look like this:
    | [
    |     'id' => 'acme',
    |     'logo' => '<svg>...</svg>',
    |     'prefix' => 'acme-',
    |     'colors' => [
    |         'lighter' => '#EEF6FB',
    |         'light' => '#DEEBF2',
    |         'dark' => '#BAD2E0',
    |         'darker' => '#7997A8',
    |     ],
    |     'name' => 'ACME'
    | ]
    |
    */

    'package_groups' => [],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Radic\BladeExtensions\BladeExtensionsServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BladeHighlightServiceProvider::class,
        App\Providers\BladeBufferServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\SqliteServiceProvider::class,
        App\Providers\GitHubUtilitiesServiceProvider::class,
        App\Providers\HighlightServiceProvider::class,
        App\Providers\MemoizeServiceProvider::class,
        App\Providers\VersionParserServiceProvider::class,
        App\Providers\SatisWrapperServiceProvider::class,
        App\Providers\XFilesystemServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'FS' => App\Facades\XFilesystemFacade::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'GitHubApi' => GrahamCampbell\GitHub\Facades\GitHub::class,
        'GitHubUtils' => App\Facades\GitHubUtilitiesFacade::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Highlight' => App\Facades\HighlightFacade::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Memoize' => App\Facades\MemoizeFacade::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Satis' => App\Facades\SatisWrapperFacade::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'VersionParser' => App\Facades\VersionParserFacade::class,
        'View' => Illuminate\Support\Facades\View::class,
    ],
];
