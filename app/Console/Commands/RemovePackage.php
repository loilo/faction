<?php

namespace App\Console\Commands;

use App\Console\PrintsProgress;
use App\Jobs;
use App\Models\Package;
use Illuminate\Console\Command;

class RemovePackage extends Command
{
    use PrintsProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'COMMAND'
    faction:remove-package
    {package : The package to remove}
    {--i|immediate : Remove package synchronously instead of pushing it to the queue}
    COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a package from the repository';

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
            // Remove the package immediately instead of queuing the request

            Jobs\RemovePackage::dispatchNow($package->name);

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

            $this->line('✅ Done');
        } else {
            Jobs\RemovePackage::dispatch($package->name)->onQueue('satis');
            Jobs\RebuildFrontend::dispatch(
                Jobs\RebuildFrontend::PACKAGE,
                $package->name,
            )->onQueue('frontend');

            $this->line('✅ Pushed package-removing job to the queue');
        }
    }
}
