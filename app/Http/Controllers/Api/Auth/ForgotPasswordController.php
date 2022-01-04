<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
        $this->middleware('throttle:60,1');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed response
     */
    public function sendResetLinkEmail(Request $request)
    {
        /*$request->validate([
            'verification_token' => ['required', new RecaptchaRule],
        ]);*/

        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        ResetPassword::toMailUsing([get_class($this), 'createResetPasswordMail']);

        $message = $this->broker()->sendResetLink($credentials);

        if ($message === Password::RESET_LINK_SENT) {
            return $this->sendResetLinkResponse($request, $message);
        }

        if ($message === Password::INVALID_USER) {
            // ensure form has no indication of 'exist'/'not exits' email
            usleep(rand(100000, 1000000)); // emulate SMTP delay

            return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
        }

        return $this->sendResetLinkFailedResponse($request, $message);
    }

    protected function sendResetLinkResponse(Request $request, $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __($message),
            ]);
        }

        return back()->with('status', __($message));
    }

    protected function sendResetLinkFailedResponse(Request $request, $message)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [__($message)],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($message)]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('users');
    }

    /**
     * Creates mail message with reset password link.
     * @see \Illuminate\Auth\Notifications\ResetPassword::toMail()
     * @see __construct()
     *
     * @param  \App\Models\User  $notifiable
     * @param  string  $token
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public static function createResetPasswordMail(User $notifiable, string $token): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notification.reset_password.subject'))
            ->line(__('notification.reset_password.receive_this_because_password_reset'))
            ->action(
                __('auth.reset_password'),
                route('auth.password.reset', ['token' => $token, 'email' => $notifiable->email], true)
            )
            ->line(__('notification.reset_password.if_not_requested_password_reset'));
    }
}
