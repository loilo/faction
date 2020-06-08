<?php

namespace App\Models\Helpers;

use Jenssegers\Model\Model;

/**
 * An offline Model which caches get*Attribute() accessor results
 * for all attributes listed in its $memoize property.
 */
class MemoizingOfflineModel extends Model
{
    use MemoizesAttributes;

    /**
     * Get the value of the model's primary key.
     * Can be overridden to properly share cached attributes
     * between model instances.
     *
     * @return mixed
     */
    public function getKey()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function __get($key)
    {
        return $this->getCachedValue($key);
    }
}
