<?php

namespace App\Jobs;

use App\Library\Webhook\FrontendBuilder;
use App\Library\Webhook\QueueClearer;
use App\Models\Package;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

/**
 * Rebuild the app's frontend based on current package data
 */
class RebuildFrontend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?string $origin;
    private int $originType;

    const PACKAGE = 0;
    const REPOSITORY = 1;

    /**
     * Create a new job instance.
     *
     * @param int         $originType What type of entity the provided $origin is
     *                                May be self::PACKAGE or self::REPOSITORY.
     * @param string|null $origin     The name of the repository or package
     *                                causing the rebuild. Knowing this helps
     *                                to properly invalidate the cache and only
     *                                drop certain pages instead of wiping the
     *                                response cache completely.
     */
    public function __construct(int $originType = 0, ?string $origin = null)
    {
        $this->originType = $originType;
        $this->origin = $origin;

        Log::info('Dispatched frontend rebuild job');

        // Clear other frontend jobs from the queue.
        // This is basically a debouncer.
        $clearer = new QueueClearer('frontend');
        if ($clearer->doesDefaultQueueUseDatabase()) {
            $deleted = $clearer->clearUnreservedPendingJobs();

            if ($deleted > 0) {
                Log::info("Cleared $deleted pending frontend rebuild jobs");
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $builder = new FrontendBuilder();

        if (!is_null($this->origin)) {
            if ($this->originType === $this::REPOSITORY) {
                $package = Package::findByRepo($this->origin);
            } else {
                $package = Package::findByName($this->origin);
            }
        } else {
            $package = null;
        }

        $builder->build($package);

        // Restart the queue
        // This is important to clear out in-memory caches of the queue
        // after clearing the persistent caches through the rebuild commend.
        Log::debug('Restart queue');
        Artisan::call('queue:restart');
    }
}
