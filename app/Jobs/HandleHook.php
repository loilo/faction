<?php

namespace App\Jobs;

use App\Library\Webhook\HookHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Loilo\GithubWebhook\Delivery;

/**
 * Handle an incoming webhook delivery
 */
class HandleHook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Delivery $delivery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery)
    {
        Log::info('Dispatched hook handling job');
        $this->delivery = $delivery;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $handler = new HookHandler($this->delivery);
        $handler->execute();
    }
}
