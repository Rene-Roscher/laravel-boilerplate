<?php

namespace App\Http\Controllers\Settings;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function __construct()
    {
        // Rate limit token creation
        $this->middleware('throttle:5,1')->only('store');
        // Rate limit token deletion
        $this->middleware('throttle:10,1')->only('destroy');
    }

    /**
     * Display the API tokens management page.
     */
    public function index(Request $request): Response
    {
        $tokens = $request->user()->tokens()
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'created_at' => $token->created_at->toISOString(),
                    'expires_at' => $token->expires_at?->toISOString(),
                ];
            });

        return Inertia::render('settings/ApiTokens', [
            'tokens' => $tokens,
            'availableAbilities' => TokenAbility::withDescriptions(),
        ]);
    }

    /**
     * Store a new API token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(TokenAbility::values())],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        try {
            DB::beginTransaction();

            // Create the token with specified abilities
            $token = $request->user()->createToken(
                name: $validated['name'],
                abilities: $validated['abilities'],
                expiresAt: isset($validated['expires_at']) ? new \DateTime($validated['expires_at']) : null
            );

            DB::commit();

            // Return the plain text token - this is the only time it will be visible
            return response()->json([
                'success' => true,
                'message' => 'API token created successfully',
                'token' => $token->plainTextToken,
                'accessToken' => [
                    'id' => $token->accessToken->id,
                    'name' => $token->accessToken->name,
                    'abilities' => $token->accessToken->abilities,
                    'created_at' => $token->accessToken->created_at->toISOString(),
                    'expires_at' => $token->accessToken->expires_at?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to create API token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create API token. Please try again.',
            ], 422);
        }
    }

    /**
     * Revoke an API token.
     */
    public function destroy(Request $request, string $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        try {
            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'API token revoked successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to revoke API token', [
                'error' => $e->getMessage(),
                'token_id' => $tokenId,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke API token. Please try again.',
            ], 422);
        }
    }

    /**
     * Revoke all API tokens for the user.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All API tokens revoked successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to revoke all API tokens', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke API tokens. Please try again.',
            ], 422);
        }
    }
}
