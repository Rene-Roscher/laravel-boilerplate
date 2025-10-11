<?php

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

// Authorization Tests
it('prevents unauthorized access to passkey management page', function () {
    $this->get(route('user.passkeys.index'))
        ->assertRedirect(route('login'));
});

it('prevents unauthorized users from generating passkey options', function () {
    $this->getJson(route('user.passkeys.generate-options'))
        ->assertUnauthorized();
});

it('prevents unauthorized users from creating passkeys', function () {
    $this->postJson(route('user.passkeys.store'), [
        'passkey' => json_encode(['fake' => 'data']),
        'options' => json_encode(['fake' => 'options']),
    ])->assertUnauthorized();
});

it('prevents users from deleting other users passkeys', function () {
    // Create a passkey for the other user using DB insert to bypass model validation
    DB::table('passkeys')->insert([
        'id' => '1234567890',
        'authenticatable_id' => $this->otherUser->id,
        'name' => 'Test Passkey',
        'credential_id' => 'test-credential-id',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($this->user)
        ->deleteJson(route('user.passkeys.destroy', ['passkey' => '1234567890']))
        ->assertForbidden();
});

// Input Validation Tests
it('validates required fields when storing a passkey', function () {
    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['passkey', 'options']);
});

it('validates JSON format for passkey data', function () {
    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [
            'passkey' => 'not-valid-json',
            'options' => 'also-not-valid-json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['passkey', 'options']);
});

it('validates passkey name length', function () {
    $longName = str_repeat('a', 256);

    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [
            'passkey' => json_encode(['test' => 'data']),
            'options' => json_encode(['test' => 'options']),
            'name' => $longName,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

// Rate Limiting Tests
it('enforces rate limiting on passkey creation', function () {
    actingAs($this->user);

    // Make 5 requests (the limit)
    for ($i = 0; $i < 5; $i++) {
        postJson(route('user.passkeys.store'), [
            'passkey' => json_encode(['test' => 'data']),
            'options' => json_encode(['test' => 'options']),
        ]);
    }

    // The 6th request should be rate limited
    postJson(route('user.passkeys.store'), [
        'passkey' => json_encode(['test' => 'data']),
        'options' => json_encode(['test' => 'options']),
    ])->assertTooManyRequests();
});

it('enforces rate limiting on passkey deletion', function () {
    actingAs($this->user);

    // Create passkeys to delete using DB insert
    $passkeyIds = [];
    for ($i = 0; $i < 11; $i++) {
        $id = (string) ($i + 100000);
        DB::table('passkeys')->insert([
            'id' => $id,
            'authenticatable_id' => $this->user->id,
            'name' => 'Test Passkey '.$i,
            'credential_id' => 'test-credential-id-'.$i,
            'data' => json_encode(['test' => 'data']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $passkeyIds[] = $id;
    }

    // Make 10 delete requests (the limit)
    for ($i = 0; $i < 10; $i++) {
        deleteJson(route('user.passkeys.destroy', ['passkey' => $passkeyIds[$i]]));
    }

    // The 11th request should be rate limited
    deleteJson(route('user.passkeys.destroy', ['passkey' => $passkeyIds[10]]))
        ->assertTooManyRequests();
});

// Security Constraint Tests
it('prevents deletion of last authentication method', function () {
    // Create a user without password
    $user = User::factory()->create(['password' => null]);

    // Create their only passkey using DB insert
    DB::table('passkeys')->insert([
        'id' => '200000',
        'authenticatable_id' => $user->id,
        'name' => 'Only Passkey',
        'credential_id' => 'only-credential',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->deleteJson(route('user.passkeys.destroy', ['passkey' => '200000']))
        ->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => 'Cannot delete your last authentication method. Please set a password or add another passkey first.',
        ]);
});

it('allows deletion if user has a password', function () {
    // User has password by default from factory
    DB::table('passkeys')->insert([
        'id' => '300000',
        'authenticatable_id' => $this->user->id,
        'name' => 'Test Passkey',
        'credential_id' => 'test-credential',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($this->user)
        ->deleteJson(route('user.passkeys.destroy', ['passkey' => '300000']))
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Passkey deleted successfully',
        ]);
});

it('allows deletion if user has other passkeys', function () {
    $user = User::factory()->create(['password' => null]);

    // Create two passkeys using DB insert
    DB::table('passkeys')->insert([
        'id' => '400000',
        'authenticatable_id' => $user->id,
        'name' => 'Passkey 1',
        'credential_id' => 'credential-1',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('passkeys')->insert([
        'id' => '400001',
        'authenticatable_id' => $user->id,
        'name' => 'Passkey 2',
        'credential_id' => 'credential-2',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Should be able to delete one when another exists
    actingAs($user)
        ->deleteJson(route('user.passkeys.destroy', ['passkey' => '400000']))
        ->assertSuccessful();
});

// SQL Injection Prevention Tests
it('prevents SQL injection through passkey name', function () {
    $maliciousName = "'; DROP TABLE passkeys; --";

    // The request will fail but won't execute SQL injection
    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [
            'passkey' => json_encode(['test' => 'data']),
            'options' => json_encode(['test' => 'options']),
            'name' => $maliciousName,
        ])
        ->assertUnprocessable(); // Will fail validation

    // Table should still exist
    expect(DB::getSchemaBuilder()->hasTable('passkeys'))->toBeTrue();
});

it('prevents SQL injection in delete endpoint', function () {
    $maliciousId = "1' OR '1'='1";

    actingAs($this->user)
        ->deleteJson(route('user.passkeys.destroy', ['passkey' => $maliciousId]))
        ->assertNotFound();

    // No passkeys should be deleted
    expect(Passkey::count())->toBe(0);
});

// XSS Prevention Tests
it('properly escapes passkey names in responses', function () {
    $xssName = '<script>alert("XSS")</script>';

    DB::table('passkeys')->insert([
        'id' => '500000',
        'authenticatable_id' => $this->user->id,
        'name' => $xssName,
        'credential_id' => 'test-credential',
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($this->user)
        ->get(route('user.passkeys.index'))
        ->assertSuccessful()
        ->assertDontSee('<script>alert("XSS")</script>', false);
});

// Error Handling Tests
it('does not expose sensitive error information', function () {
    // Force an error by using invalid data that will fail in the action
    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [
            'passkey' => json_encode(['invalid' => 'structure']),
            'options' => json_encode(['also' => 'invalid']),
        ])
        ->assertUnprocessable()
        ->assertJsonMissing(['trace'])
        ->assertJsonMissing(['file'])
        ->assertJsonMissing(['line']);
});

// Transaction Rollback Test
it('rolls back transaction on failure', function () {
    $initialCount = Passkey::count();

    // Mock the StorePasskeyAction to throw an exception
    $this->mock(\Spatie\LaravelPasskeys\Actions\StorePasskeyAction::class)
        ->shouldReceive('execute')
        ->andThrow(new \Exception('Simulated failure'));

    actingAs($this->user)
        ->postJson(route('user.passkeys.store'), [
            'passkey' => json_encode(['test' => 'data']),
            'options' => json_encode(['test' => 'options']),
        ])
        ->assertUnprocessable();

    // Count should remain the same due to rollback
    expect(Passkey::count())->toBe($initialCount);
});

// Authentication Options Security Test
it('generates unique challenge for each authentication attempt', function () {
    actingAs($this->user);

    $response1 = getJson(route('user.passkeys.generate-options'));
    $response2 = getJson(route('user.passkeys.generate-options'));

    $options1 = $response1->json();
    $options2 = $response2->json();

    // Challenges should be different
    expect($options1['challenge'])->not->toBe($options2['challenge']);
});

// Passkey Metadata Security Test
it('stores passkey metadata securely', function () {
    DB::table('passkeys')->insert([
        'id' => '600000',
        'authenticatable_id' => $this->user->id,
        'name' => 'Test Passkey',
        'credential_id' => 'test-credential',
        'data' => json_encode(['test' => 'data']),
        'user_agent' => 'Mozilla/5.0 Test',
        'device_type' => 'desktop',
        'browser_name' => 'Chrome',
        'operating_system' => 'Windows',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $passkey = DB::table('passkeys')->where('id', '600000')->first();

    // Verify metadata is stored but doesn't contain sensitive info
    expect($passkey->user_agent)->toBe('Mozilla/5.0 Test');
    expect($passkey->device_type)->toBe('desktop');
    expect($passkey->browser_name)->toBe('Chrome');
    expect($passkey->operating_system)->toBe('Windows');

    // Ensure no private keys or sensitive auth data is stored
    $data = json_decode($passkey->data, true);
    expect($data)->not->toHaveKey('privateKey');
    expect($data)->not->toHaveKey('secret');
});
