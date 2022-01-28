<?php
/**
 * Admin Panel Routes
 * @see \App\Providers\RouteServiceProvider::mapAdminRoutes()
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

/*Route::name('auth.')->group(function () {
    Route::get('login', Auth\LoginController::class.'@showLoginForm')->name('login');

    Route::get('password/reset', Auth\ForgotPasswordController::class.'@showLinkRequestForm')->name('password.request');
    Route::get('password/reset/{token}', Auth\ResetPasswordController::class.'@showResetForm')->name('password.reset');
});*/

Route::middleware('auth:web-admin')->group(function() {
    Route::resource('admins', AdminController::class);
});
