<?php

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    /**
     * Handle received Stripe webhooks.
     */
    public function handle(WebhookReceived $event): void
    {
        logger(json_encode($event->payload));
        //        if ($event->payload['type'] === 'invoice.payment_succeeded') {
        //            logger($event->payload);
        //        }
    }
}
