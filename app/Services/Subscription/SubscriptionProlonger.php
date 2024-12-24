<?php

namespace App\Services\Subscription;

use App\Enums\PaymentType;
use App\Enums\SubscriptionStatus;
use App\Events\Subscription\UserSubscriptionTerminated;
use App\Models\Payment;
use App\Models\Subscription;
use App\Notifications\Subscription\SubscriptionEnded;
use App\Notifications\Subscription\SubscriptionProlongationCancelled;
use App\Notifications\Subscription\SubscriptionProlongationNoPaymentMethodFailure;
use App\Notifications\Subscription\SubscriptionProlongationPaymentFailure;
use App\Notifications\Subscription\SubscriptionProlongationSucceed;
use LogicException;

/**
 * SubscriptionProlonger responsible for the user subscription prolongation.
 *
 * This class handles expired subscriptions.
 * It handles multiple attempts of subscription payment performing on failure.
 */
class SubscriptionProlonger
{
    const MAX_PAYMENT_ATTEMPT_COUNT = 3;

    /**
     * @var \App\Models\Subscription expired subscription to be handled
     */
    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Handles user active subscription expiration flow.
     */
    public function handleExpiration(): void
    {
        if ($this->subscription->status !== SubscriptionStatus::ACTIVE) {
            throw new LogicException('Subscription to be prolonged can be only in active status.');
        }

        $this->subscription->update(['status' => SubscriptionStatus::ARCHIVED]);

        if (! $this->subscription->is_recurrent) {
            $this->notifyEnded();

            $this->dispatchSubscriptionTerminatedEvent();

            return;
        }

        if ($this->subscription->user->activePaymentMethod === null) {
            $this->notifyNoPaymentMethod();

            $this->dispatchSubscriptionTerminatedEvent();

            return;
        }

        $this->prolong();
    }

    /**
     * Handles subscription payment re-attempt flow.
     *
     * @return \App\Models\Payment|null performed payment instance, `null` if payment can not be performed.
     */
    public function handlePending()
    {
        if ($this->subscription->status !== SubscriptionStatus::PENDING) {
            throw new LogicException('Subscription to be activated can be only in pending status.');
        }

        if ($this->subscription->user->activePaymentMethod()->first() === null) {
            $this->subscription->update(['status' => SubscriptionStatus::CANCELLED]);

            $this->notifyNoPaymentMethod();

            $this->dispatchSubscriptionTerminatedEvent();

            return null;
        }

        return $this->activate();
    }

    /**
     * Creates new subscription from the current one.
     * In case payment failure newly created subscription will be in 'pending' state.
     *
     * @return \App\Models\Subscription
     */
    protected function prolong(): Subscription
    {
        $payment = $this->subscription
            ->user
            ->activePaymentMethod
            ->pay(
                $this->subscription->subscriptionPlan->price,
                PaymentType::SUBSCRIPTION
            );

        $subscriptionMonthCount = 1;

        if ($payment->isSuccessful()) {
            $subscriptionStatusId = SubscriptionStatus::ACTIVE;
        } else {
            $subscriptionStatusId = SubscriptionStatus::PENDING;
        }

        $newSubscription = $this->subscription
            ->subscriptionPlan
            ->subscribe($this->subscription->user, $subscriptionMonthCount, ['status' => $subscriptionStatusId]);

        $newSubscription->payments()->attach($payment);

        if ($payment->isSuccessful()) {
            $this->notifyProlongationSuccess($newSubscription);
        } else {
            $this->notifyProlongationFailure($payment);
        }

        return $newSubscription;
    }

    /**
     * Attempts to activate pending subscription.
     * Subscription will be cancelled if too may attempts fail.
     *
     * @return \App\Models\Payment
     */
    protected function activate(): Payment
    {
        $payment = $this->subscription
            ->user
            ->activePaymentMethod
            ->pay($this->subscription->subscriptionPlan->price, PaymentType::SUBSCRIPTION);

        $this->subscription->payments()->attach($payment);

        if ($payment->isSuccessful()) {
            $this->subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'beginAt' => now(),
                'endAt' => now()->addMonth(),
            ]);

            $this->subscription
                ->user
                ->subscriptions()
                ->whereKeyNot($this->subscription->id)
                ->where('status', SubscriptionStatus::ACTIVE)
                ->update(['status' => SubscriptionStatus::ARCHIVED]);

            $this->notifyProlongationSuccess($this->subscription);

            return $payment;
        }

        if ($this->subscription->payments()->count() >= self::MAX_PAYMENT_ATTEMPT_COUNT) {
            $this->subscription->update(['status' => SubscriptionStatus::CANCELLED]);

            $this->notifyProlongationCancel();

            $this->dispatchSubscriptionTerminatedEvent();

            return $payment;
        }

        $this->notifyProlongationFailure($payment);

        return $payment;
    }

    /**
     * Notifies user about subscription graceful ending.
     */
    protected function notifyEnded(): void
    {
        $this->subscription->user->notify(new SubscriptionEnded($this->subscription));
    }

    /**
     * Notifies user about subscription processing failure due to payment method (credit card) absence.
     */
    protected function notifyNoPaymentMethod(): void
    {
        $this->subscription->user->notify(new SubscriptionProlongationNoPaymentMethodFailure($this->subscription));
    }

    /**
     * Notifies user about successful subscription prolongation.
     * @param \App\Models\Subscription $newSubscription new user subscription.
     */
    protected function notifyProlongationSuccess(Subscription $newSubscription): void
    {
        $newSubscription->user->notify(new SubscriptionProlongationSucceed($newSubscription));
    }

    /**
     * Notifies user about failed subscription prolongation attempt, e.g. another attempt will be made.
     * @param \App\Models\Payment $payment failed payment instance.
     */
    protected function notifyProlongationFailure(Payment $payment): void
    {
        $this->subscription->user->notify(new SubscriptionProlongationPaymentFailure($this->subscription, $payment));
    }

    /**
     * Notifies user about subscription prolongation cancel, e.g. no more prolongation attempts
     * will be performed.
     */
    protected function notifyProlongationCancel(): void
    {
        $this->subscription->user->notify(new SubscriptionProlongationCancelled($this->subscription));
    }

    /**
     * Dispatches user's subscription terminated event, indicating user is no longer subscribed to any plan.
     */
    protected function dispatchSubscriptionTerminatedEvent(): void
    {
        event(new UserSubscriptionTerminated($this->subscription));
    }
}
