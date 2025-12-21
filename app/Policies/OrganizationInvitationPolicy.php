<?php

namespace App\Policies;

use App\Models\Organization\OrganizationInvitation;
use App\Models\User;
use App\Policies\Traits\BelongsToOrganization;
use App\Policies\Traits\ChecksOrganizationOwnership;
use App\Policies\Traits\ChecksOrganizationPermissions;
use Illuminate\Database\Eloquent\Model;

class OrganizationInvitationPolicy extends Policy
{
    use BelongsToOrganization, ChecksOrganizationOwnership, ChecksOrganizationPermissions;

    protected string $mode = 'permissive';

    protected ?string $resource = 'organization_invitation';

    /**
     * Determine whether the user can view any invitations.
     */
    public function viewAny(User $user): bool
    {
        // Must have a current organization selected
        return $user->current_organization_id !== null;
    }

    /**
     * Determine whether the user can view the invitation.
     */
    public function view(User $user, Model $model): bool
    {
        if (! $model instanceof OrganizationInvitation) {
            return false;
        }

        // Can view if belongs to the organization OR is the invited email
        return $user->belongsToOrganization($model->organization)
            || strtolower($user->email) === strtolower($model->email);
    }

    /**
     * Determine whether the user can create invitations.
     */
    public function create(User $user): bool
    {
        // Must have organization admin permissions
        if (! $user->current_organization_id) {
            return false;
        }

        $organization = $user->currentOrganization;

        return $this->isOrganizationAdmin($user, $organization);
    }

    /**
     * Determine whether the user can update the invitation.
     */
    public function update(User $user, Model $model): bool
    {
        if (! $model instanceof OrganizationInvitation) {
            return false;
        }

        // Only organization admins can update invitations
        return $this->isOrganizationAdmin($user, $model->organization);
    }

    /**
     * Determine whether the user can delete the invitation.
     */
    public function delete(User $user, Model $model): bool
    {
        if (! $model instanceof OrganizationInvitation) {
            return false;
        }

        // Organization admins can delete invitations
        return $this->isOrganizationAdmin($user, $model->organization);
    }

    /**
     * Determine whether the user can accept the invitation.
     */
    public function accept(User $user, OrganizationInvitation $invitation): bool
    {
        // Can only accept if the invitation is for their email
        return strtolower($user->email) === strtolower($invitation->email);
    }

    /**
     * Determine whether the user can resend the invitation.
     */
    public function resend(User $user, OrganizationInvitation $invitation): bool
    {
        // Organization admins can resend invitations
        return $this->isOrganizationAdmin($user, $invitation->organization);
    }
}
