<?php
/**
 * API routes.
 * @see \App\Providers\RouteServiceProvider::mapApiRoutes()
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::name('auth.')->group(function () {
    Route::post('login', Auth\LoginController::class.'@login')->name('login');
    Route::post('logout', Auth\LoginController::class.'@logout')->name('logout');

    Route::post('password/email', Auth\ForgotPasswordController::class.'@sendResetLinkEmail')->name('password.email');
    Route::post('password/reset', Auth\ResetPasswordController::class.'@reset')->name('password.reset');

    Route::post('signup', Auth\SignupController::class.'@signup')->name('signup');
});
