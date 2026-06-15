<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function roleAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function roleAdminHeaders(string $token, ?string $tenantSlug = null): array
{
    $headers = [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ];

    if ($tenantSlug !== null) {
        $headers['X-Tenant-Slug'] = $tenantSlug;
    }

    return $headers;
}

function roleAdminCashierRoleId(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    return (int) RoleModel::query()
        ->where('tenant_id', $tenantId)
        ->where('slug', 'cashier')
        ->value('id');
}

function roleAdminTenantOwnerRoleId(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    return (int) RoleModel::query()
        ->where('tenant_id', $tenantId)
        ->where('slug', 'tenant_owner')
        ->value('id');
}

function roleAdminCashierPermissionSlugs(): array
{
    $roleId = roleAdminCashierRoleId();
    $role = RoleModel::query()->with('permissions')->findOrFail($roleId);

    return $role->permissions->pluck('slug')->all();
}

it('1. admin lista roles de su tenant', function () {
    $token = roleAdminToken();

    $response = test()->getJson('/api/v1/admin/roles', roleAdminHeaders($token))
        ->assertOk();

    $slugs = collect($response->json('data.roles'))->pluck('slug')->all();

    expect($slugs)->toContain('tenant_owner', 'cashier', 'waiter', 'cleaning')
        ->and($slugs)->not->toContain('super_admin');
});

it('2. admin no ve roles de otro tenant', function () {
    $token = roleAdminToken();
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro Bar',
        'slug' => 'otro-bar',
        'status' => 'active',
    ]);

    $foreignRole = RoleModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Rol Ajeno',
        'slug' => 'custom_foreign',
    ]);

    test()->getJson('/api/v1/admin/roles/'.$foreignRole->id, roleAdminHeaders($token))
        ->assertNotFound();
});

it('3. admin no ve permisos platform.*', function () {
    $token = roleAdminToken();

    $response = test()->getJson('/api/v1/admin/permissions', roleAdminHeaders($token))
        ->assertOk();

    $slugs = collect($response->json('data.groups'))
        ->flatMap(fn (array $group) => collect($group['permissions'])->pluck('slug'))
        ->all();

    foreach ($slugs as $slug) {
        expect($slug)->not->toStartWith('platform.')
            ->and($slug)->not->toStartWith('admin.tenants.')
            ->and($slug)->not->toStartWith('billing.');
    }
});

it('4. admin actualiza permisos permitidos', function () {
    $token = roleAdminToken();
    $roleId = roleAdminCashierRoleId();
    $slugs = roleAdminCashierPermissionSlugs();

    expect($slugs)->toContain('orders.create');

    $updated = array_values(array_diff($slugs, ['orders.create']));

    test()->putJson("/api/v1/admin/roles/{$roleId}/permissions", [
        'permission_slugs' => $updated,
    ], roleAdminHeaders($token))
        ->assertOk();

    $role = RoleModel::query()->with('permissions')->findOrFail($roleId);
    expect($role->permissions->pluck('slug')->all())->not->toContain('orders.create');
});

it('5. admin no puede asignar permiso prohibido', function () {
    $token = roleAdminToken();
    $roleId = roleAdminCashierRoleId();
    $slugs = roleAdminCashierPermissionSlugs();
    $slugs[] = 'platform.setup';

    test()->putJson("/api/v1/admin/roles/{$roleId}/permissions", [
        'permission_slugs' => $slugs,
    ], roleAdminHeaders($token))
        ->assertStatus(422);
});

it('6. admin no puede editar rol global', function () {
    $token = roleAdminToken();
    $globalRoleId = (int) RoleModel::query()->where('slug', 'super_admin')->whereNull('tenant_id')->value('id');

    test()->getJson('/api/v1/admin/roles/'.$globalRoleId, roleAdminHeaders($token))
        ->assertNotFound();

    test()->putJson('/api/v1/admin/roles/'.$globalRoleId.'/permissions', [
        'permission_slugs' => ['orders.access'],
    ], roleAdminHeaders($token))
        ->assertNotFound();
});

it('7. admin no puede borrar rol con usuarios', function () {
    $token = roleAdminToken();
    $roleId = roleAdminCashierRoleId();

    test()->deleteJson('/api/v1/admin/roles/'.$roleId, [], roleAdminHeaders($token))
        ->assertStatus(422);
});

it('8. admin crea rol local', function () {
    $token = roleAdminToken();

    test()->postJson('/api/v1/admin/roles', [
        'name' => 'Encargado turno',
        'slug' => 'shift_manager',
    ], roleAdminHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.role.slug', 'shift_manager');

    expect(RoleModel::query()->where('slug', 'shift_manager')->exists())->toBeTrue();
});

it('9. admin no puede dejar tenant sin rol administrador', function () {
    $token = roleAdminToken();
    $roleId = roleAdminTenantOwnerRoleId();
    $slugs = RoleModel::query()->with('permissions')->findOrFail($roleId)->permissions->pluck('slug')->all();
    $withoutAdmin = array_values(array_diff($slugs, ['roles.permissions.update']));

    test()->putJson("/api/v1/admin/roles/{$roleId}/permissions", [
        'permission_slugs' => $withoutAdmin,
    ], roleAdminHeaders($token))
        ->assertStatus(422);
});

it('10. superadmin puede ver roles por tenant con contexto', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    test()->getJson('/api/v1/admin/roles', roleAdminHeaders($token, 'casa-demo'))
        ->assertOk()
        ->assertJsonFragment(['slug' => 'cashier']);
});

it('11. garzón no accede al módulo', function () {
    $token = nightposLoginPin('5678');

    test()->getJson('/api/v1/admin/roles', roleAdminHeaders($token))
        ->assertForbidden();
});

it('12. limpieza no accede al módulo', function () {
    $token = nightposLoginPin('3333');

    test()->getJson('/api/v1/admin/roles', roleAdminHeaders($token))
        ->assertForbidden();
});
