<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserStatus;
use App\Extensions\Auth\ThrottlesLogins;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @see \App\Http\Controllers\Auth\LoginController
 */
class LoginController extends Controller
{
    use ThrottlesLogins;

    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('guest:web')->except(['logout']);
        $this->middleware('auth:web')->only(['logout']);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed response.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $this->validate($request, [
            $this->username() => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // throttle the login attempts for this application
        // key this by the username and the IP address of the client making these requests into this application
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->guard()->attempt($credentials, $request->filled('remember'))) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse();
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed response
     */
    protected function sendLoginResponse(Request $request)
    {
        /** @var $user \App\Models\User */
        $user = $this->guard()->user();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $this->clearLoginAttempts($request);

        if ($user->status !== UserStatus::ACTIVE) {
            $this->guard()->logout();

            return $this->sendFailedLoginResponse(__('This account is inactive.'));
        }

        return response()->json([
            'message' => __('You have successfully logged in.'),
            'redirect' => route('home'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  string|null  $message
     * @return mixed response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(?string $message = null)
    {
        if ($message === null) {
            $message = __('auth.failed');
        }

        throw ValidationException::withMessages([
            $this->username() => [$message],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('You have been logged out successfully.'),
            ], 202);
        }

        return redirect(route('home'));
    }

    /**
     * Get the login username to be used by the controller.
     * @see \App\Extensions\Auth\ThrottlesLogins
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }
}
