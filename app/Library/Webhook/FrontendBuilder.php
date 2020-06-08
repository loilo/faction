<?php

namespace App\Library\Webhook;

use App\Library\ResponseCache\PackageResponseCleaner;
use App\Models\Package;
use Artisan;
use Cache;
use Http;
use Log;

/**
 * The whole process of shutting down the webpage,
 * rebuilding it from scratch and booting it up again.
 */
class FrontendBuilder
{
    /**
     * @param Package|null $package The package which caused the rebuild
     *                              Used to clean according response cache entries
     */
    public function build(?Package $package = null)
    {
        Log::debug('Start to rebuild frontend');

        Log::debug('Send app into maintenance mode');
        Artisan::call('down');

        Log::debug('Re-popuplate database');

        // Refresh & seed all package-related tables
        Artisan::call('migrate:refresh', [
            '--seed' => true,
            '--force' => true,
            '--path' => [
                'database/migrations/1970_01_01_000000_create_packages_table.php',
                'database/migrations/1970_01_01_000000_create_versions_table.php',
                'database/migrations/1970_01_01_000000_create_dependencies_table.php',
            ],
        ]);

        Log::debug('Clear caches');
        Cache::store('file')->clear();

        $packageCleaner = new PackageResponseCleaner();
        $packageCleaner->clean($package);

        Cache::store('model')->clear();

        Log::debug('End maintenance mode');
        Artisan::call('up');

        Log::debug('Warm cache');
        try {
            Http::get(config('app.url'));
        } catch (\Exception $e) {
            // Ignore
        }

        Log::info('Rebuilt frontend');
    }
}
