<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionProlonger;
use Illuminate\Console\Command;

/**
 * ProcessExpiredUserSubscriptions handles expired user subscriptions.
 *
 * This command should be executed at scheduled basis at least once a day.
 * Payment re-attempts are perform via `ProcessPendingSubscriptions`, if necessary.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger::handleExpiration()
 * @see \App\Console\Commands\ProcessPendingSubscriptions
 */
class ProcessExpiredSubscriptions extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'subscription:process-expired';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Handles expired user subscriptions.';

    public function handle()
    {
        $query = Subscription::query()
            ->with(['user.activeCreditCard', 'subscriptionPlan'])
            ->where('status', SubscriptionStatus::ACTIVE)
            ->expired();

        $totalCount = $query->count();
        $successCount = 0;

        $this->info('Processing expired user subscriptions (' . $totalCount . ')...');

        $query->chunk(200, function ($subscriptions) use ($successCount, $totalCount) {
            foreach ($subscriptions as $subscription) {
                (new SubscriptionProlonger($subscription))->handleExpiration();
                $successCount++;
            }

            $this->line('processed ' . $successCount . '/' . $totalCount);
        });

        $this->info('...complete.');

        return 0;
    }
}
