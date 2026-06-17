<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function companionGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function companionGirlName(): string
{
    return (string) UserModel::query()->where('username', 'chica.centro')->value('name');
}

function companionWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function companionCashierToken(): string
{
    return nightposLoginPin('1234');
}

function companionProductId(): int
{
    return nightposSeedOrderProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 80, 'girl_amount' => 40, 'house_amount' => 40],
    ]);
}

function companionCreateOrderWithGirl(string $token, ?int $girlId = null): array
{
    $girlId ??= companionGirlId();
    $productId = companionProductId();

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Acompañante',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))->assertCreated()->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($token))->assertCreated();

    return ['order_id' => $orderId, 'girl_id' => $girlId, 'product_id' => $productId];
}

function companionOrderItem(array $orderResponse): array
{
    return $orderResponse['data']['order']['items'][0] ?? [];
}

// ─── 1. order detail con CON_ACOMPANANTE devuelve girl_name ───────────────────

it('order detail includes girl_name for CON_ACOMPANANTE simple item', function () {
    $waiter = companionWaiterToken();
    $created = companionCreateOrderWithGirl($waiter);

    $response = test()->getJson("/api/v1/orders/{$created['order_id']}", nightposOperationalHeaders($waiter))
        ->assertOk();

    $item = companionOrderItem($response->json());

    expect($item['sale_mode'])->toBe('CON_ACOMPANANTE')
        ->and($item['girl_user_id'])->toBe($created['girl_id'])
        ->and($item['girl_name'])->toBe(companionGirlName())
        ->and($item['requires_allocation'] ?? false)->toBeFalse();
});

// ─── 2. precheck devuelve girl_name ──────────────────────────────────────────

it('order precheck includes girl_name for CON_ACOMPANANTE simple item', function () {
    $waiter = companionWaiterToken();
    $created = companionCreateOrderWithGirl($waiter);

    $response = test()->getJson("/api/v1/orders/{$created['order_id']}/precheck", nightposOperationalHeaders($waiter))
        ->assertOk();

    $item = $response->json('data.precheck.order.items.0');

    expect($item['girl_name'])->toBe(companionGirlName());
});

// ─── 3. venta/ticket devuelve girl_name ──────────────────────────────────────

it('charged sale detail includes girl_name for CON_ACOMPANANTE simple item', function () {
    $waiter = companionWaiterToken();
    $cashier = companionCashierToken();
    nightposOpenCashSession($cashier);

    $created = companionCreateOrderWithGirl($waiter);
    $orderId = $created['order_id'];

    $charge = test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 80]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    $saleId = (int) $charge->json('data.sale.id');

    $sale = test()->getJson("/api/v1/sales/{$saleId}", nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data.sale.items.0');

    expect($sale['sale_mode'])->toBe('CON_ACOMPANANTE')
        ->and($sale['girl_name'])->toBe(companionGirlName());
});

// ─── 4. SOLO_CLIENTE no devuelve girl_name ───────────────────────────────────

it('SOLO_CLIENTE order item does not include girl_name', function () {
    $waiter = companionWaiterToken();
    $result = nightposCreateOrderWithItem($waiter);

    $response = test()->getJson("/api/v1/orders/{$result['order_id']}", nightposOperationalHeaders($waiter))
        ->assertOk();

    $item = companionOrderItem($response->json());

    expect($item['sale_mode'])->toBe('SOLO_CLIENTE')
        ->and(array_key_exists('girl_name', $item))->toBeFalse();
});

// ─── 5. combo sigue devolviendo allocations con girl_name ────────────────────

it('combo order item still exposes allocations with girl_name', function () {
    $waiter = companionWaiterToken();
    $girlId = companionGirlId();
    $girlId2 = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');
    $productId = companionComboSeedProduct(6);

    $created = companionComboCreateOrderWithItem($waiter, $productId, 1);
    $orderId = $created['order_id'];
    $itemId = $created['item_id'];

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [
            ['girl_user_id' => $girlId, 'units' => 3],
            ['girl_user_id' => $girlId2, 'units' => 3],
        ],
    ], nightposOperationalHeaders($waiter))->assertOk();

    $response = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($waiter))
        ->assertOk();

    $item = companionOrderItem($response->json());

    expect($item['requires_allocation'])->toBeTrue()
        ->and($item['allocations'])->toHaveCount(2)
        ->and(collect($item['allocations'])->pluck('girl_name')->filter()->count())->toBe(2)
        ->and(array_key_exists('girl_name', $item))->toBeFalse();
});

function companionComboSeedProduct(int $braceletUnits = 6): int
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Combo 6 Cervezas Companion Test',
        'product_type' => 'beverage',
        'unit' => 'combo',
        'status' => 'active',
        'settlement_behavior' => 'GIRL_BRACELET_ALLOCATION',
        'bracelet_units_per_line' => $braceletUnits,
        'requires_allocation' => true,
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel::query()->create([
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

function companionComboCreateOrderWithItem(string $waiterToken, int $productId, int $quantity = 1): array
{
    $waiterId = nightposDemoWaiterUserId();

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Combo Mesa Companion',
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

    return [
        'order_id' => $orderId,
        'item_id' => (int) $itemResponse->json('data.order.items.0.id'),
    ];
}
