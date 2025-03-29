<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CurrentOrganizationController extends Controller
{

    public function switch(Organization $organization, Request $request)
    {
        $request->user()->switchOrganization($organization);

        return Redirect::route('organization.show', [
            'organization' => $request->user()->currentOrganization,
        ]);
    }

}
