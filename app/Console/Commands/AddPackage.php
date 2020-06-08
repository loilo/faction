<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Jobs;
use Illuminate\Console\Command;
use Satis;

/**
 * This command adds a package to the repository by scanning a provided GitHub repo
 */
class AddPackage extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'COMMAND'
    faction:add-package
    {repo : The GitHub repository to scan}
    {--i|immediate : Add package synchronously instead of pushing it to the queue}
    COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a package to the Composer repository';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $repo = full_repo_name($this->argument('repo'));
        $exists = Satis::readExistingPackages()->containsRepo($repo);

        if ($exists) {
            $this->warn(
                sprintf(
                    'Did not add repository <fg=cyan>%s</>, it\'s already registered.',
                    $repo,
                ),
            );

            return 0;
        }

        if ($this->option('immediate')) {
            // Add the repo immediately instead of queuing the request
            Jobs\AddPackage::dispatchNow($repo);

            $done = $this->progress('Rebuild frontend');
            try {
                Jobs\RebuildFrontend::dispatchNow();
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }

            $done = $this->progress('Fetch readme');
            try {
                Jobs\GetReadme::dispatchNow($repo);
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }

            $this->line('✅ Done');
        } else {
            Jobs\AddPackage::dispatch($repo)->onQueue('satis');
            Jobs\RebuildFrontend::dispatch(
                Jobs\RebuildFrontend::REPOSITORY,
                $repo,
            )->onQueue('frontend');
            Jobs\GetReadme::dispatch($repo)->onQueue('readme');

            $this->line('✅ Pushed package-adding job to the queue');
        }
    }
}
