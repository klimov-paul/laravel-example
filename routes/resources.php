<?php
/**
 * Resources (files) Routes
 * @see \App\Providers\RouteServiceProvider::mapResourceRoutes()
 */

namespace App\Http\Controllers\Resources;

use Illuminate\Support\Facades\Route;

Route::get('robots.txt', Robots::class);

Route::get('sitemap.xml', Sitemap::class);

Route::get('manifest.webmanifest', Manifest::class)->name('manifest.webmanifest');
