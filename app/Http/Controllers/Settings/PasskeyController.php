<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\UserAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Passkey;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;

class PasskeyController extends Controller
{
    /**
     * Display the passkeys management page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $passkeys = $user->passkeys()
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($passkey) {
                return [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'device_name' => $passkey->device_name,
                    'device_type' => $passkey->device_type,
                    'browser_name' => $passkey->browser_name,
                    'operating_system' => $passkey->operating_system,
                    'last_used_at' => $passkey->last_used_at?->toDateTimeString(),
                    'created_at' => $passkey->created_at->toDateTimeString(),
                    'is_recently_used' => $passkey->last_used_at?->isAfter(now()->subDays(7)) ?? false,
                ];
            });

        return Inertia::render('settings/Passkeys', [
            'passkeys' => $passkeys,
        ]);
    }

    /**
     * Generate options for passkey registration.
     */
    public function generateOptions(Request $request, GeneratePasskeyRegisterOptionsAction $action): JsonResponse
    {
        $options = $action->execute($request->user());

        // The action returns a JSON string, we need to decode it for the response
        return response()->json(json_decode($options, true));
    }

    /**
     * Store a new passkey.
     */
    public function store(Request $request, StorePasskeyAction $action): JsonResponse
    {
        $validated = $request->validate([
            'passkey' => 'required|json',
            'options' => 'required|json',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Parse user agent data
            $userAgentService = app(UserAgent::class);
            $userAgentService->setUserAgent($request->userAgent());

            // Get device information
            $deviceType = 'desktop';
            if ($userAgentService->isMobile()) {
                $deviceType = 'mobile';
            } elseif ($userAgentService->isTablet()) {
                $deviceType = 'tablet';
            }

            $userAgentData = [
                'browser_name' => $userAgentService->browser(),
                'operating_system' => $userAgentService->platform(),
                'device_type' => $deviceType,
                'device_name' => null, // Device name not available from UserAgent
            ];

            // Auto-generate device name if not provided
            $name = $validated['name'] ?? $this->generateDeviceName($userAgentData);

            // Store the passkey
            $passkey = $action->execute(
                $request->user(),
                $validated['passkey'],
                $validated['options'],
                $request->getHost(),
                ['name' => $name]
            );

            // Update passkey with additional metadata
            $passkey->update([
                'user_agent' => $request->userAgent(),
                'device_name' => $userAgentData['device_name'] ?? null,
                'device_type' => $userAgentData['device_type'] ?? 'desktop',
                'browser_name' => $userAgentData['browser_name'] ?? null,
                'operating_system' => $userAgentData['operating_system'] ?? null,
                'counter' => 0,
                'backup_eligible' => false,
                'backup_state' => false,
                'transports' => json_decode($validated['passkey'], true)['response']['transports'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Passkey created successfully',
                'passkey' => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'device_name' => $passkey->device_name,
                    'created_at' => $passkey->created_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create passkey: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a passkey.
     */
    public function destroy(Request $request, Passkey $passkey): JsonResponse
    {
        // Ensure the passkey belongs to the authenticated user (compare as strings for Snowflake IDs)
        if ((string) $passkey->authenticatable_id !== (string) $request->user()->id) {
            abort(403);
        }

        // Prevent deletion if it's the last authentication method and user has no password
        $user = $request->user();
        $hasPassword = ! empty($user->password);
        $hasOtherPasskeys = $user->passkeys()->where('id', '!=', $passkey->id)->exists();
        $hasTwoFactor = $user->two_factor_enabled;

        if (! $hasPassword && ! $hasOtherPasskeys && ! $hasTwoFactor) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your last authentication method. Please set a password or add another passkey first.',
            ], 422);
        }

        $passkey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Passkey deleted successfully',
        ]);
    }

    /**
     * Generate a device name from user agent data.
     */
    private function generateDeviceName(array $userAgentData): string
    {
        $parts = [];

        // Add browser name
        if (!empty($userAgentData['browser_name'])) {
            $parts[] = $userAgentData['browser_name'];
        }

        // Add operating system
        if (!empty($userAgentData['operating_system'])) {
            $parts[] = 'on ' . $userAgentData['operating_system'];
        }

        // Add device type if not desktop
        if (!empty($userAgentData['device_type']) && $userAgentData['device_type'] !== 'desktop') {
            $parts[] = '(' . ucfirst($userAgentData['device_type']) . ')';
        }

        if (empty($parts)) {
            return 'Unknown Device';
        }

        return implode(' ', $parts);
    }
}