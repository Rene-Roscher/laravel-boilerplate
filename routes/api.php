<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Current user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // API Token Management (for web interface, not available via API)
    // These routes are intentionally not exposed via API for security

    // Example API endpoints
    Route::prefix('v1')->group(function () {
        // User endpoints
        Route::apiResource('users', UserController::class)->only(['index', 'show']);

        // Add more API resources here
    });
});
