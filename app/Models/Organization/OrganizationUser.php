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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'user_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
