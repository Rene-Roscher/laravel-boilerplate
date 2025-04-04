<?php

namespace App\Http\Controllers\Organization;

use App\Enums\OrganizationRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use App\Models\User;
use App\Notifications\Organization\OrganizationInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizationUserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function show(Organization $organization)
    {
        $organization->loadMissing(['invitations', 'users']);

        return Inertia::render('organization/Users', [
            'organization' => $organization,
            'users' => $organization->users,
            'roles' => OrganizationRoleEnum::all(),
            'invitations' => $organization->invitations,
            'canAddMember' => auth()->user()->can('add-organization-member', $organization),
        ]);
    }

    public function inviteUser(Organization $organization, Request $request)
    {
        $this->authorize('add-organization-member', $organization);

        $request->validate([
            'email' => [
                'string',
                'email',
                function ($attribute, $value, $fail) use ($organization) {
                    if ($organization->users()->where('email', $value)->exists()) {
                        return $fail(__('organization.user_exists'));
                    }

                    if ($organization->invitations()->where('email', $value)->exists()) {
                        return $fail(__('organization.invitation_exists'));
                    }
                },
            ],
            'role' => [
                'string',
                Rule::in(OrganizationRoleEnum::names()),
            ],
        ]);

        /** @var \App\Models\Organization\OrganizationInvitation $invitation */
        $invitation = $organization->invitations()->create($request->only(['email', 'role']));
        $invitation->notify(new OrganizationInvitationNotification($invitation));

        return back();
    }

    public function deleteInvitation(Organization $organization, Request $request)
    {
        $this->authorize('remove-organization-member', $organization);

        $request->validate([
            'invitation_id' => [
                'required',
                Rule::exists('organization_invitations', 'id')
                    ->where('organization_id', $organization->id),
            ],
        ]);

        $organization->invitations()->where('id', $request->invitation_id)->delete();

        return back();
    }

    public function detachUser(Organization $organization, Request $request)
    {
        $this->authorize('remove-organization-member', $organization);

        $request->validate([
            'user_id' => [
                'required',
                Rule::exists('organization_user', 'user_id')
                    ->where('organization_id', $organization->id),
            ],
        ]);

        $userId = $request->user_id;

        $organization->users()->detach($userId);

        // Ensure the user is removed from the organization
        if (User::query()->where('id', $userId)->where('current_organization_id', $organization->id)->exists()) {
            User::query()->where('id', $userId)->update([
                'current_organization_id' => optional(Organization::query()->where('user_id', $userId)->where('is_default', true)->first())->id ?? null,
            ]);
        }

        return back();
    }

    public function updateUser(Organization $organization, Request $request)
    {
        $this->authorize('update-organization-member', $organization);

        $request->validate([
            'user_id' => [
                'required',
                Rule::exists('organization_user', 'user_id')
                    ->where('organization_id', $organization->id),
            ],
            'role' => [
                'required',
                Rule::in(OrganizationRoleEnum::names()),
            ],
        ]);

        $organization->users()->updateExistingPivot($request->user_id, [
            'role' => $request->role,
        ]);

        return back();
    }

}
