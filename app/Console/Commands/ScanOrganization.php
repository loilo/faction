<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Jobs;
use Illuminate\Console\Command;
use Satis;

/**
 * Initialize the repository from scratch, scanning all packages in the
 * configured GitHub organization
 * Depending on the number of GitHub repos, this may take a while
 */
class InitializeRepository extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faction:scan-org';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize/reset the Faction repository';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line(
            'Scanning all organization repositories. This might take a while.',
        );

        $exitCode = Satis::rescan();
        Jobs\RebuildFrontend::dispatchNow();

        if ($exitCode === 0) {
            $this->line('✅ Successfully initialized packages');
        } else {
            $this->error('Could not initialize packages');
        }

        return $exitCode;
    }
}
