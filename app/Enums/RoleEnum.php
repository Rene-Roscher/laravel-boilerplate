<?php

namespace App\Enums;

enum RoleEnum
{
    case SUPER_ADMIN;
    case ADMIN;

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
        };
    }
}
