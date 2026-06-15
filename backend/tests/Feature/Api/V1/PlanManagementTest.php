<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function planMgmtSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

function planMgmtTenantAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

it('creates a plan as superadmin', function () {
    $response = $this->postJson('/api/v1/admin/platform/plans', [
        'name' => 'Custom Pro',
        'code' => 'CUSTOM_PRO',
        'description' => 'Plan de prueba',
        'monthly_price' => 99,
        'yearly_price' => 990,
        'is_active' => true,
        'display_order' => 10,
    ], [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    expect($response->json('data.plan.code'))->toBe('CUSTOM_PRO');
    expect(PlanModel::query()->where('code', 'CUSTOM_PRO')->exists())->toBeTrue();
});

it('updates a plan as superadmin', function () {
    $planId = PlanModel::query()->where('code', 'STARTER')->value('id');

    $this->putJson("/api/v1/admin/platform/plans/{$planId}", [
        'name' => 'Starter Plus',
        'code' => 'STARTER',
        'description' => 'Actualizado',
        'monthly_price' => 59,
        'yearly_price' => 590,
        'is_active' => true,
        'display_order' => 2,
    ], [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertOk()
        ->assertJsonPath('data.plan.name', 'Starter Plus');
});

it('assigns plan to tenant', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $starterId = PlanModel::query()->where('code', 'STARTER')->value('id');

    $this->putJson("/api/v1/admin/tenants/{$tenantId}", [
        'name' => 'Casa Demo',
        'slug' => 'casa-demo',
        'status' => 'active',
        'plan_id' => $starterId,
    ], [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertOk()
        ->assertJsonPath('data.tenant.plan_id', $starterId)
        ->assertJsonPath('data.tenant.plan_name', 'STARTER');
});

it('returns plan limits', function () {
    $planId = PlanModel::query()->where('code', 'FREE')->value('id');

    $response = $this->getJson("/api/v1/admin/platform/plans/{$planId}/limits", [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertOk();

    $keys = collect($response->json('data.limits'))->pluck('limit_key')->all();
    expect($keys)->toContain('branches', 'users', 'products');
});

it('reports usage for tenant without plan', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    TenantModel::query()->where('id', $tenantId)->update([
        'plan_id' => null,
        'plan_name' => null,
    ]);

    $response = $this->getJson("/api/v1/admin/tenants/{$tenantId}", [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertOk();

    expect($response->json('data.tenant.plan_usage.plan'))->toBeNull()
        ->and($response->json('data.tenant.plan_usage.usage'))->toBeArray();
});

it('rejects assigning inactive plan to tenant', function () {
    $inactive = PlanModel::query()->create([
        'name' => 'Legacy',
        'code' => 'LEGACY',
        'monthly_price' => 0,
        'yearly_price' => 0,
        'is_active' => false,
        'display_order' => 99,
    ]);

    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $this->putJson("/api/v1/admin/tenants/{$tenantId}", [
        'name' => 'Casa Demo',
        'slug' => 'casa-demo',
        'status' => 'active',
        'plan_id' => $inactive->id,
    ], [
        'Authorization' => 'Bearer '.planMgmtSuperToken(),
        'Accept' => 'application/json',
    ])->assertStatus(422);
});

it('denies plan management to tenant admin', function () {
    $this->getJson('/api/v1/admin/platform/plans', [
        'Authorization' => 'Bearer '.planMgmtTenantAdminToken(),
        'X-Tenant-Slug' => 'casa-demo',
        'Accept' => 'application/json',
    ])->assertForbidden();
});

it('denies plan creation to non superadmin even with tenant list permission', function () {
    $this->postJson('/api/v1/admin/platform/plans', [
        'name' => 'Hack',
        'code' => 'HACK',
        'monthly_price' => 1,
        'yearly_price' => 1,
    ], [
        'Authorization' => 'Bearer '.planMgmtTenantAdminToken(),
        'X-Tenant-Slug' => 'casa-demo',
        'Accept' => 'application/json',
    ])->assertForbidden();
});
