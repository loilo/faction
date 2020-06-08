<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Jobs;
use Illuminate\Console\Command;

/**
 * This command rebuilds the Faction frontend by re-populating the database
 * and clearing all relevant caches
 */
class RebuildFrontend extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'COMMAND'
    faction:rebuild-frontend
    {--i|immediate : Add package synchronously instead of pushing it to the queue}
    COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rewire the frontend (e.g. link package metadata, regenerate database, ...)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('immediate')) {
            // Rebuild frontend immediately instead of queuing the request
            $done = $this->progress('Rebuild frontend');
            try {
                Jobs\RebuildFrontend::dispatchNow();
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }

            $this->line('✅ Done');
        } else {
            Jobs\RebuildFrontend::dispatch()->onQueue('frontend');

            $this->line('✅ Pushed frontend-rebuilding job to the queue');
        }
    }
}
