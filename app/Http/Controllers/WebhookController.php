<?php

namespace App\Http\Controllers;

use App\Jobs\HandleHook;
use App\Jobs\RebuildFrontend;
use Auth;
use Illuminate\Http\Response;
use Loilo\GithubWebhook\Delivery;

/**
 * Handle GitHub webhook requests
 */
class WebhookController extends Controller
{
    /**
     * Read the webhook delivery from the authenticated WebhookUser,
     * and pass it to the HandleHook job.
     */
    public function handle(): Response
    {
        /**
         * @var Delivery
         */
        $delivery = Auth::user()->delivery;

        HandleHook::dispatch($delivery)->onQueue('satis');
        RebuildFrontend::dispatch(
            RebuildFrontend::REPOSITORY,
            $delivery->payload('repository.full_name'),
        )->onQueue('frontend');

        return response($delivery->event(), 200)->header(
            'Content-Type',
            'text/plain',
        );
    }
}
