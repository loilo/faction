<?php

namespace App\Models;

use Carbon\Carbon;
use Jenssegers\Model\Model;

/**
 * An object which expires after a set amount of time
 * It can also hold data which it exposes if it has not expired yet
 *
 * @property Carbon     $expires When the object will expire/has expired
 * @property mixed      $rawData What data was assigned to the object
 * @property-read bool  $expired Whether the object has expired
 * @property-read mixed $data    The data that was assigned to the object,
 *                               null if the object is expired already
 */
final class Perishable extends Model
{
    /**
     * Create a new perishable object
     *
     * @param string|int $expires When the object expires, e.g. "1 week"
     *                            This can be a concrete timestamp or any
     *                            string accepted by strtotime()
     * @param mixed      $data    Payload data carried by the object
     */
    public static function create($expires, $data = null): self
    {
        return new self([
            'expires' => new Carbon($expires),
            'rawData' => $data,
        ]);
    }

    public function getExpiredAttribute()
    {
        return !$this->expires->isFuture();
    }

    public function getDataAttribute()
    {
        return $this->expired ? null : $this->rawData;
    }
}
