<?php

namespace App\Models;

use Kra8\Snowflake\HasShortflakePrimary;
use Spatie\LaravelPasskeys\Models\Passkey as SpatiePasskey;

class Passkey extends SpatiePasskey
{
    use HasShortflakePrimary;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'id' => 'string',
        'authenticatable_id' => 'string',
        'data' => 'array',
        'transports' => 'array',
        'backup_eligible' => 'boolean',
        'backup_state' => 'boolean',
        'last_used_at' => 'datetime',
    ];
}