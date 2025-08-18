<?php

namespace App\Policies\Traits;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ChecksOrganizationPermissions
{
    /**
     * Check BOTH permission types:
     * 1. System-level permissions (for system admins)
     * 2. Organization-level permissions (for org members)
     */
    protected function hasPermission(User $user, string $ability, ?Model $model = null): bool
    {
        // First check system-level permission (for system admins)
        if (parent::hasPermission($user, $ability, $model)) {
            return true;
        }

        // Then check organization-level permission
        $organization = null;

        if ($model instanceof Organization) {
            $organization = $model;
        } elseif ($model && method_exists($model, 'organization')) {
            $organization = $model->organization;
        }

        if ($organization) {
            return $user->hasOrganizationPermission($organization, $ability);
        }

        return false;
    }
}
