<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

/**
 * @see \App\Http\Controllers\Api\Auth\SignupController
 */
class SignupController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
    }

    public function showSignupForm()
    {
        return view('auth.signup');
    }
}
