<?php

namespace App\Enums;

enum TokenAbility: string
{
    /**
     * Basic CRUD abilities for API tokens.
     * Extend this enum with your application-specific abilities as needed.
     */
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';

    /**
     * Get the description for the ability.
     */
    public function description(): string
    {
        return match ($this) {
            self::CREATE => 'Create new resources',
            self::READ => 'Read and view resources',
            self::UPDATE => 'Update existing resources',
            self::DELETE => 'Delete resources',
        };
    }

    /**
     * Get all abilities with their descriptions for UI display.
     */
    public static function withDescriptions(): array
    {
        $abilities = [];
        foreach (self::cases() as $ability) {
            $abilities[$ability->value] = $ability->description();
        }

        return $abilities;
    }

    /**
     * Get all ability values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a given string is a valid ability.
     */
    public static function isValid(string $ability): bool
    {
        return in_array($ability, self::values(), true);
    }

    /**
     * Get ability from string value.
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
