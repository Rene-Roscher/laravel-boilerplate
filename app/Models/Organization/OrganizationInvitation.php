<?php

namespace App\Models\Organization;

use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin IdeHelperOrganizationInvitation
 */
class OrganizationInvitation extends BaseModel
{
    use Notifiable;

    protected $fillable = [
        'email',
        'role',
        'organization_id',
    ];

    /**
     * Get the organization that owns the invitation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

}
