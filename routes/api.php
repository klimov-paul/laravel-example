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

Route::apiResource('books', BookController::class)->only('index', 'show');
Route::apiResource('subscriptionPlans', SubscriptionPlanController::class)->only('index', 'show');

Route::name('me.')->prefix('me')->middleware('auth:web')->group(function () {
    Route::apiResource('favorites', Me\FavoriteController::class)->except('update');

    Route::apiResource('subscriptions', Me\SubscriptionController::class)->except('update');

    Route::apiResource('rents', Me\RentController::class)->except('update');
});

Route::post('braintree/generate-token', BraintreeController::class . '@generateClientToken')->name('braintree.generate-client-token');
