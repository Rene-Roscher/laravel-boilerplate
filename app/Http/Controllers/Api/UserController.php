<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Check if user has read permission when using API token
        if ($request->user()->currentAccessToken()) {
            if (! $request->user()->tokenCan(TokenAbility::READ->value)) {
                abort(403, 'Insufficient permissions. This endpoint requires "read" ability.');
            }
        }

        $users = User::query()
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): UserResource
    {
        // Check if user has read permission when using API token
        if ($request->user()->currentAccessToken()) {
            if (! $request->user()->tokenCan(TokenAbility::READ->value)) {
                abort(403, 'Insufficient permissions. This endpoint requires "read" ability.');
            }
        }

        return new UserResource($user);
    }
}
