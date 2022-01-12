<?php

namespace App\Events\Subscription;

use App\Models\Subscription;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * UserSubscriptionTerminated dispatched once user's subscription has been terminated.
 *
 * This event fires when user's status is switched from 'subscribed' to 'unsubscribed' by some reason.
 * It covers normal subscription expiration as well as subscription cancellation.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger
 */
class UserSubscriptionTerminated
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Models\Subscription terminated user's subscription
     */
    public $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
