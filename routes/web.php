<?php

use App\Http\Controllers\Organization\CurrentOrganizationController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/organization.php';

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'web']
], function () {
    Route::inertia('/', 'Welcome')->name('home');

    Route::group([
        'middleware' => ['auth', 'verified']
    ], function () {
        Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

        Route::put('/switch-organization/{organization}', [CurrentOrganizationController::class, 'switch'])->name('switch-organization');
    });
});
