<?php

namespace App\Http\Controllers\Organization;

use App\Enums\OrganizationRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizationController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function show(Organization $organization)
    {
        return Inertia::render('organization/Profile', [
            'organization' => $organization
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $organization = $request->user()->ownedOrganizations()->create($request->only('name'));

        $request->user()->switchOrganization($organization);

        return redirect()->route('organization.show', $organization);
    }

    public function update(Organization $organization, Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:1024'],
        ]);

        $organization->update($request->only('name', 'avatar'));

        return redirect()->route('organization.show', $organization);
    }

    public function deleteAvatar(Organization $organization, Request $request)
    {
        $this->authorize('update', $organization);

        $organization->deleteMedia('avatar')->save();

        return redirect()->back();
    }

}
