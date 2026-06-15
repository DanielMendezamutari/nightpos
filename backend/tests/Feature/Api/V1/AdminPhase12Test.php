<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('allows superadmin to get and update tenant', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $this->getJson("/api/v1/admin/tenants/{$tenantId}", [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonPath('data.tenant.slug', 'casa-demo');

    $this->putJson("/api/v1/admin/tenants/{$tenantId}", [
        'name' => 'Casa Demo Actualizada',
        'slug' => 'casa-demo',
        'status' => 'active',
        'plan_name' => 'enterprise',
        'subscription_starts_at' => '2025-01-01',
        'subscription_ends_at' => '2026-12-31',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonPath('data.tenant.name', 'Casa Demo Actualizada')
        ->assertJsonPath('data.tenant.plan_name', 'ENTERPRISE');
});

it('rejects duplicate tenant slug on update', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $other = TenantModel::query()->create([
        'name' => 'Otra',
        'slug' => 'otra-casa',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $demoId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $this->putJson("/api/v1/admin/tenants/{$demoId}", [
        'name' => 'Demo',
        'slug' => 'otra-casa',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertStatus(422);

    expect($other->slug)->toBe('otra-casa');
});

it('denies tenant owner from updating global tenant', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $this->putJson("/api/v1/admin/tenants/{$tenantId}", [
        'name' => 'Hack',
        'slug' => 'hack',
        'status' => 'active',
    ], nightposOperationalHeaders($token, null))
        ->assertForbidden();
});

it('allows admin to update branch of own tenant', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $branchId = BranchModel::query()->where('code', 'CENTRO')->value('id');

    $this->getJson("/api/v1/admin/branches/{$branchId}", nightposOperationalHeaders($token, null))
        ->assertOk()
        ->assertJsonPath('data.branch.code', 'CENTRO');

    $this->putJson("/api/v1/admin/branches/{$branchId}", [
        'name' => 'Sucursal Centro Editada',
        'code' => 'CENTRO',
        'address' => 'Av. Principal 100',
        'status' => 'active',
    ], nightposOperationalHeaders($token, null))
        ->assertOk()
        ->assertJsonPath('data.branch.name', 'Sucursal Centro Editada')
        ->assertJsonPath('data.branch.address', 'Av. Principal 100');
});

it('denies admin from updating branch of another tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa-2',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $foreignBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Ajena',
        'code' => 'AJENA',
        'status' => 'active',
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->putJson("/api/v1/admin/branches/{$foreignBranch->id}", [
        'name' => 'Intruso',
        'code' => 'AJENA',
        'status' => 'active',
    ], nightposOperationalHeaders($token, null))
        ->assertForbidden();
});

it('allows admin to update product category', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $category = ProductCategoryModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Bebidas',
        'type' => 'beverage',
        'status' => 'active',
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->getJson("/api/v1/product-categories/{$category->id}", nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.category.name', 'Bebidas');

    $this->putJson("/api/v1/product-categories/{$category->id}", [
        'name' => 'Bebidas Premium',
        'type' => 'beverage',
        'status' => 'active',
    ], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.category.name', 'Bebidas Premium');
});

it('denies cashier from updating product category', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $category = ProductCategoryModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Snacks',
        'type' => 'food',
        'status' => 'active',
    ]);

    $token = nightposLoginPin('1234');

    $this->putJson("/api/v1/product-categories/{$category->id}", [
        'name' => 'Snacks Edit',
        'type' => 'food',
        'status' => 'active',
    ], nightposOperationalHeaders($token))
        ->assertForbidden();
});

it('does not allow updating category from another tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Externa',
        'slug' => 'externa',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $foreignCategory = ProductCategoryModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Ajena',
        'type' => 'general',
        'status' => 'active',
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->putJson("/api/v1/product-categories/{$foreignCategory->id}", [
        'name' => 'Robo',
        'type' => 'general',
        'status' => 'active',
    ], nightposOperationalHeaders($token))
        ->assertNotFound();
});
