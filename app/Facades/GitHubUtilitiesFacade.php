<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class GitHubUtilitiesFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'github-utilities';
    }
}
