<?php

namespace App\Services\Subscription;

use App\Events\Subscription\UserSubscribed;
use App\Models\PaymentMethod;
use App\Services\Payment\Braintree;
use Throwable;
use App\Models\User;
use App\Enums\PaymentType;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use App\Exceptions\PaymentException;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;

/**
 * SubscriptionCheckout represents the checkout for the user subscription purchase.
 *
 * Checkout binds user with subscription plan, performing online payment via BrainTree.
 * User's credit card can be created in the process.
 *
 * @see \App\Exceptions\PaymentException
 */
class SubscriptionCheckout
{
    public function __construct(
        private readonly User $user,
        private readonly SubscriptionPlan $subscriptionPlan,
        private readonly Braintree $braintree
    ) {}

    public function process($paymentMethodNonce = null): Subscription
    {
        DB::beginTransaction();

        try {
            if ($paymentMethodNonce) {
                PaymentMethod::createForUser($this->user, $this->braintree, $paymentMethodNonce);
            }

            $isNewSubscription = ($this->user->activeSubscription === null);

            $paymentAmount = $this->calculatePaymentAmount();
            if ($paymentAmount > 0) {
                if ($this->user->activePaymentMethod === null) {
                    throw new PaymentException('Unable to perform payment: there is no credit card available.');
                }

                $payment = $this->user->activePaymentMethod->pay($this->braintree, $paymentAmount, PaymentType::SUBSCRIPTION);

                if (!$payment->isSuccessful()) {
                    throw new PaymentException('Unable to perform payment: '.$payment->getErrorMessage());
                }

                $subscription = $this->subscriptionPlan->subscribe($this->user);

                $subscription->payments()->attach($payment->id);
            } else {
                $subscription = $this->subscriptionPlan->subscribe($this->user);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($isNewSubscription) {
            event(new UserSubscribed($subscription));
        }

        return $subscription;
    }

    /**
     * Calculates amount to be paid for the subscription.
     * This method takes into account user's active subscription, including discount in case of
     * subscription upgrade/downgrade.
     * @return float
     */
    public function calculatePaymentAmount()
    {
        if ($this->user->activeSubscription === null) {
            return $this->subscriptionPlan->price;
        }

        $now = now()->startOfDay();

        if ($now->gte($this->user->activeSubscription->end_at)) {
            return $this->subscriptionPlan->price;
        }

        $dayDiff = $this->user->activeSubscription->end_at->diffInDays($now);

        if ($dayDiff < 1) {
            return $this->subscriptionPlan->price;
        }

        $subscriptionDayLength = $this->user->activeSubscription->end_at->diffInDays($this->user->activeSubscription->begin_at);

        $subscriptionPaidAmount = $this->user->activeSubscription->payments()
            ->where(['status' => PaymentStatus::SUCCESS])
            ->sum('amount');

        $discount = round($subscriptionPaidAmount * $dayDiff / $subscriptionDayLength, 2);

        return max($this->subscriptionPlan->price - $discount, 0);
    }
}
