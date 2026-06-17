<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function comboGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function comboGirlId2(): int
{
    return (int) UserModel::query()->where('username', 'chica2.demo')->value('id');
}

function comboSeedProduct(int $braceletUnits = 6): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Combo 6 Cervezas Test',
        'product_type' => 'beverage',
        'unit' => 'combo',
        'status' => 'active',
        'settlement_behavior' => 'GIRL_BRACELET_ALLOCATION',
        'bracelet_units_per_line' => $braceletUnits,
        'requires_allocation' => true,
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 120,
        'girl_amount' => 60,
        'house_amount' => 60,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

function comboCreateOrderWithItem(string $waiterToken, int $productId, int $quantity = 1): array
{
    nightposEnsureShiftOpen();
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Combo Mesa',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken));
    $orderResponse->assertCreated();
    $orderId = (int) $orderResponse->json('data.order.id');

    $itemResponse = test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => $quantity,
    ], nightposOperationalHeaders($waiterToken));
    $itemResponse->assertCreated();

    $itemId = (int) $itemResponse->json('data.order.items.0.id');

    return ['order_id' => $orderId, 'product_id' => $productId, 'item_id' => $itemId];
}

function comboSyncAllocations(string $token, int $orderId, int $itemId, array $allocations): \Illuminate\Testing\TestResponse
{
    return test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => $allocations,
    ], nightposOperationalHeaders($token));
}

it('combo product requires allocation flags in catalog', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $productId = comboSeedProduct();

    $response = test()->getJson("/api/v1/products/{$productId}", nightposOperationalHeaders($admin));

    $response->assertOk()
        ->assertJsonPath('data.product.requires_allocation', true)
        ->assertJsonPath('data.product.bracelet_units_per_line', 6)
        ->assertJsonPath('data.product.settlement_behavior', 'GIRL_BRACELET_ALLOCATION');
});

it('combo without allocations cannot send to bar', function () {
    $waiter = nightposLoginPin('1234');
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertStatus(422);
});

it('combo rejects 5 bracelet allocations', function () {
    $waiter = nightposLoginPin('1234');
    $girlId = comboGirlId();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $created['order_id'], $itemId, [
        ['girl_user_id' => $girlId, 'units' => 5],
    ])->assertStatus(422);
});

it('combo rejects 7 bracelet allocations', function () {
    $waiter = nightposLoginPin('1234');
    $girlId = comboGirlId();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $created['order_id'], $itemId, [
        ['girl_user_id' => $girlId, 'units' => 7],
    ])->assertStatus(422);
});

it('combo accepts 6 bracelet allocations and sends to bar', function () {
    $waiter = nightposLoginPin('1234');
    $girlId = comboGirlId();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $orderId, $itemId, [
        ['girl_user_id' => $girlId, 'units' => 6],
    ])->assertOk()
        ->assertJsonPath('data.order.items.0.allocation_complete', true)
        ->assertJsonPath('data.order.items.0.allocated_bracelet_units', 6);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();
});

it('two combos require 12 bracelet allocations', function () {
    $waiter = nightposLoginPin('1234');
    $girlId = comboGirlId();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId, 2);
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $created['order_id'], $itemId, [
        ['girl_user_id' => $girlId, 'units' => 6],
    ])->assertStatus(422);

    comboSyncAllocations($waiter, $created['order_id'], $itemId, [
        ['girl_user_id' => $girlId, 'units' => 12],
    ])->assertOk()
        ->assertJsonPath('data.order.items.0.required_bracelet_units', 12);
});

it('charge creates sale item allocations snapshot', function () {
    $waiter = nightposLoginPin('1234');
    $cashier = nightposLoginPin('1234');
    $girl1 = comboGirlId();
    $girl2 = comboGirlId2();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $orderId, $itemId, [
        ['girl_user_id' => $girl1, 'units' => 3],
        ['girl_user_id' => $girl2, 'units' => 3],
    ])->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 200], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 120]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    expect(SaleItemAllocationModel::query()->count())->toBe(2);
});

it('settlements generate GIRL_BRACELET_ALLOCATION per girl', function () {
    $waiter = nightposLoginPin('1234');
    $cashier = nightposLoginPin('1234');
    $girl1 = comboGirlId();
    $girl2 = comboGirlId2();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $orderId, $itemId, [
        ['girl_user_id' => $girl1, 'units' => 3],
        ['girl_user_id' => $girl2, 'units' => 3],
    ]);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 200], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 120]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $items = StaffSettlementItemModel::query()->where('source_type', 'GIRL_BRACELET_ALLOCATION')->get();

    expect($items)->toHaveCount(2);
    expect($items->sum(fn ($i) => (float) $i->amount))->toBe(60.0);
    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(0);
});

it('does not duplicate settlement per allocation on regenerate', function () {
    $waiter = nightposLoginPin('1234');
    $cashier = nightposLoginPin('1234');
    $girlId = comboGirlId();
    $productId = comboSeedProduct();
    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    comboSyncAllocations($waiter, $orderId, $itemId, [
        ['girl_user_id' => $girlId, 'units' => 6],
    ]);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 200], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 120]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_BRACELET_ALLOCATION')->count())->toBe(1);
});

it('simple CON_ACOMPANANTE product still works unchanged', function () {
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('1234');
    $girlId = comboGirlId();

    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashier))->assertCreated();

    $productId = nightposSeedOrderProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 80, 'girl_amount' => 40, 'house_amount' => 40],
    ]);

    $created = comboCreateOrderWithItem($waiter, $productId);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    test()->patchJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($waiter))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 80]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(1);
    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_BRACELET_ALLOCATION')->count())->toBe(0);
});

it('direct sale blocks combo with allocation', function () {
    $cashier = nightposLoginPin('1234');
    $productId = comboSeedProduct();

    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashier))->assertCreated();

    test()->postJson('/api/v1/direct-sales', [
        'items' => [[
            'product_id' => $productId,
            'sale_mode' => 'CON_ACOMPANANTE',
            'quantity' => 1,
            'girl_user_id' => comboGirlId(),
        ]],
        'payments' => [['method' => 'CASH', 'amount' => 120]],
    ], nightposOperationalHeaders($cashier))
        ->assertStatus(422);
});
