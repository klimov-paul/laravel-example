<?php

namespace App\Notifications\Subscription;

use App\Models\Subscription;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * SubscriptionProlongationNoPaymentMethodFailure is sent to the user in case he has no payment method (credit card)
 * for subscription prolongation.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger
 */
class SubscriptionProlongationNoPaymentMethodFailure extends Notification
{
    /**
     * @var \App\Models\Subscription user subscription, which has ended.
     */
    public $subscription;

    public function __construct(Subscription $subscription)
    {
        parent::__construct();

        $this->subscription = $subscription;
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
            ->subject(__('Your subscription at :appName has ended', ['appName' => $appName]))
            ->line(__('Your subscription has ended and some services may become unavailable.'))
            ->line(__('We are unable to prolong your subscription, because you have no active credit card attached to your account.'))
            ->action(
                __('Goto :appName', ['appName' => $appName]),
                route('me.profile.show', [], true)
            );
    }
}
