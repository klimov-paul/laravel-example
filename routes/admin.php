<?php
/**
 * Admin Panle Routes
 * @see \App\Providers\RouteServiceProvider::mapAdminRoutes()
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::get('/', PageController::class . '@home')->name('home');
