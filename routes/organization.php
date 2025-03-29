<?php

use App\Http\Controllers\Organization\CurrentOrganizationController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Organization\OrganizationInvitationController;
use App\Http\Controllers\Organization\OrganizationUserController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'web']
], function () {
    Route::group([
        'prefix' => 'org',
        'middleware' => ['auth', 'verified']
    ], function () {
        Route::put('/switch', [CurrentOrganizationController::class, 'switch'])->name('switch-organization');
        Route::get('/invitations/{invitation}', [OrganizationInvitationController::class, 'accept'])
            ->middleware(['signed'])->name('invitations.accept');

        Route::prefix('{organization}')->group(function () {
            Route::put('/switch', [CurrentOrganizationController::class, 'switch'])->name('switch-organization');

            Route::get('/', [OrganizationController::class, 'show'])->name('organization.show');
            Route::patch('/', [OrganizationController::class, 'update'])->name('organization.update');
            Route::delete('/', [OrganizationController::class, 'deleteAvatar'])->name('organization.avatar.delete');

            Route::get('users', [OrganizationUserController::class, 'show'])->name('organization.users.show');

            Route::post('/invite-user', [OrganizationUserController::class, 'inviteUser'])->name('organization.user.invite');
            Route::post('/detach-user', [OrganizationUserController::class, 'detachUser'])->name('organization.user.detach');
            Route::patch('/update-user', [OrganizationUserController::class, 'updateUser'])->name('organization.user.update');
            Route::post('/delete-invitation', [OrganizationUserController::class, 'deleteInvitation'])->name('organization.user.invitation.delete');
        });

        Route::post('/', [OrganizationController::class, 'store'])->name('organization.store');
    });
});
