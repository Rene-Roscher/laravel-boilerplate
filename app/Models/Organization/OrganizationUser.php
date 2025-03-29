<?php

namespace App\Models\Organization;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperOrganizationUser
 */
class OrganizationUser extends Pivot
{
    use HasUuids;
}
