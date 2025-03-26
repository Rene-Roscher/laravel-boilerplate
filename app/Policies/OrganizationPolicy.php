<?php

namespace App\Policies;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->ownsOrganization($organization);
    }

    /**
     * Determine whether the user can add team members.
     */
    public function addOrganizationMember(User $user, Organization $organization): bool
    {
        return $user->ownsOrganization($organization);
    }

    /**
     * Determine whether the user can update team member permissions.
     */
    public function updateOrganizationMember(User $user, Organization $organization): bool
    {
        return $user->ownsOrganization($organization);
    }

    /**
     * Determine whether the user can remove team members.
     */
    public function removeOrganizationMember(User $user, Organization $organization): bool
    {
        return $user->ownsOrganization($organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->ownsOrganization($organization);
    }
}
