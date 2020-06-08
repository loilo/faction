<?php

namespace App\Library\Webhook;

use DB;
use RuntimeException;

/**
 * A tool to check for pending jobs in a (database-backed) queue and clear them out
 */
class QueueClearer
{
    private string $queue;

    /**
     * @param string $queue The queue name to clear out
     */
    public function __construct(string $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Check whether the database table holds any pending jobs
     *
     * @throws RuntimeException If the queue is not running on the database driver
     */
    public function hasPendingJobs(): bool
    {
        $this->enforceDatabaseQueue();

        return DB::table($this->getDefaultQueueConfig()['table'])
            ->where('queue', $this->queue)
            ->count() > 0;
    }

    /**
     * Check whether the database table holds any pending jobs that are not yet reserved for execution
     *
     * @throws RuntimeException If the default queue is not running on the database driver
     */
    public function hasUnreservedPendingJobs(): bool
    {
        $this->enforceDatabaseQueue();

        return DB::table($this->getDefaultQueueConfig()['table'])
            ->where('queue', $this->queue)
            ->where('reserved_at', null)
            ->count() > 0;
    }

    /**
     * Remove all unreserved pending jobs from the database
     *
     * @throws RuntimeException If the default queue is not running on the database driver
     */
    public function clearUnreservedPendingJobs(): bool
    {
        $this->enforceDatabaseQueue();

        return DB::table($this->getDefaultQueueConfig()['table'])
            ->where('queue', $this->queue)
            ->where('reserved_at', null)
            ->delete();
    }

    /**
     * Get the configuration of the default queue
     */
    public function getDefaultQueueConfig(): array
    {
        return config(sprintf('queue.connections.%s', config('queue.default')));
    }

    /**
     * Check whether the default queue uses the database
     */
    public function doesDefaultQueueUseDatabase(): bool
    {
        return $this->getDefaultQueueConfig()['driver'] === 'database';
    }

    /**
     * @throws RuntimeException If the default queue is not running on the database driver
     */
    private function enforceDatabaseQueue()
    {
        if (!$this->doesDefaultQueueUseDatabase()) {
            throw new RuntimeException(
                'Cannot check for pending jobs when not running on a database queue',
            );
        }
    }
}
