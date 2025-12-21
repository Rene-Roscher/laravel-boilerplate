<?php

namespace App\Models;

use Kra8\Snowflake\HasShortflakePrimary;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @mixin IdeHelperRole
 */
class Role extends SpatieRole
{
    use HasShortflakePrimary;
}
