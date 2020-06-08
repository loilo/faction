<?php

namespace App\Library\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\DefaultHasher;

/**
 * A custom response cache hasher
 */
class Hasher extends DefaultHasher
{
    /**
     * Generate a response cache hash with a special semantic suffix.
     * Since the response cache is database-backed, this suffix allows to
     * filter for itself in the cached pages.
     *
     * @param Request $request
     */
    public function getHashFor(Request $request): string
    {
        return 'responsecache-' .
            md5(
                "{$request->getHost()}-{$request->getRequestUri()}-{$request->getMethod()}/",
            ) .
            $this->cacheProfile->useCacheNameSuffix($request);
    }
}
