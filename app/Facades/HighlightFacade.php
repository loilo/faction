<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HighlightFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'highlight';
    }
}
