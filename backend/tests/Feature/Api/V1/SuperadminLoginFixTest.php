<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('logs in superadmin with password without tenant_slug', function () {
    $response = $this->postJson('/api/v1/auth/login-password', [
        'username' => 'superadmin',
        'password' => 'SuperAdmin123!',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.username', 'superadmin')
        ->assertJsonPath('data.user.role', 'super_admin');
});

it('logs in superadmin even when a tenant_slug is sent by mistake', function () {
    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'superadmin',
        'password' => 'SuperAdmin123!',
        'tenant_slug' => 'casa-demo',
    ])->assertOk()
        ->assertJsonPath('data.user.role', 'super_admin');
});

it('allows superadmin global access to admin tenants without branch header', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->getJson('/api/v1/admin/tenants', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('returns tenant current in global mode for superadmin without tenant header', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->getJson('/api/v1/tenant/current', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.tenant', null);
});

it('rejects tenant admin login without tenant_slug', function () {
    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
    ])->assertUnauthorized();
});

it('allows tenant admin login with tenant_slug', function () {
    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
        'tenant_slug' => 'casa-demo',
    ])->assertOk()
        ->assertJsonPath('data.user.username', 'admin.demo');
});

it('logs in superadmin with uppercase username normalized', function () {
    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'SuperAdmin',
        'password' => 'SuperAdmin123!',
    ])->assertOk()
        ->assertJsonPath('data.user.role', 'super_admin');
});

it('rejects empty tenant_slug string for tenant admin', function () {
    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'admin.demo',
        'password' => 'AdminDemo123!',
        'tenant_slug' => '',
    ])->assertUnauthorized();
});

it('does not treat cashier as superadmin', function () {
    nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->postJson('/api/v1/auth/login-password', [
        'username' => 'cajero.demo',
        'password' => 'wrong-password',
        'tenant_slug' => 'casa-demo',
    ])->assertUnauthorized();
});

it('keeps pin login working for cashier', function () {
    $response = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.username', 'cajero.demo');
});
