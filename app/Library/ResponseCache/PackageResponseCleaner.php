<?php

namespace App\Library\ResponseCache;

use App\Models\Package;
use DB;
use Str;

/**
 * This class can clean out the response cache selectively, by only removing
 * entries that are in some relation with the package.
 */
class PackageResponseCleaner
{
    /**
     * @param Package|null $package The package which caused the rebuild
     *                              If `null` is passed, all relations pages will be cleared
     * @return void
     */
    public function clean(?Package $package)
    {
        $query = DB::table(config('cache.stores.response.table'))
            // Clear packages overview
            ->where('key', 'like', '%.packages');

        if (is_null($package)) {
            // Clear all relations pages
            $query->orWhere('key', 'like', '%.relations.package.%');
        } else {
            // Clear all package detail pages of the cleaned up package
            $query->orWhere('key', 'like', "%.package.$package->shortName");

            // Clear relations page of dependencies
            foreach ($package->allDependencies as $dependency) {
                if (
                    Str::startsWith(
                        $dependency->dependency,
                        config('app.repository.package_vendor') . '/',
                    )
                ) {
                    $name = short_package_name($dependency->dependency);
                    $query->orWhere(
                        'key',
                        'like',
                        "%.relations.package.{$name}",
                    );
                }
            }

            // Clear relations page of dependants
            foreach ($package->dependants as $dependant) {
                $name = short_package_name($dependant->package);
                $query->orWhere('key', 'like', "%.relations.package.$name");
            }
        }

        return $query->delete();
    }
}
