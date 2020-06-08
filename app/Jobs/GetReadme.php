<?php

namespace App\Jobs;

use App\Models\Package;
use GitHubUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fetch and cache the readme of a package
 */
class GetReadme implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $repo;

    /**
     * Create a new job instance.
     *
     * @param string $repo The repository to fetch the readme from
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
        GitHubUtils::getReadme(Package::findByRepo($this->repo));
    }
}
