<?php

namespace App\Events\Subscription;

use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * UserSubscribed dispatched once regular user is subscribed to some subscription plan.
 *
 * This event triggered only on user' subscription status change: it will fire only when non-subscribed user
 * receives a subscription, but will not be fired, when user is switched from one subscription plan to another one.
 *
 * @see \App\Services\Subscription\SubscriptionCheckout
 */
class UserSubscribed
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Models\Subscription just created user's subscription
     */
    public $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
