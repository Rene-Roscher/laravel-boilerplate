<?php

use App\Http\Controllers\Settings\BrowserSessionController;
use App\Http\Controllers\Settings\OtherBrowserSessionsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'auth' ]
], function () {
    Route::redirect('settings', '/settings/profile')->name('settings');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('user.profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::delete('settings/avatar', [ProfileController::class, 'deleteUserAvatar'])->name('user.profile.avatar.delete');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user.password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('user.password.update');

    Route::inertia('settings/two-factor-authentication', 'settings/TwoFactorAuthentication', [
        'requiresConfirmation' => config('fortify-options.two-factor-authentication')['confirm'] ?? true,
        'confirmPassword' => config('fortify-options.two-factor-authentication')['confirmPassword'] ?? true,
        'isSetup' => fn () => auth()->user()->two_factor_pending,
    ])->name('user.two-factor-authentication.edit');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('user.appearance.edit');

    Route::get('user/browser-sessions', [BrowserSessionController::class, 'index'])
        ->name('user.browser-sessions.index');

    Route::delete('user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
        ->middleware('password.confirm')
        ->name('user.other-browser-sessions.destroy');
});
