<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function provisioningSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

/**
 * @return array<string, mixed>
 */
function fullTenantCreatePayload(string $slug, string $username): array
{
    $starterId = PlanModel::query()->where('code', 'STARTER')->value('id');

    return [
        'name' => 'Empresa '.$slug,
        'slug' => $slug,
        'status' => 'active',
        'plan_id' => $starterId,
        'branch' => [
            'name' => 'Sucursal Principal',
            'code' => 'MAIN',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin '.$slug,
            'username' => $username,
            'password' => 'Secret123!',
            'pin' => '4321',
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function fullPlatformSetupPayload(string $slug, string $username): array
{
    return [
        'tenant' => [
            'name' => 'Wizard '.$slug,
            'slug' => $slug,
            'status' => 'active',
            'plan_name' => 'FREE',
        ],
        'branch' => [
            'name' => 'Sede Wizard',
            'code' => 'WZ01',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin Wizard',
            'username' => $username,
            'password' => 'Wizard123!',
            'pin' => '1111',
        ],
    ];
}

function assertTenantIsOperable(string $slug, string $adminUsername): void
{
    $tenant = TenantModel::query()->where('slug', $slug)->first();
    expect($tenant)->not->toBeNull();

    $branchCount = BranchModel::query()->where('tenant_id', $tenant->id)->count();
    expect($branchCount)->toBeGreaterThan(0);

    $admin = UserModel::query()
        ->where('tenant_id', $tenant->id)
        ->where('username', $adminUsername)
        ->first();
    expect($admin)->not->toBeNull();

    $roles = RoleModel::query()->where('tenant_id', $tenant->id)->get();
    expect($roles->count())->toBeGreaterThan(0);

    foreach ($roles as $role) {
        expect($role->permissions()->count())->toBeGreaterThan(0);
    }

    expect(PermissionModel::query()->count())->toBeGreaterThan(0);
}

it('creates operable tenant from platform setup wizard', function () {
    $response = $this->postJson('/api/v1/admin/platform/setup', fullPlatformSetupPayload('wizard-tenant', 'admin.wizard'), [
        'Authorization' => 'Bearer '.provisioningSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    expect($response->json('data.tenant.slug'))->toBe('wizard-tenant')
        ->and($response->json('data.branch.code'))->toBe('WZ01')
        ->and($response->json('data.admin.username'))->toBe('admin.wizard')
        ->and($response->json('data.roles'))->toContain('tenant_owner');

    assertTenantIsOperable('wizard-tenant', 'admin.wizard');
});

it('creates operable tenant from admin tenants endpoint', function () {
    $response = $this->postJson('/api/v1/admin/tenants', fullTenantCreatePayload('empresa-nueva', 'admin.empresa'), [
        'Authorization' => 'Bearer '.provisioningSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    expect($response->json('data.tenant.slug'))->toBe('empresa-nueva')
        ->and($response->json('data.branch.code'))->toBe('MAIN')
        ->and($response->json('data.admin.username'))->toBe('admin.empresa')
        ->and($response->json('data.roles'))->toContain('tenant_owner');

    assertTenantIsOperable('empresa-nueva', 'admin.empresa');
});

it('rejects empty tenant creation without branch and admin', function () {
    $this->postJson('/api/v1/admin/tenants', [
        'name' => 'Solo Tenant',
        'slug' => 'solo-tenant',
    ], [
        'Authorization' => 'Bearer '.provisioningSuperToken(),
        'Accept' => 'application/json',
    ])->assertStatus(422);

    expect(TenantModel::query()->where('slug', 'solo-tenant')->exists())->toBeFalse();
});
