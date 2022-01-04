<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->only('verify');
        $this->middleware('auth:web')->only(['showRequestForm']);
        $this->middleware('signed')->only('verify', 'update');
        $this->middleware('throttle:6,1')->only('verify', 'update');
    }

    public function showRequestForm()
    {
        return view('auth.email');
    }

    /**
     * Mark the non-authenticated user's email address as verified.
     * Completes the signup process.
     *
     * @param \App\Models\User $user user to be processed.
     * @return mixed response
     */
    public function verify(User $user)
    {
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            $user->confirmIdentity();

            $this->guard()->login($user);
        }

        return redirect($this->redirectPath())->with('verified', true);
    }

    /**
     * Completes changing of the user's email, processing the verification link sent via email message.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user user to be processed.
     * @return mixed response
     */
    public function update(Request $request, User $user)
    {
        /** @var $authUser \App\Models\User|null */
        $authUser = $this->guard()->user();
        if ($authUser !== null && $authUser->getKey() != $user->getKey()) {
            abort(403);
        }

        $email = $request->query('email');

        if ($user->update(['email' => $email])) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($authUser === null) {
            $this->guard()->login($user);
        }

        return redirect($this->redirectPath())->with('verified', true);
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

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route('home');
    }
}
