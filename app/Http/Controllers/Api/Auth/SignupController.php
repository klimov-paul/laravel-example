<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @see \App\Http\Controllers\Auth\SignupController
 */
class SignupController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
    }

    /**
     * Registers new user as 'pending' (e.g. unable to login).
     * Account should be verified via email.
     * @see \App\Http\Controllers\Auth\EmailController::verify()
     *
     * @param Request $request HTTP request instance.
     * @return mixed response
     */
    public function signup(Request $request)
    {
        $validatedData = $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            //'verification_token' => ['required', new RecaptchaRule],
        ]);

        (new User())->signup($validatedData);

        return [
            'message' => __('signup.success'),
        ];
    }
}
