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
 * Run Satis to add a GitHub repository (and its possibly contained package)
 * to the Composer repository.
 */
class AddPackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $repo)
    {
        $this->repo = full_repo_name($repo);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $satisResult = Satis::updateRepo(
            $this->repo,
            config('app.repository.github_org'),
            config('app.repository.package_vendor'),
        );

        if ($satisResult !== 0) {
            throw new RuntimeException(
                "Satis command failed with exit code $satisResult",
            );
        }
    }
}
