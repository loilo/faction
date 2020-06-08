<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class XFilesystemFacade extends Facade
{
    const PHP_ALLOW_CACHED = 0;
    const PHP_INVALIDATE_CACHE = 1;
    const PHP_FORCE_INVALIDATE_CACHE = 2;

    const CSV_DUMP_PLAIN = 0;
    const CSV_DUMP_STRUCTURED = 1;
    const CSV_DUMP_DETECT = 3;

    const PARSE_ARRAY = 0;
    const PARSE_ASSOC = 1;
    const PARSE_OBJECT = 2;

    protected static function getFacadeAccessor()
    {
        return 'x-filesystem';
    }
}
