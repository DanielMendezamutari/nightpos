<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('ignores foreign tenant slug header and keeps own tenant context', function () {
    TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->getJson('/api/v1/tenant/current', array_merge(
        nightposOperationalHeaders($token, null),
        ['X-Tenant-Slug' => 'otra-casa']
    ))
        ->assertOk()
        ->assertJsonPath('data.slug', 'casa-demo');
});

it('denies access to branch where user has no permission', function () {
    $token = nightposLoginPin('1234');

    $this->getJson('/api/v1/branches/current', nightposOperationalHeaders($token, 'INVALID'))
        ->assertForbidden();
});

it('allows superadmin to list global tenants', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->getJson('/api/v1/admin/tenants', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['tenants']]);
});

it('denies cashier from listing global tenants', function () {
    $token = nightposLoginPin('1234');

    $this->getJson('/api/v1/admin/tenants', nightposOperationalHeaders($token))
        ->assertForbidden();
});

it('returns only branches allowed for the user', function () {
    $token = nightposLoginPin('1234');

    $response = $this->getJson('/api/v1/branches/available', nightposOperationalHeaders($token));

    $response->assertOk();

    $codes = collect($response->json('data.branches'))->pluck('code')->all();

    expect($codes)->toContain('CENTRO')
        ->and($codes)->not->toContain('INVALID');
});

it('resolves tenant and branch on login response', function () {
    $response = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '1234',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.username', 'cajero.demo');

    $token = (string) $response->json('data.token');

    $this->getJson('/api/v1/tenant/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.slug', 'casa-demo');

    $this->getJson('/api/v1/branches/current', nightposOperationalHeaders($token, 'CENTRO'))
        ->assertOk()
        ->assertJsonPath('data.code', 'CENTRO');
});

it('allows tenant owner to list users but not global tenants', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->getJson('/api/v1/admin/tenants', nightposOperationalHeaders($token, null))
        ->assertForbidden();

    $this->getJson('/api/v1/admin/users', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonStructure(['data' => ['users']]);
});
