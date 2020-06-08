<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Models\Package;
use Cache;
use GitHubUtils;
use Illuminate\Console\Command;
use Str;

/**
 * This command fetches, processes and caches readmes of all or a subset of packages
 */
class CacheReadmes extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'COMMAND'
    faction:cache-readmes
    {--c|clear : Clear cached readmes}
    {--p|pattern= : Only load readmes matching the given regex pattern}
    COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load current readmes of all packages into the cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Package::all();
        $cache = Cache::store('readme');

        if ($this->option('pattern')) {
            $packages = $packages
                ->filter(
                    fn(Package $package) => preg_match(
                        $this->option('pattern'),
                        $package->shortName,
                    ),
                )
                ->values();
        }

        $numPackages = $packages->count();
        $numLength = Str::length((string) $numPackages);

        if ($this->option('clear')) {
            $this->comment('Cache will be cleared');
        }

        foreach ($packages as $i => $package) {
            $done = $this->progress(
                sprintf(
                    '%s/%s %s',
                    str_pad($i + 1, $numLength, ' ', STR_PAD_LEFT),
                    $numPackages,
                    $package->shortName,
                ),
            );

            $cache->forget("$package->shortName.commit");
            $cache->forget("$package->shortName.hash");
            $cache->forget("$package->shortName.content");

            try {
                GitHubUtils::getReadme($package);
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }
        }

        $this->line('✅ Done');
    }
}
