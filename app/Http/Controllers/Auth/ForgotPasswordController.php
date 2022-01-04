<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web');
        $this->middleware('throttle:60,1');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return mixed response.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
}
