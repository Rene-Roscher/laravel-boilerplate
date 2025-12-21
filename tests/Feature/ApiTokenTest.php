<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

// Token Management Page Tests
it('requires authentication to view api tokens page', function () {
    $this->get(route('user.api-tokens.index'))
        ->assertRedirect(route('login'));
});

it('displays the api tokens management page', function () {
    actingAs($this->user)
        ->get(route('user.api-tokens.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/ApiTokens')
            ->has('tokens')
            ->has('availableAbilities'));
});

// Token Creation Tests
it('can create an api token with abilities', function () {
    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => 'Test Token',
            'abilities' => ['read', 'update'],
        ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'API token created successfully',
        ])
        ->assertJsonStructure([
            'token',
            'accessToken' => [
                'id',
                'name',
                'abilities',
                'created_at',
            ],
        ]);

    expect($this->user->tokens()->count())->toBe(1);
    expect($this->user->tokens()->first()->name)->toBe('Test Token');
    expect($this->user->tokens()->first()->abilities)->toContain('read', 'update');
});

it('can create an api token with expiration', function () {
    $expiresAt = now()->addDays(30);

    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => 'Expiring Token',
            'abilities' => ['read'],
            'expires_at' => $expiresAt->toISOString(),
        ])
        ->assertSuccessful();

    $token = $this->user->tokens()->first();
    expect($token->expires_at->format('Y-m-d'))->toBe($expiresAt->format('Y-m-d'));
});

it('validates token creation input', function () {
    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'abilities']);

    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => '',
            'abilities' => [],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'abilities']);
});

it('validates token abilities are valid', function () {
    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => 'Invalid Token',
            'abilities' => ['invalid-ability', 'another-invalid'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['abilities.0', 'abilities.1']);
});

it('validates expiration date is in the future', function () {
    actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => 'Past Token',
            'abilities' => ['read'],
            'expires_at' => now()->subDay()->toISOString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['expires_at']);
});

// Token Deletion Tests
it('can delete an api token', function () {
    $token = $this->user->createToken('Test Token', ['read']);

    actingAs($this->user)
        ->deleteJson(route('user.api-tokens.destroy', $token->accessToken->id))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'API token revoked successfully',
        ]);

    expect($this->user->tokens()->count())->toBe(0);
});

it('cannot delete another users token', function () {
    $otherUser = User::factory()->create();
    $token = $otherUser->createToken('Other Token', ['read']);

    actingAs($this->user)
        ->deleteJson(route('user.api-tokens.destroy', $token->accessToken->id))
        ->assertNotFound();

    expect($otherUser->tokens()->count())->toBe(1);
});

it('returns 404 for non-existent token', function () {
    actingAs($this->user)
        ->deleteJson(route('user.api-tokens.destroy', 'non-existent-id'))
        ->assertNotFound();
});

it('can delete all tokens', function () {
    $this->user->createToken('Token 1', ['read']);
    $this->user->createToken('Token 2', ['update']);
    $this->user->createToken('Token 3', ['delete']);

    expect($this->user->tokens()->count())->toBe(3);

    actingAs($this->user)
        ->deleteJson(route('user.api-tokens.destroy-all'))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'All API tokens revoked successfully',
        ]);

    expect($this->user->tokens()->count())->toBe(0);
});

// Rate Limiting Tests
it('enforces rate limiting on token creation', function () {
    actingAs($this->user);

    // Make 5 requests (the limit)
    for ($i = 0; $i < 5; $i++) {
        postJson(route('user.api-tokens.store'), [
            'name' => "Token $i",
            'abilities' => ['read'],
        ])->assertSuccessful();
    }

    // The 6th request should be rate limited
    postJson(route('user.api-tokens.store'), [
        'name' => 'Token 6',
        'abilities' => ['read'],
    ])->assertTooManyRequests();
});

it('enforces rate limiting on token deletion', function () {
    actingAs($this->user);

    // Create tokens to delete
    $tokens = [];
    for ($i = 0; $i < 11; $i++) {
        $token = $this->user->createToken("Token $i", ['read']);
        $tokens[] = $token->accessToken->id;
    }

    // Make 10 delete requests (the limit)
    for ($i = 0; $i < 10; $i++) {
        deleteJson(route('user.api-tokens.destroy', $tokens[$i]))->assertSuccessful();
    }

    // The 11th request should be rate limited
    deleteJson(route('user.api-tokens.destroy', $tokens[10]))->assertTooManyRequests();
});

// API Authentication Tests
it('can authenticate with api token', function () {
    $token = $this->user->createToken('API Token', ['read']);

    getJson('/api/user', [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])
        ->assertSuccessful()
        ->assertJson([
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
});

it('cannot access api without token', function () {
    getJson('/api/user')
        ->assertUnauthorized();
});

it('cannot access api with invalid token', function () {
    getJson('/api/user', [
        'Authorization' => 'Bearer invalid-token',
    ])
        ->assertUnauthorized();
});

it('cannot access api with expired token', function () {
    $token = $this->user->createToken('Expired Token', ['read'], now()->subDay());

    getJson('/api/user', [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])
        ->assertUnauthorized();
});

// Token Abilities Tests
it('allows api access with correct token abilities', function () {
    $token = $this->user->createToken('Read Token', ['read']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/v1/users')
        ->assertSuccessful();
});

it('denies api access without correct token abilities', function () {
    $token = $this->user->createToken('Other Token', ['create', 'update', 'delete']);

    $this->withToken($token->plainTextToken)
        ->getJson('/api/v1/users')
        ->assertForbidden();
});

it('allows access with read ability', function () {
    $token = $this->user->createToken('Read Token', ['read']);

    getJson('/api/v1/users', [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])
        ->assertSuccessful();
});

// Token Metadata Tests
it('tracks last used time for tokens', function () {
    $token = $this->user->createToken('Track Token', ['read']);

    expect($this->user->tokens()->first()->last_used_at)->toBeNull();

    getJson('/api/user', [
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ]);

    expect($this->user->tokens()->first()->last_used_at)->not->toBeNull();
});

// Security Tests
it('does not expose plain text token after creation', function () {
    $response = actingAs($this->user)
        ->postJson(route('user.api-tokens.store'), [
            'name' => 'Security Token',
            'abilities' => ['read'],
        ]);

    $tokenValue = $response->json('token');
    expect($tokenValue)->toBeString();
    expect($tokenValue)->toContain('|');

    // Get the tokens list
    actingAs($this->user)
        ->get(route('user.api-tokens.index'))
        ->assertInertia(function ($page) {
            $page->component('settings/ApiTokens')
                ->has('tokens')
                ->where('tokens', function ($tokens) {
                    // Token value should not be in the tokens list
                    foreach ($tokens as $token) {
                        expect($token)->not->toHaveKey('token');
                        expect($token)->not->toHaveKey('plain_text_token');
                    }

                    return true;
                });
        });
});
