<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activation
    |--------------------------------------------------------------------------
    |
    | The response cache can be toggled either explicitely by an environment
    | variable or implicitely by the app environment.
    | It's also only available for the default language.
    |
    */

    'enabled' =>
        env('RESPONSE_CACHE_ENABLED', env('APP_ENV') !== 'local') &&
        config('app.locale') === config('app.fallback_locale'),

    /*
    |--------------------------------------------------------------------------
    | Cache Profile
    |--------------------------------------------------------------------------
    |
    | The given class will determinate if a request should be cached. The
    | default class will cache all successful GET-requests.
    |
    | You can provide your own class given that it implements the
    | CacheProfile interface.
    |
    */

    'cache_profile' => \App\Library\ResponseCache\CacheRequests::class,

    /*
    |--------------------------------------------------------------------------
    | Cache Lifetime
    |--------------------------------------------------------------------------
    |
    | When using the default CacheRequestFilter this setting controls the
    | default number of seconds responses must be cached.
    |
    */

    'cache_lifetime_in_seconds' => env(
        'RESPONSE_CACHE_LIFETIME',
        60 * 60 * 24 * 7 * 4,
    ),

    /*
    |--------------------------------------------------------------------------
    | Time Header
    |--------------------------------------------------------------------------
    |
    | This setting determines if a HTTP header named with the cache time
    | should be added to a cached response. This can be handy when debugging.
    |
    */

    'add_cache_time_header' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Time Header Name
    |--------------------------------------------------------------------------
    |
    | This setting determines the name of the http header that contains
    | the time at which the response was cached.
    |
    */

    'cache_time_header_name' => env(
        'RESPONSE_CACHE_HEADER_NAME',
        'Laravel-Response-Cache',
    ),

    /*
    |--------------------------------------------------------------------------
    | Store Name
    |--------------------------------------------------------------------------
    |
    | Here you may define the cache store that should be used to store requests.
    | This can be the name of any store that is configured in app/config/cache.php
    |
    */

    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'response'),

    /*
    |--------------------------------------------------------------------------
    | Replacers
    |--------------------------------------------------------------------------
    |
    | Here you may define replacers that dynamically replace content from the
    | response. Each replacer must implement the Replacer interface.
    |
    */

    'replacers' => [\Spatie\ResponseCache\Replacers\CsrfTokenReplacer::class],

    /*
    |--------------------------------------------------------------------------
    | Cache Tag
    |--------------------------------------------------------------------------
    |
    | If the cache driver you configured supports tags, you may specify a tag
    | name here. All responses will be tagged. When clearing the responsecache
    | only items with that tag will be flushed.
    |
    */

    'cache_tag' => '',

    /*
    |--------------------------------------------------------------------------
    | Hasher
    |--------------------------------------------------------------------------
    |
    | This class is responsible for generating a hash for a request. This hash
    | is used to look up an cached response.
    |
    */

    'hasher' => \App\Library\ResponseCache\Hasher::class,

    /*
    |--------------------------------------------------------------------------
    | Serializer
    |--------------------------------------------------------------------------
    |
    | This class is responsible for serializing responses.
    |
    */
    'serializer' => \Spatie\ResponseCache\Serializers\DefaultSerializer::class,
];
