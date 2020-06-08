<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use FS;

/**
 * Ensure that the SQLite database file exists before connecting to it.
 */
class SqliteServiceProvider extends ServiceProvider
{
    /**
     * Create database file if to does not exist.
     *
     * @return void
     */
    public function boot()
    {
        $dbPath = config('database.connections.sqlite.database');
        if (!FS::exists($dbPath)) {
            info(sprintf('Create SQLite database file at "%s"', $dbPath));
            FS::touch($dbPath);
        }
    }
}
