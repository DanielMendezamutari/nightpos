<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function posCatalogTenantId(): int
{
    return (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
}

function posCatalogCreateCategory(string $name = 'Bebidas'): int
{
    return (int) ProductCategoryModel::query()->create([
        'tenant_id' => posCatalogTenantId(),
        'name' => $name,
        'status' => 'active',
    ])->id;
}

function posCatalogCreateProduct(string $name, ?int $categoryId = null, string $status = 'active'): int
{
    return (int) ProductModel::query()->create([
        'tenant_id' => posCatalogTenantId(),
        'category_id' => $categoryId,
        'name' => $name,
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => $status,
    ])->id;
}

function posCatalogPrice(int $productId, string $token): void
{
    test()->postJson("/api/v1/products/{$productId}/prices", [
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 25,
    ], nightposOperationalHeaders($token))->assertCreated();
}

it('returns categories and meta without products when no filter is applied', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory();
    $pricedId = posCatalogCreateProduct('Paceña POS', $categoryId);
    posCatalogCreateProduct('Sin Precio POS', $categoryId);
    posCatalogPrice($pricedId, $token);

    $response = $this->getJson('/api/v1/products/pos-catalog', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($response->json('data.products'))->toBeArray()->toBeEmpty()
        ->and($response->json('data.categories'))->not->toBeEmpty()
        ->and($response->json('data.meta.sellable_count'))->toBeGreaterThanOrEqual(1)
        ->and($response->json('data.meta.unpriced_count'))->toBeGreaterThanOrEqual(1);
});

it('returns only sellable products by default', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory('Vendibles');
    $sellableId = posCatalogCreateProduct('Vendible POS', $categoryId);
    posCatalogCreateProduct('No Vendible POS', $categoryId);
    posCatalogPrice($sellableId, $token);

    $response = $this->getJson('/api/v1/products/pos-catalog?search=vendible', nightposOperationalHeaders($token))
        ->assertOk();

    $names = collect($response->json('data.products'))->pluck('name')->all();

    expect($names)->toContain('Vendible POS')
        ->and($names)->not->toContain('No Vendible POS');
});

it('returns unpriced active products when unpriced_only is set', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory('Sin precio');
    $pricedId = posCatalogCreateProduct('Con Precio Cat', $categoryId);
    $unpricedId = posCatalogCreateProduct('Sin Precio Cat', $categoryId);
    posCatalogPrice($pricedId, $token);

    $response = $this->getJson('/api/v1/products/pos-catalog?unpriced_only=1&sellable_only=0', nightposOperationalHeaders($token))
        ->assertOk();

    $ids = collect($response->json('data.products'))->pluck('id')->all();

    expect($ids)->toContain($unpricedId)
        ->and($ids)->not->toContain($pricedId);
});

it('filters products by category', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $drinksId = posCatalogCreateCategory('Tragos');
    $foodId = posCatalogCreateCategory('Snacks');
    $drinkProductId = posCatalogCreateProduct('Gin POS', $drinksId);
    $foodProductId = posCatalogCreateProduct('Papas POS', $foodId);
    posCatalogPrice($drinkProductId, $token);
    posCatalogPrice($foodProductId, $token);

    $response = $this->getJson("/api/v1/products/pos-catalog?category_id={$drinksId}", nightposOperationalHeaders($token))
        ->assertOk();

    $names = collect($response->json('data.products'))->pluck('name')->all();

    expect($names)->toContain('Gin POS')
        ->and($names)->not->toContain('Papas POS');
});

it('searches products with at least two characters', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory();
    $targetId = posCatalogCreateProduct('Whisky Especial POS', $categoryId);
    posCatalogCreateProduct('Cerveza POS', $categoryId);
    posCatalogPrice($targetId, $token);

    $this->getJson('/api/v1/products/pos-catalog?search=w', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.products', []);

    $response = $this->getJson('/api/v1/products/pos-catalog?search=wh', nightposOperationalHeaders($token))
        ->assertOk();

    expect(collect($response->json('data.products'))->pluck('name')->all())
        ->toContain('Whisky Especial POS');
});

it('returns products by explicit product ids', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory();
    $firstId = posCatalogCreateProduct('Favorito POS', $categoryId);
    $secondId = posCatalogCreateProduct('Otro POS', $categoryId);
    posCatalogPrice($firstId, $token);
    posCatalogPrice($secondId, $token);

    $response = $this->getJson("/api/v1/products/pos-catalog?product_ids={$firstId}", nightposOperationalHeaders($token))
        ->assertOk();

    $ids = collect($response->json('data.products'))->pluck('id')->all();

    expect($ids)->toBe([$firstId]);
});

it('limits result count', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $categoryId = posCatalogCreateCategory('Masivos');

    for ($i = 1; $i <= 30; $i++) {
        $id = posCatalogCreateProduct("Bulk POS {$i}", $categoryId);
        posCatalogPrice($id, $token);
    }

    $response = $this->getJson("/api/v1/products/pos-catalog?category_id={$categoryId}&limit=10", nightposOperationalHeaders($token))
        ->assertOk();

    expect($response->json('data.products'))->toHaveCount(10)
        ->and($response->json('data.meta.limit'))->toBe(10)
        ->and($response->json('data.meta.has_more'))->toBeTrue();
});

it('does not expose products from another tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa POS',
        'slug' => 'otra-casa-pos',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $foreignProductId = posCatalogCreateProduct('Ajeno POS');

    ProductModel::query()->whereKey($foreignProductId)->update([
        'tenant_id' => $otherTenant->id,
        'name' => 'Ajeno POS',
    ]);

    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $response = $this->getJson("/api/v1/products/pos-catalog?product_ids={$foreignProductId}", nightposOperationalHeaders($token))
        ->assertOk();

    expect($response->json('data.products'))->toBeEmpty();
});

it('allows waiter to list only active products in pos catalog', function () {
    $categoryId = posCatalogCreateCategory('Garzón');
    $activeId = posCatalogCreateProduct('Activo Garzón', $categoryId);
    posCatalogCreateProduct('Inactivo Garzón', $categoryId, 'inactive');
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    posCatalogPrice($activeId, $token);

    $waiterToken = nightposLoginPin('5678');

    $response = $this->getJson('/api/v1/products/pos-catalog?search=garz', nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $names = collect($response->json('data.products'))->pluck('name')->all();

    expect($names)->toContain('Activo Garzón')
        ->and($names)->not->toContain('Inactivo Garzón');
});
