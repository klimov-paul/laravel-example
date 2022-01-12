<?php

namespace App\Notifications\Subscription;

use App\Models\Payment;
use App\Models\Subscription;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * SubscriptionProlongationPaymentFailure is sent to the user on subscription payment failure.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger
 */
class SubscriptionProlongationPaymentFailure extends Notification
{
    /**
     * @var \App\Models\Subscription user subscription, which has ended.
     */
    public $subscription;

    /**
     * @var \App\Models\Payment failed payment instance.
     */
    public $payment;

    public function __construct(Subscription $subscription, Payment $payment)
    {
        parent::__construct();

        $this->subscription = $subscription;
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject(__('Payment for subscription at :appName failed', ['appName' => $appName]))
            ->line(__('Payment for your subscription has failed due to error:'))
            ->line($this->payment->getErrorMessage())
            ->line(__('Please make sure you are using correct credit card with enough funds.'))
            ->action(
                __('Goto :appName', ['appName' => $appName]),
                route('me.profile.show', [], true)
            );
    }
}
