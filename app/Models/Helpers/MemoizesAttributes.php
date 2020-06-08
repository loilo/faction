<?php

namespace App\Models\Helpers;

use Cache;

/**
 * Provide a method to read attributes from cache
 */
trait MemoizesAttributes
{
    private $cache = [];

    /**
     * Get an attribute value, read it from cache if necessary.
     * Cached values are shared between multiple instances of the same model
     * depending on their primary key value.
     *
     * @param string $key The attribute name to receive
     * @return mixed
     */
    protected function getCachedValue(string $key)
    {
        if (is_array($this->memoize) && in_array($key, $this->memoize)) {
            $primaryKeyValue = $this->getKey();

            if (is_null($primaryKeyValue)) {
                if (!isset($this->cache[$key])) {
                    $value = parent::__get($key);
                    $this->cache[$key] = $value;
                }

                return $this->cache[$key];
            } else {
                $cache = Cache::store('model');
                $cacheKey = get_class($this) . ":$primaryKeyValue:$key";

                if (!$cache->has($cacheKey)) {
                    $value = parent::__get($key);
                    $cache->set($cacheKey, $value);
                    return $value;
                }

                return $cache->get($cacheKey);
            }
        } else {
            return parent::__get($key);
        }
    }
}
