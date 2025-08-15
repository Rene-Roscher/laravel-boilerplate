<?php

namespace App\Models\Organization;

use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperOrganizationUser
 */
class OrganizationUser extends Pivot
{
    use HasShortflakePrimary;
}
