<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

/**
 * @see \App\Http\Controllers\Api\Auth\LoginController
 */
class LoginController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
}
