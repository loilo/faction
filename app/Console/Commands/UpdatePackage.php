<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Jobs;
use App\Models\Package;
use Illuminate\Console\Command;

class UpdatePackage extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'COMMAND'
    faction:update-package
    {package : The package to update}
    {--i|immediate : Update package synchronously instead of pushing it to the queue}
    COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a package in the repository';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $package = Package::findByName($this->argument('package'));

        if (is_null($package)) {
            $this->error(
                sprintf(
                    'Could not find package with name <bg=red;fg=yellow>%s</>',
                    $this->argument('package'),
                ),
            );
            return 1;
        }

        if ($this->option('immediate')) {
            // Update the package immediately instead of queuing the request

            Jobs\UpdatePackage::dispatchNow($package->name);

            $done = $this->progress('Rebuild frontend');
            try {
                Jobs\RebuildFrontend::dispatchNow(
                    Jobs\RebuildFrontend::PACKAGE,
                    $package->name,
                );
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }

            $done = $this->progress('Update readme');
            try {
                Jobs\GetReadme::dispatchNow($package->repo);
                $done(true);
            } catch (\Exception $e) {
                $done(false);
                throw $e;
            }

            $this->line('✅ Done');
        } else {
            Jobs\UpdatePackage::dispatch($package->name)->onQueue('satis');
            Jobs\RebuildFrontend::dispatch(
                Jobs\RebuildFrontend::PACKAGE,
                $package->name,
            )->onQueue('frontend');
            Jobs\GetReadme::dispatch($package->repo)->onQueue('readme');

            $this->line('✅ Pushed package-updating job to the queue');
        }
    }
}
