<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});
