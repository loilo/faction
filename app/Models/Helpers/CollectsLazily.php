<?php

namespace App\Models\Helpers;

use Illuminate\Support\Traits\ForwardsCalls;

/**
 * Utilizes a user-implemented collect() method to get a collection of objects
 * on which static methods are executed.
 * This can be used to initialize large/expensive collections on-demand.
 */
trait CollectsLazily
{
    use ForwardsCalls;

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $collection = $this->collect();

        if ($method === 'all') {
            return $collection;
        }

        return $this->forwardCallTo($collection, $method, $parameters);
    }
}
