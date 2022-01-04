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
