<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phase0CashierToken(): string
{
    return nightposLoginPin('1234');
}

function phase0WaiterToken(): string
{
    return nightposLoginPin('5678');
}

function phase0ChargeableOrders(string $cashierToken): \Illuminate\Testing\TestResponse
{
    return test()->getJson('/api/v1/orders?scope=cashier_chargeable', nightposOperationalHeaders($cashierToken));
}

function phase0FindOrderRow(\Illuminate\Testing\TestResponse $response, int $orderId): ?array
{
    return collect($response->json('data.orders'))->firstWhere('id', $orderId);
}

function phase0CompanionProductId(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Ron Phase0 Companion',
        'product_type' => 'beverage',
        'unit' => 'botella',
        'status' => 'active',
        'settlement_behavior' => 'GIRL_CONSUMPTION',
        'requires_allocation' => false,
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 80,
        'girl_amount' => 40,
        'house_amount' => 40,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

function phase0ComboProductId(int $braceletUnits = 6): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Combo Phase0 Test',
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

function phase0SendToBar(string $waiterToken, int $orderId): void
{
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();
}

function phase0MarkSentToBar(int $orderId): void
{
    OrderModel::query()->where('id', $orderId)->update([
        'status' => 'SENT_TO_BAR',
        'sent_to_bar_at' => now(),
    ]);
}

function phase0CreateOrderWithItem(string $waiterToken, int $productId, string $saleMode = 'SOLO_CLIENTE', bool $sendToBar = false): int
{
    nightposEnsureShiftOpen();
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Phase0',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => $saleMode,
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    if ($sendToBar)
        phase0SendToBar($waiterToken, $orderId);

    return $orderId;
}

it('cashier_chargeable excludes OPEN drafts', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $openId = phase0CreateOrderWithItem($waiterToken, $productId, 'SOLO_CLIENTE', false);

    $ids = collect(phase0ChargeableOrders($cashierToken)->json('data.orders'))->pluck('id')->all();

    expect($ids)->not->toContain($openId);
});

it('cashier_chargeable returns waiting_minutes', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $orderId = phase0CreateOrderWithItem($waiterToken, $productId);
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row)->not->toBeNull()
        ->and($row)->toHaveKey('waiting_minutes')
        ->and($row['waiting_minutes'])->toBeInt()
        ->and($row['waiting_minutes'])->toBeGreaterThanOrEqual(0);
});

it('cashier_chargeable returns has_companion_items for CON_ACOMPANANTE lines', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = phase0CompanionProductId();
    $orderId = phase0CreateOrderWithItem($waiterToken, $productId, 'CON_ACOMPANANTE');
    phase0MarkSentToBar($orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['has_companion_items'])->toBeTrue();
});

it('cashier_chargeable returns has_combo_items for combo products', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = phase0ComboProductId();
    $orderId = phase0CreateOrderWithItem($waiterToken, $productId, 'CON_ACOMPANANTE');
    phase0MarkSentToBar($orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['has_combo_items'])->toBeTrue();
});

it('cashier_chargeable returns allocation_incomplete for incomplete combo', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = phase0ComboProductId();
    $orderId = phase0CreateOrderWithItem($waiterToken, $productId, 'CON_ACOMPANANTE');
    phase0MarkSentToBar($orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['allocation_incomplete'])->toBeTrue()
        ->and($row['charge_blocked'])->toBeTrue()
        ->and($row['charge_blockers'])->toContain('ALLOCATION_INCOMPLETE');
});

it('cashier_chargeable returns girl_missing_count when companion line has no girl', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = phase0CompanionProductId();
    $orderId = phase0CreateOrderWithItem($waiterToken, $productId, 'CON_ACOMPANANTE');
    phase0MarkSentToBar($orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['girl_missing_count'])->toBe(1)
        ->and($row['charge_blocked'])->toBeTrue()
        ->and($row['charge_blockers'])->toContain('GIRL_MISSING');
});

it('cashier_chargeable returns charge_blocked false when order is ready', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $orderId = phase0CreateOrderWithItem($waiterToken, $productId);
    phase0SendToBar($waiterToken, $orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['charge_blocked'])->toBeFalse()
        ->and($row['charge_blockers'])->toBe([]);
});

it('cashier_chargeable clears allocation blockers after combo is completed', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $productId = phase0ComboProductId();
    $orderId = phase0CreateOrderWithItem($waiterToken, $productId, 'CON_ACOMPANANTE');

    $itemId = (int) OrderModel::query()->find($orderId)?->items()->value('id');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [['girl_user_id' => $girlId, 'units' => 6]],
    ], nightposOperationalHeaders($waiterToken))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row['allocation_incomplete'])->toBeFalse()
        ->and($row['charge_blocked'])->toBeFalse();
});

it('cashier_chargeable keeps legacy list fields for existing clients', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $orderId = phase0CreateOrderWithItem($waiterToken, $productId);
    phase0MarkSentToBar($orderId);

    $row = phase0FindOrderRow(phase0ChargeableOrders($cashierToken), $orderId);

    expect($row)->toHaveKeys([
        'id',
        'order_number',
        'table_label',
        'status',
        'total',
        'waiter_name',
        'opened_at',
        'items_count',
    ]);
});

it('cashier_chargeable respects tenant isolation', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $orderId = phase0CreateOrderWithItem($waiterToken, $productId);
    phase0MarkSentToBar($orderId);

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa Phase0',
        'slug' => 'otra-casa-phase0',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $foreignOrder = OrderModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => 1,
        'order_number' => 'C-FOREIGN-P0',
        'status' => 'SENT_TO_BAR',
        'opened_by_user_id' => 1,
        'subtotal' => 100,
        'total' => 100,
        'currency' => 'BOB',
    ]);

    $ids = collect(phase0ChargeableOrders($cashierToken)->json('data.orders'))->pluck('id')->all();

    expect($ids)->toContain($orderId)
        ->and($ids)->not->toContain($foreignOrder->id);
});

it('cashier_chargeable respects branch isolation', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $orderId = phase0CreateOrderWithItem($waiterToken, $productId);
    phase0MarkSentToBar($orderId);
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $rows = phase0ChargeableOrders($cashierToken)->json('data.orders');

    expect(collect($rows)->pluck('id'))->toContain($orderId);

    foreach ($rows as $row) {
        expect((int) $row['branch_id'])->toBe($branchId);
    }
});

it('cashier_chargeable sorts by waiting minutes descending', function () {
    nightposEnsureShiftOpen();
    $waiterToken = phase0WaiterToken();
    $cashierToken = phase0CashierToken();
    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
        ->json('data.products.0.id');

    $olderId = phase0CreateOrderWithItem($waiterToken, $productId);
    $newerId = phase0CreateOrderWithItem($waiterToken, $productId);

    OrderModel::query()->where('id', $olderId)->update([
        'created_at' => now()->subMinutes(30),
        'sent_to_bar_at' => now()->subMinutes(25),
        'status' => 'SENT_TO_BAR',
    ]);

    OrderModel::query()->where('id', $newerId)->update([
        'created_at' => now()->subMinutes(5),
        'sent_to_bar_at' => now()->subMinutes(3),
        'status' => 'SENT_TO_BAR',
    ]);

    $rows = phase0ChargeableOrders($cashierToken)->json('data.orders');
    $ids = collect($rows)->pluck('id')->all();
    $olderIndex = array_search($olderId, $ids, true);
    $newerIndex = array_search($newerId, $ids, true);

    expect($olderIndex)->toBeInt()
        ->and($newerIndex)->toBeInt()
        ->and($olderIndex)->toBeLessThan($newerIndex);
});
