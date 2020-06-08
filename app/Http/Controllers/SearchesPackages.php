<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Fuse\Fuse;
use Str;

trait SearchesPackages
{
    /**
     * Search available packages for a given query
     *
     * @param string $query The query to search for
     */
    protected function searchPackages(string $query): array
    {
        $fuseList = Package::orderByDesc('last_modified')
            ->get()
            ->map(
                fn(Package $package) => [
                    'name' => $package->shortName,
                    'conciseName' => Str::unstart(
                        $package->shortName,
                        $package->group->prefix,
                    ),
                ],
            )
            ->values()
            ->toArray();

        $fuse = new Fuse($fuseList, [
            'keys' => ['name', 'conciseName'],
            'shouldSort' => true,
            'threshold' => 0.4,
            'tokenize' => true,
            'matchAllTokens' => true,
        ]);

        return array_map(fn($result) => $result['name'], $fuse->search($query));
    }
}
