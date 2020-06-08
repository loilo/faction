<?php

namespace App\Models;

use Loilo\GithubWebhook\Delivery;

/**
 * Represents a user authenticated to respond to a
 * webhook and carrying a delivery.
 *
 * @property Delivery $delivery
 */
class WebhookUser extends AnonymousUser
{
    public function __construct(Delivery $delivery)
    {
        parent::__construct([
            'delivery' => $delivery,
        ]);
    }
}
