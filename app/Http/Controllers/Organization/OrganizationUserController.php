<?php

namespace App\Http\Controllers\Organization;

use App\Enums\OrganizationRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use App\Models\User;
use App\Notifications\Organization\OrganizationInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
            'canAddMember' => auth()->user()->can('addOrganizationMember', $organization),
        ]);
    }

    public function inviteUser(Organization $organization, Request $request)
    {
        $this->authorize('addOrganizationMember', $organization);

        // Rate limiting: max 10 invitations per hour per organization
        $key = 'org-invite:'.$organization->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, __('organization.too_many_invitations', ['seconds' => $seconds]));
        }
        RateLimiter::hit($key, 3600); // 1 hour

        $request->validate([
            'email' => [
                'string',
                'email',
                function ($attribute, $value, $fail) use ($organization, $request) {
                    // Cannot invite yourself
                    if (strtolower($value) === strtolower($request->user()->email)) {
                        return $fail(__('organization.cannot_invite_self'));
                    }

                    // Check if user already exists in organization (including owner)
                    if ($organization->users()->where('email', $value)->exists() ||
                        (strtolower($organization->owner->email) === strtolower($value))) {
                        return $fail(__('organization.user_exists'));
                    }

                    // Check for existing invitations
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
        $this->authorize('removeOrganizationMember', $organization);

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
        $request->validate([
            'user_id' => [
                'required',
                Rule::exists('organization_user', 'user_id')
                    ->where('organization_id', $organization->id),
            ],
        ]);

        $userId = $request->user_id;
        $targetUser = User::findOrFail($userId);

        // Check if user can remove this specific member
        $policy = new \App\Policies\OrganizationPolicy;
        if (! $policy->canRemoveSpecificMember($request->user(), $organization, $targetUser)) {
            abort(403, 'Unauthorized to remove this member');
        }

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
        $this->authorize('updateOrganizationMember', $organization);

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

        // Ensure that the user is not trying to change their own role
        if (
            $request->user_id === $request->user()->id &&
            auth()->user()->organizationRole($organization)['id'] !== $request->get('role')
        ) {
            return back()->withErrors([
                'role' => __('organization.cannot_change_own_role'),
            ]);
        }

        $organization->users()->updateExistingPivot($request->user_id, [
            'role' => $request->role,
        ]);

        return back();
    }
}
