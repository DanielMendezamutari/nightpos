<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function nightposCreateProduct(string $token, array $payload = []): \Illuminate\Testing\TestResponse
{
    return test()->postJson('/api/v1/products', array_merge([
        'name' => 'Paceña',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'status' => 'active',
    ], $payload), nightposOperationalHeaders($token));
}

it('allows admin to create a product', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposCreateProduct($token)
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.product.name', 'Paceña')
        ->assertJsonPath('data.product.tenant_id', TenantModel::query()->where('slug', 'casa-demo')->value('id'));
});

it('denies cashier from creating a product', function () {
    $token = nightposLoginPin('1234');

    nightposCreateProduct($token)->assertForbidden();
});

it('allows cashier to quick create product with prices', function () {
    $category = \App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel::query()->create([
        'tenant_id' => TenantModel::query()->where('slug', 'casa-demo')->value('id'),
        'name' => 'Rápidos',
        'status' => 'active',
    ]);

    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/products/quick', [
        'name' => 'Shot Express',
        'category_id' => $category->id,
        'solo_price' => 20,
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.product.name', 'Shot Express');
});

it('allows waiter to list only active products', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Activo',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Inactivo',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'inactive',
    ]);

    $waiterToken = nightposLoginPin('5678');

    $response = $this->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken));

    $response->assertOk();

    $names = collect($response->json('data.products'))->pluck('name')->all();

    expect($names)->toContain('Activo')
        ->and($names)->not->toContain('Inactivo');
});

it('requires tenant context to create a product', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->postJson('/api/v1/products', [
        'name' => 'Sin Tenant',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('stores SOLO_CLIENTE price correctly', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token, ['name' => 'Paceña SOLO'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 40,
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.price.sale_mode', 'SOLO_CLIENTE')
        ->assertJsonPath('data.price.price', '40.00')
        ->assertJsonPath('data.price.girl_amount', null);
});

it('validates CON_ACOMPANANTE split equals price', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token, ['name' => 'Paceña Duo'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 80,
        'girl_amount' => 50,
        'house_amount' => 20,
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 80,
        'girl_amount' => 40,
        'house_amount' => 40,
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.price.girl_amount', '40.00')
        ->assertJsonPath('data.price.house_amount', '40.00');
});

it('rejects negative prices', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token)->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => -5,
    ], nightposOperationalHeaders($token))
        ->assertStatus(422);
});

it('rejects duplicate active sale mode for same scope', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token)->json('data.product.id');

    $payload = [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 40,
    ];

    $this->postJson("/api/v1/products/{$productId}/prices", $payload, nightposOperationalHeaders($token))
        ->assertCreated();

    $this->postJson("/api/v1/products/{$productId}/prices", $payload, nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Ya existe un precio activo para esta modalidad en el mismo ámbito.');
});

it('does not expose products from another tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $foreignProduct = ProductModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => null,
        'name' => 'Producto Ajeno',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->getJson('/api/v1/products/'.$foreignProduct->id, nightposOperationalHeaders($token))
        ->assertNotFound();
});

it('allows branch-specific price', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchCentro = BranchModel::query()->where('code', 'CENTRO')->first();

    $branchNorte = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ]);

    $productId = nightposCreateProduct($token, ['name' => 'Paceña Branch'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 40,
    ], nightposOperationalHeaders($token, 'CENTRO'))
        ->assertCreated()
        ->assertJsonPath('data.price.branch_id', $branchCentro->id);

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 45,
        'branch_id' => $branchNorte->id,
    ], nightposOperationalHeaders($token, 'NORTE'))
        ->assertCreated()
        ->assertJsonPath('data.price.branch_id', $branchNorte->id)
        ->assertJsonPath('data.price.price', '45.00');
});

it('includes active prices when requested', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token, ['name' => 'Con Precios Embed'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 40,
    ], nightposOperationalHeaders($token))->assertCreated();

    $response = $this->getJson('/api/v1/products?include=active_prices', nightposOperationalHeaders($token))
        ->assertOk();

    $row = collect($response->json('data.products'))
        ->firstWhere('id', $productId);

    expect($row)->not->toBeNull()
        ->and($row['active_prices'])->toHaveCount(1)
        ->and($row['active_prices'][0]['sale_mode'])->toBe('SOLO_CLIENTE')
        ->and($row['has_active_pricing'])->toBeTrue();
});

it('returns active prices on product detail', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token, ['name' => 'Detalle Precios'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 25,
    ], nightposOperationalHeaders($token))->assertCreated();

    $this->getJson("/api/v1/products/{$productId}", nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.product.active_prices.0.price', '25.00')
        ->assertJsonPath('data.product.has_active_pricing', true);
});

it('replaces active price and keeps history', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $productId = nightposCreateProduct($token, ['name' => 'Reemplazo'])->json('data.product.id');

    $this->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 40,
    ], nightposOperationalHeaders($token))->assertCreated();

    $this->putJson("/api/v1/products/{$productId}/prices/active", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 45,
    ], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.price.price', '45.00');

    $prices = $this->getJson("/api/v1/products/{$productId}/prices", nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.prices');

    expect(collect($prices)->where('status', 'active')->where('sale_mode', 'SOLO_CLIENTE'))->toHaveCount(1)
        ->and(collect($prices)->where('status', 'inactive')->where('sale_mode', 'SOLO_CLIENTE'))->toHaveCount(1);
});
