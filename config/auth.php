<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Access
    |--------------------------------------------------------------------------
    |
    | This disables all authentication procedures.
    |
    */

    'public' => env('PUBLIC', env('APP_ENV') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Remember Duration
    |--------------------------------------------------------------------------
    |
    | This defines how long a user authenticated via OAuth is remembered before
    | issueing a revalidation. The revalidation does not need any user
    | interaction but is a hit to performance due to the extra HTTP roundtrips.
    | Value must be parseable by strtotime().
    |
    */

    'remember_duration' => env('AUTH_REMEMBER_DURATION', '1 day'),

    /*
    |--------------------------------------------------------------------------
    | GitHub Organizations Whitelist
    |--------------------------------------------------------------------------
    |
    | This is a list of GitHub organizations whose members are allowed to
    | access the repository.
    |
    */

    'github_orgs_whitelist' => comma_split(
        env('AUTH_GITHUB_ORGS_WHITELIST', ''),
    ),

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | This is a list of IP addresses or CIDR notation subnets where the
    | repository should be available without further authentication.
    |
    */

    'ip_whitelist' => comma_split(env('AUTH_IP_WHITELIST', '')),

    /*
    |--------------------------------------------------------------------------
    | GitHub Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The secret declared in the organization's webhook settings.
    |
    */

    'github_webhook_secret' => env('REPOSITORY_GITHUB_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'public.ip.oauth',
        ],

        'oauth' => [
            'driver' => 'oauth',
        ],

        'composer' => [
            'driver' => 'public.ip.token',
        ],

        'webhook' => [
            'driver' => 'webhook',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [],
];
