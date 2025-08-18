<?php

namespace App\Policies\Traits;

use App\Enums\OrganizationRoleEnum;
use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ChecksOrganizationOwnership
{
    /**
     * Check model ownership (user_id field)
     * NOT organization ownership - that's separate!
     */
    protected function owns(User $user, Model $model): bool
    {
        // Special case: Organization model itself
        if ($model instanceof Organization) {
            return $user->ownsOrganization($model);
        }

        // Regular models: check user_id field
        return parent::owns($user, $model);
    }

    /**
     * Check if user owns the organization (separate from model ownership!)
     */
    protected function userOwnsOrganization(User $user, Model $model): bool
    {
        if ($model instanceof Organization) {
            return $user->ownsOrganization($model);
        }

        // If model belongs to an organization
        if (method_exists($model, 'organization')) {
            $organization = $model->organization;

            return $organization && $user->ownsOrganization($organization);
        }

        return false;
    }

    /**
     * Check if user is admin in the organization
     * Note: hasOrganizationRole returns true for owners automatically!
     */
    protected function isOrganizationAdmin(User $user, Model $model): bool
    {
        $organization = $model instanceof Organization
            ? $model
            : ($model->organization ?? null);

        if (! $organization) {
            return false;
        }

        return $user->hasOrganizationRole($organization, OrganizationRoleEnum::ADMIN);
    }
}
