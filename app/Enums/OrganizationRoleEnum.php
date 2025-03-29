<?php

namespace App\Enums;

use ArchTech\Enums\From;
use ArchTech\Enums\Names;

enum OrganizationRoleEnum
{
    use From, Names;

    case ADMIN;
    case MEMBER;
    case VIEWER;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
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
            self::ADMIN => [
                AbilityEnum::create->name,
                AbilityEnum::read->name,
                AbilityEnum::update->name,
                AbilityEnum::delete->name,
            ],
            self::MEMBER => [
                AbilityEnum::read->name,
                AbilityEnum::update->name,
            ],
            self::VIEWER => [
                AbilityEnum::read->name,
            ],
        };
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full access to everything',
            self::MEMBER => 'Can read and update',
            self::VIEWER => 'Read only',
        };
    }

    public function toArray()
    {
        return [
            'id' => $this->name,
            'label' => $this->label(),
            'description' => $this->description(),
            'permissions' => $this->permissions(),
        ];
    }

    public static function owner(): array
    {
        return [
            'id' => 'owner',
            'label' => 'Owner',
            'description' => 'Full access',
            'permissions' => [
                AbilityEnum::create->name,
                AbilityEnum::read->name,
                AbilityEnum::update->name,
                AbilityEnum::delete->name,
            ],
        ];
    }

    public static function all(): array
    {
        return collect(self::cases())->map(function ($role) {
            return $role->toArray();
        })->toArray();
    }

}
