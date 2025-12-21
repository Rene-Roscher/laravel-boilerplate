<?php

namespace App\Policies;

use App\Enums\OrganizationRoleEnum;
use App\Models\Organization\Organization;
use App\Models\User;
use App\Policies\Traits\ChecksOrganizationOwnership;
use App\Policies\Traits\ChecksOrganizationPermissions;
use Illuminate\Database\Eloquent\Model;

class OrganizationPolicy extends Policy
{
    use ChecksOrganizationOwnership, ChecksOrganizationPermissions;

    protected string $mode = 'permissive'; // Permission OR Ownership

    protected ?string $resource = 'organization';

    /**
     * Anyone can list organizations
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * User must belong to organization to view it
     */
    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof Organization) {
            return false;
        }

        // System admin OR belongs to organization
        return $user->can($this->permission('view'))
            || $user->belongsToOrganization($model);
    }

    /**
     * Anyone can create an organization
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * System admin OR organization permission required
     */
    public function update(User $user, Model $model): bool
    {
        if (! $model instanceof Organization) {
            return false;
        }

        // Use the framework's check method which respects $mode
        return $this->check($user, 'update', $model);
    }

    /**
     * Only organization owner can delete (NOT system admin)
     */
    public function delete(User $user, Model $model): bool
    {
        if (! $model instanceof Organization) {
            return false;
        }

        // Direct check - owner only, no system admin override
        return $user->ownsOrganization($model);
    }

    /**
     * Member management - organization admin only
     * These are custom methods, so they can have specific type hints
     */
    public function addOrganizationMember(User $user, Organization $organization): bool
    {
        return $this->isOrganizationAdmin($user, $organization);
    }

    public function updateOrganizationMember(User $user, Organization $organization): bool
    {
        return $this->isOrganizationAdmin($user, $organization);
    }

    public function removeOrganizationMember(User $user, Organization $organization): bool
    {
        // Must be admin to remove members
        // Note: The check for removing the owner should be done in the controller/request
        // because Laravel's Gate only passes 2 parameters to policy methods by default
        return $this->isOrganizationAdmin($user, $organization);
    }

    /**
     * Check if a specific user can be removed from organization
     * This is a separate method that can be called with explicit parameters
     */
    public function canRemoveSpecificMember(User $user, Organization $organization, User $targetUser): bool
    {
        // Cannot remove organization owner
        if ($organization->owner->is($targetUser)) {
            return false;
        }

        // Cannot remove yourself
        if ($user->is($targetUser)) {
            return false;
        }

        // Check if this is the last admin (besides owner)
        $adminCount = $organization->users()
            ->wherePivot('role', OrganizationRoleEnum::ADMIN->name)
            ->count();

        if ($targetUser->hasOrganizationRole($organization, OrganizationRoleEnum::ADMIN) && $adminCount <= 1) {
            // This is the last admin, don't allow removal
            return false;
        }

        // Must be admin to remove members
        return $this->isOrganizationAdmin($user, $organization);
    }
}
