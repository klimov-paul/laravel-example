<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Rules\PasswordRule;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
        $this->middleware('throttle:60,1');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed response
     */
    public function reset(Request $request)
    {
        $credentials = $this->validate($request, [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', new PasswordRule, 'confirmed'],
        ]);

        $message = $this->broker()->reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        if ($message === Password::PASSWORD_RESET) {
            /** @var \App\Models\User $user */
            $user = $this->guard()->user();
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();

                if ($user->status === UserStatus::PENDING) {
                    $user->confirmIdentity();
                }
            }

            return $this->sendResetResponse($request, $message);
        }

        return $this->sendResetFailedResponse($request, $message);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    protected function sendResetResponse(Request $request, $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __($message),
                'redirect' => route('home'),
                'data' => [
                    'user' => new UserResource($this->guard()->user()),
                ],
            ]);
        }

        return redirect(route('home'))
            ->with('status', __($message));
    }

    protected function sendResetFailedResponse(Request $request, $message)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [__($message)],
            ]);
        }

        return redirect()->back()
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
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }
}
