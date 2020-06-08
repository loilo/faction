<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SatisWrapperFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'satis-wrapper';
    }
}
