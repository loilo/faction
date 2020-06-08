<?php

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 * A Model which caches get*Attribute() accessor results for all
 * attributes listed in its $memoize property.
 */
class MemoizingModel extends Model
{
    use MemoizesAttributes;

    /**
     * @inheritdoc
     */
    public function __get($key)
    {
        return $this->getCachedValue($key);
    }
}
