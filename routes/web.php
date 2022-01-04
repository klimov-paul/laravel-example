<?php
/**
 * Web Routes
 * @see \App\Providers\RouteServiceProvider::mapWebRoutes()
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::get('/', PageController::class.'@home')->name('home');
Route::get('faq', PageController::class.'@faq')->name('help.faq');
Route::get('contact', PageController::class.'@contact')->name('help.contact');

Route::name('auth.')->group(function () {
    Route::post('login', Auth\LoginController::class.'@login')->name('login');

    Route::get('password/reset', Auth\ForgotPasswordController::class.'@showLinkRequestForm')->name('password.request');
    Route::get('password/reset/{token}', Auth\ResetPasswordController::class.'@showResetForm')->name('password.reset');
});
