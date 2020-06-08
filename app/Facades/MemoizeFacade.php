<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class MemoizeFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'memoize';
    }
}
