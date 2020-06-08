<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class VersionParserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'version-parser';
    }
}
