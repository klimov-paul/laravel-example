<?php

namespace App\Notifications\Subscription;

use App\Models\Subscription;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * SubscriptionProlongationSucceed is sent to the user, once his subscription has been prolonged.
 *
 * @see \App\Services\Subscription\SubscriptionProlonger
 */
class SubscriptionProlongationSucceed extends Notification
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
     * Get the notification's channels.
     *
     * @param  \App\Models\User|mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  \App\Models\User|mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Your subscription has been prolonged'))
            ->line(__('Your subscription plan is:'))
            ->line($this->subscription->subscriptionPlan->name)
            ->action(
                __('Goto :appName', ['appName' => config('app.name')]),
                route('me.profile.show', [], true)
            );
    }
}
