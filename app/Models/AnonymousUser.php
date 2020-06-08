<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Represents an unknown user.
 * This is needed because Laravel authentication
 * requires to always return a user object on success.
 */
class AnonymousUser extends Authenticatable
{
    protected $guarded = [];

    public function __construct(array $data = [])
    {
        parent::__construct(array_merge(['name' => 'anonymous'], $data));
    }

    public function getKeyName()
    {
        return 'name';
    }
}
