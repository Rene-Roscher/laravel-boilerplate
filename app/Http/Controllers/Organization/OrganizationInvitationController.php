<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization\OrganizationInvitation;

class OrganizationInvitationController extends Controller
{
    public function accept(OrganizationInvitation $invitation)
    {
        $this->authorize('accept', $invitation);

        $user = auth()->user();
        $organization = $invitation->organization;

        if (! $organization->users()->where('user_id', $user->id)->exists()) {
            $organization->users()->attach($user->id, [
                'role' => $invitation->role,
            ]);
        }

        $invitation->delete();

        $user->forceFill([
            'current_organization_id' => $organization->id,
        ])->save();

        return redirect()->route('organization.show', $organization);
    }
}
