<?php

namespace App\Policies\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait BelongsToOrganization
{
    /**
     * Ensure user belongs to organization before any action
     * System admins bypass organization membership check
     */
    protected function check(User $user, string $ability, ?Model $model = null): bool
    {
        // For organization-scoped models
        if ($model && method_exists($model, 'organization')) {
            $organization = $model->organization;

            // Check organization membership (unless system admin)
            if ($organization && ! $user->belongsToOrganization($organization)) {
                // System admin can still pass via permission check
                if (! $user->can($this->permission($ability))) {
                    return false;
                }
            }
        }

        // Continue with normal checks
        return parent::check($user, $ability, $model);
    }

    /**
     * User must have a current organization selected
     */
    public function viewAny(User $user): bool
    {
        // System admins can always view
        if ($user->can($this->permission('viewAny'))) {
            return true;
        }

        return $user->current_organization_id !== null;
    }
}
