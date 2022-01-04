<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Notification sent to the new signed up user, finalizing the registration process.
 *
 * Notification includes:
 *
 * - autogenerated user password value
 * - email verification link
 *
 * @see \Illuminate\Auth\Notifications\VerifyEmail
 */
class UserSignedUp extends Notification
{
    /**
     * @var string merchant's raw (not encrypted) password
     */
    public $password;

    /**
     * Create a notification instance.
     *
     * @param  string  $password merchant's raw (not encrypted) password
     * @return void
     */
    public function __construct(string $password)
    {
        parent::__construct();

        $this->password = $password;
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
     * @param \App\Models\User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject(__('notification.user.signup.subject', ['appName' => $appName]))
            ->line(__('notification.user.signup.your_password_is'))
            ->line($this->password)
            ->line(__('notification.user.signup.click_to_verify_email'))
            ->action(
                __('auth.verify_email'),
                $this->verificationUrl($notifiable)
            )
            ->line(__('notification.user.signup.if_not_signed_up'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  \App\Models\User|mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'auth.email.verify',
            Carbon::now()->addMinutes(60),
            [
                '_locale' => app()->getLocale(),
                'user' => $notifiable->getKey(),
            ]
        );
    }
}