<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Fortify;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;

class PasskeyAuthenticationController extends Controller
{
    /**
     * Generate authentication options for passkey login.
     */
    public function options(GeneratePasskeyAuthenticationOptionsAction $action): JsonResponse
    {
        $options = $action->execute();

        // The action returns a JSON string, we need to decode it for the response
        return response()->json(json_decode($options, true));
    }

    /**
     * Authenticate using a passkey.
     */
    public function authenticate(
        Request $request,
        FindPasskeyToAuthenticateAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'start_authentication_response' => 'required|json',
        ]);

        try {
            DB::beginTransaction();

            // Attempt to authenticate with the passkey
            $passkey = $action->execute(
                $validated['start_authentication_response'],
                Session::get('passkey-authentication-options')
            );

            if ($passkey) {
                $user = $passkey->authenticatable;
                // Update passkey metadata
                $passkey->update([
                    'last_used_at' => now(),
                    'counter' => $passkey->counter + 1,
                ]);

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

            // Log the actual error for debugging
            \Log::error('Passkey authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
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
