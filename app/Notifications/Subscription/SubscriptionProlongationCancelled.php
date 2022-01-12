<?php

namespace App\Notifications\Subscription;

use App\Models\Subscription;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * SubscriptionProlongationCancelled is sent to the user in case his subscription can not be prolonged.
 *
 * This notification is sent once all allowed payment attempts are exhausted.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger
 */
class SubscriptionProlongationCancelled extends Notification
{
    /**
     * @var \App\Models\Subscription user subscription, created as a prolongation.
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
            ->subject(__('Your subscription at :appName has been cancelled', ['appName' => $appName]))
            ->line(__('We are unable to perform a payment for your subscription.'))
            ->action(
                __('Goto :appName', ['appName' => $appName]),
                route('me.profile.show', [], true)
            );
    }
}
