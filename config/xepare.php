<?php

return [

    // Permissions
    'permissions' => [
        // Custom permissions likely 'user.impersonate'
    ],

    // Models to be used for permissions
    'models' => $permissionModels = [
        \App\Models\User::class,
        \App\Models\Role::class,
        \App\Models\Permission::class,
    ],

    // Defined roles and there permissions
    'roles' => [

        // Super admin has all permissions / Gate before bypass
        \App\Enums\RoleEnum::SUPER_ADMIN->name => [],

        // Assign all permissions with all abilities
        \App\Enums\RoleEnum::ADMIN->name => collect($permissionModels)->mapWithKeys(fn($key) => [
            $key => \App\Enums\AbilityEnum::names(),
        ])->toArray(),
    ],

];
