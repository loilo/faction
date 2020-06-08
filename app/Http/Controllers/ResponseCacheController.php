<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\RequestHasher;

/**
 * Control response cache behavior
 */
class ResponseCacheController extends Controller
{
    private RequestHasher $hasher;

    public function __construct(RequestHasher $hasher)
    {
        $this->hasher = $hasher;
    }

    public function purge(Request $request)
    {
        $request->setMethod('GET');
        $hash = $this->hasher->getHashFor($request);

        $deletedCount = DB::table(config('cache.stores.response.table'))
            ->where('key', 'like', "$hash%")
            ->delete();

        return response()->json([
            'cleared' => $deletedCount > 0,
        ]);
    }
}
