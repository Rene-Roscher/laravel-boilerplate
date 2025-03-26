<?php

namespace App\Enums;

use ArchTech\Enums\From;

enum OrganizationRoleEnum
{
    use From;

    case ADMIN;
    case MANAGER;
    case MEMBER;
    case VIEWER;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::MEMBER => 'Team-Member',
            self::VIEWER => 'Viewer',
        };
    }

    /**
     * Get the permissions for the organization role.
     *
     * @return array
     */
    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN, self::MANAGER => [
                AbilityEnum::create,
                AbilityEnum::read,
                AbilityEnum::update,
                AbilityEnum::delete,
            ],
            self::MEMBER => [
                AbilityEnum::read,
                AbilityEnum::update,
            ],
            self::VIEWER => [
                AbilityEnum::read,
            ],
        };
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full access',
            self::MANAGER => 'Full access except delete',
            self::MEMBER => 'Can read and update',
            self::VIEWER => 'Read only',
        };
    }

    public function toArray()
    {
        return [
            'label' => $this->label(),
            'description' => $this->description(),
            'permissions' => $this->permissions(),
        ];
    }

    public static function owner(): array
    {
        return [
            'label' => 'Owner',
            'description' => 'Full access',
            'permissions' => [
                AbilityEnum::create,
                AbilityEnum::read,
                AbilityEnum::update,
                AbilityEnum::delete,
            ],
        ];
    }

}
