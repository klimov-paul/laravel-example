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
    Route::get('login', Auth\LoginController::class.'@showLoginForm')->name('login');

    Route::get('password/reset', Auth\ForgotPasswordController::class.'@showLinkRequestForm')->name('password.request');
    Route::get('password/reset/{token}', Auth\ResetPasswordController::class.'@showResetForm')->name('password.reset');

    Route::get('signup', Auth\SignupController::class.'@showSignupForm')->name('signup');

    Route::get('email/verify/{user}', Auth\EmailController::class.'@verify')->name('email.verify');
    Route::get('email', Auth\EmailController::class.'@showRequestForm')->name('email.form');
});

Route::get('books', BookController::class.'@index')->name('books.index');
Route::get('rents', RentController::class.'@index')->name('rents.index');

Route::name('me.')->prefix('subscriptions')->group(function () {
    Route::name('subscriptions.')->group(function () {
        Route::get('purchase', Me\SubscriptionController::class . '@purchase')->name('purchase');
    });
});
