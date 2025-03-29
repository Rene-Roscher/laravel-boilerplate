<?php

namespace App\Http\Controllers\Organization;

use App\Enums\OrganizationRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizationInvitationController extends Controller
{

    public function accept(OrganizationInvitation $invitation)
    {
        $user = auth()->user();
        $organization = $invitation->organization;

        if (strtolower($invitation->email) !== strtolower($user->email)) {
            return redirect()->route('dashboard')->with('error', __('You are not authorized to accept this invitation.'));
        }

        if (!$organization->users()->where('user_id', $user->id)->exists()) {
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
