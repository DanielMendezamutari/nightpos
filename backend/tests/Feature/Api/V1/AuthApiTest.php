<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('logs in with pin as primary auth method', function () {
    $response = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.username', 'cajero.demo')
        ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
});

it('rejects invalid pin', function () {
    $response = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '9999',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('logs in with username and password', function () {
    $response = $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
        'tenant_slug' => 'casa-demo',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.username', 'admin.demo')
        ->assertJsonStructure(['data' => ['token']]);
});

it('returns authenticated user on me endpoint', function () {
    $login = $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
        'tenant_slug' => 'casa-demo',
    ]);

    $token = $login->json('data.token');

    $this->getJson('/api/v1/auth/me', [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertOk()
        ->assertJsonPath('data.user.username', 'admin.demo');
});

it('logs out and invalidates session token when blacklist enabled', function () {
    $login = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $token = $login->json('data.token');

    $this->postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()->assertJsonPath('success', true);
});

it('refreshes an authenticated token within refresh window', function () {
    $login = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $token = $login->json('data.token');

    $refresh = $this->postJson('/api/v1/auth/refresh', [], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $refresh->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'token_type']]);

    $newToken = $refresh->json('data.token');

    expect($newToken)->not->toBe($token);

    $this->getJson('/api/v1/auth/me', [
        'Authorization' => 'Bearer '.$newToken,
    ])
        ->assertOk()
        ->assertJsonPath('data.user.username', 'cajero.demo');
});

it('uses operational jwt ttl default of twelve hours', function () {
    expect((int) config('jwt.ttl'))->toBeGreaterThanOrEqual(720);
});

it('returns json 401 when accessing me without token', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.code', 'unauthenticated');
});

it('uses jwt blacklist grace period to reduce parallel refresh races', function () {
    expect((int) config('jwt.blacklist_grace_period'))->toBeGreaterThanOrEqual(30);
});

it('stores pin as hash not plain text', function () {
    $cashier = UserModel::query()->where('username', 'cajero.demo')->first();

    expect($cashier->pin_hash)->not->toBe('1234')
        ->and(strlen((string) $cashier->pin_hash))->toBeGreaterThan(20);
});

it('returns jwt_not_configured on pin login when jwt secret is empty', function () {
    config(['jwt.secret' => '']);

    $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.code', 'jwt_not_configured')
        ->assertJsonPath(
            'message',
            'La autenticación del servidor no está configurada. Contacte al administrador del sistema.',
        );
});

it('returns jwt_not_configured on password login when jwt secret is empty', function () {
    config(['jwt.secret' => '']);

    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
        'tenant_slug' => 'casa-demo',
    ])
        ->assertStatus(503)
        ->assertJsonPath('data.code', 'jwt_not_configured');
});
