<?php

namespace App\Models;

use Kra8\Snowflake\HasShortflakePrimary;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @mixin IdeHelperPermission
 */
class Permission extends SpatiePermission
{
    use HasShortflakePrimary;
}
