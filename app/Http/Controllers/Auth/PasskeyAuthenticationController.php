<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Fortify;
use Spatie\LaravelPasskeys\Actions\AuthenticateUsingPasskeyAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Models\Passkey;

class PasskeyAuthenticationController extends Controller
{
    /**
     * Generate authentication options for passkey login.
     */
    public function options(GeneratePasskeyAuthenticationOptionsAction $action): JsonResponse
    {
        $options = $action->execute();

        return response()->json($options);
    }

    /**
     * Authenticate using a passkey.
     */
    public function authenticate(
        Request $request,
        AuthenticateUsingPasskeyAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'start_authentication_response' => 'required|json',
        ]);

        try {
            DB::beginTransaction();

            // Attempt to authenticate with the passkey
            $user = $action->execute($validated['start_authentication_response']);

            if ($user) {
                // Update passkey metadata
                $passkeyData = json_decode($validated['start_authentication_response'], true);
                $credentialId = $passkeyData['id'] ?? null;

                if ($credentialId) {
                    $passkey = Passkey::where('credential_id', $credentialId)->first();

                    if ($passkey) {
                        $passkey->update([
                            'last_used_at' => now(),
                            'counter' => $passkey->counter + 1,
                        ]);
                    }
                }

                // Login the user
                Auth::login($user);
                $request->session()->regenerate();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Authentication successful',
                    'redirect' => $this->redirectPath(),
                ]);
            }

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
            ], 401);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get the post-authentication redirect path.
     */
    protected function redirectPath(): string
    {
        return Fortify::redirects('login', route('dashboard'));
    }
}