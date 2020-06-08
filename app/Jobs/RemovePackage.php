<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Satis;

/**
 * Run Satis to remove a package from the rpository
 */
class RemovePackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $package;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $package)
    {
        $this->package = full_package_name($package);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $satisResult = Satis::remove($this->package);

        if ($satisResult !== 0) {
            throw new RuntimeException(
                "Satis command failed with exit code $satisResult",
            );
        }
    }
}
