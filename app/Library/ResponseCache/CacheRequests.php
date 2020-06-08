<?php

namespace App\Library\ResponseCache;

use Illuminate\Http\Request;
use Route;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Str;

/**
 * Caching strategy for response cache,
 * disabling all requests with search query parameters.
 */
class CacheRequests extends CacheAllSuccessfulGetRequests
{
    /**
     * Don't cache requests with a search parameter to avoid cache overkill
     */
    public function shouldCacheRequest(Request $request): bool
    {
        if (!empty($request->query('search'))) {
            return false;
        }

        return parent::shouldCacheRequest($request);
    }

    /**
     * Generate a package-dependent cache key suffix when on a package page.
     * This allows to exclusively clear cached pages related to that package.
     *
     * @param Request $request
     */
    public function useCacheNameSuffix(Request $request): string
    {
        $route = Route::current();

        if ($route->getName() === 'packages') {
            return '.packages';
        } elseif (Str::startsWith($route->getName(), 'package.')) {
            $suffix = join('.', array_reverse(explode('.', $route->getName())));

            return sprintf('.%s.%s', $suffix, $route->parameter('name'));
        } else {
            return '';
        }
    }
}
