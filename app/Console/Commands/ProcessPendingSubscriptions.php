<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Payment\Braintree;
use App\Services\Subscription\SubscriptionProlonger;
use Illuminate\Console\Command;

/**
 * ProcessPendingUserSubscriptions handles pending user subscriptions.
 *
 * This command should be executed at scheduled basis once a day.
 * Subscription becomes pending in case its payment has failed during prolongation.
 * This job performs additional attempts to perform a payment for the subscription.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger::handlePending()
 * @see \App\Console\Commands\ProcessExpiredSubscriptions
 */
class ProcessPendingSubscriptions extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'subscription:process-pending';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Handles pending user subscriptions.';

    public function handle(Braintree $braintree)
    {
        $query = Subscription::query()
            ->with(['user.activeCreditCard', 'subscriptionPlan'])
            ->where('status', SubscriptionStatus::PENDING);

        $totalCount = $query->count();
        $successCount = 0;

        $this->info('Processing pending user subscriptions (' . $totalCount . ')...');

        $query->chunk(200, function ($subscriptions) use ($braintree, $successCount, $totalCount) {
            foreach ($subscriptions as $subscription) {
                (new SubscriptionProlonger($subscription, $braintree))->handlePending();
                $successCount++;
            }

            $this->line('processed ' . $successCount . '/' . $totalCount);
        });

        $this->info('...complete.');

        return 0;
    }
}
