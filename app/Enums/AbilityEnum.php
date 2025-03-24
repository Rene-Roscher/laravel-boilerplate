<?php

namespace App\Enums;

use ArchTech\Enums\Names;

enum AbilityEnum
{
    use Names;

    case create;
    case read;
    case update;
    case delete;

    public function label(): string
    {
        return match ($this) {
            self::create => 'Create',
            self::read => 'Read',
            self::update => 'Update',
            self::delete => 'Delete',
        };
    }
}
