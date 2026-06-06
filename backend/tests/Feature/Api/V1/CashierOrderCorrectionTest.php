<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
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

function corrCashierToken(): string
{
    return nightposLoginPin('1234');
}

function corrWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function corrWaiter2Token(): string
{
    return nightposLoginPin('5688');
}

function corrGirlUserId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function corrSeedProduct(bool $withCompanion = false): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'Producto Corrección',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 30,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    if ($withCompanion) {
        ProductPriceModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => 50,
            'girl_amount' => 20,
            'house_amount' => 30,
            'currency' => 'BOB',
            'status' => 'active',
        ]);
    }

    return (int) $product->id;
}

function corrCreateOpenOrder(string $waiterToken, int $quantity = 2): array
{
    nightposEnsureShiftOpen();

    $productId = corrSeedProduct(true);

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Error',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $orderId = (int) $orderResponse->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => $quantity,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    return compact('orderId', 'productId', 'itemId');
}

it('allows cashier to update quantity on OPEN order', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter);
    $cashier = corrCashierToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 4,
    ], nightposOperationalHeaders($cashier))->assertOk();

    $item = OrderItemModel::query()->find($itemId);
    $order = OrderModel::query()->find($orderId);

    expect((int) $item->quantity)->toBe(4)
        ->and((float) $item->line_total)->toBe(120.0)
        ->and((float) $order->total)->toBe(120.0);
});

it('allows cashier to change sale mode on OPEN order', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter);
    $cashier = corrCashierToken();
    $girlId = corrGirlUserId();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'sale_mode' => 'CON_ACOMPANANTE',
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($cashier))->assertOk();

    $item = OrderItemModel::query()->find($itemId);

    expect($item->sale_mode)->toBe('CON_ACOMPANANTE')
        ->and((int) $item->girl_user_id)->toBe($girlId)
        ->and((float) $item->unit_price)->toBe(50.0);
});

it('allows cashier to change girl on CON_ACOMPANANTE item', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter);
    $cashier = corrCashierToken();
    $girlId = corrGirlUserId();
    $girl2Id = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'sale_mode' => 'CON_ACOMPANANTE',
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($cashier))->assertOk();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'girl_user_id' => $girl2Id,
    ], nightposOperationalHeaders($cashier))->assertOk();

    expect((int) OrderItemModel::query()->find($itemId)->girl_user_id)->toBe($girl2Id);
});

it('allows cashier to remove PENDING line on OPEN order', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'productId' => $productId] = corrCreateOpenOrder($waiter);
    $cashier = corrCashierToken();

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    $pendingId = (int) OrderItemModel::query()
        ->where('order_id', $orderId)
        ->where('item_status', 'PENDING')
        ->orderByDesc('id')
        ->value('id');

    test()->deleteJson("/api/v1/orders/{$orderId}/items/{$pendingId}", [], nightposOperationalHeaders($cashier))
        ->assertOk();

    expect(OrderItemModel::query()->find($pendingId))->toBeNull()
        ->and((float) OrderModel::query()->find($orderId)->total)->toBe(60.0);
});

it('allows cashier to cancel sent line with reason', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter, 1);
    $cashier = corrCashierToken();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Producto equivocado',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $item = OrderItemModel::query()->find($itemId);

    expect($item->item_status)->toBe('CANCELLED')
        ->and($item->cancellation_reason)->toBe('Producto equivocado')
        ->and((float) OrderModel::query()->find($orderId)->total)->toBe(0.0);
});

it('rejects cancel sent line without reason', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter, 1);
    $cashier = corrCashierToken();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [], nightposOperationalHeaders($cashier))
        ->assertStatus(422);
});

it('rejects modifications on BILLED order', function () {
    nightposEnsureShiftOpen();
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter, 1);
    $cashier = corrCashierToken();
    nightposOpenCashSession($cashier);

    $total = (float) OrderModel::query()->find($orderId)->total;

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $total]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 3,
    ], nightposOperationalHeaders($cashier))->assertStatus(422);
});

it('denies waiter editing another waiters order', function () {
    $waiter1 = corrWaiterToken();
    ['orderId' => $orderId, 'productId' => $productId] = corrCreateOpenOrder($waiter1);
    $waiter2 = corrWaiter2Token();

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiter2))->assertNotFound();
});

it('allows cashier to update table label and notes', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId] = corrCreateOpenOrder($waiter);
    $cashier = corrCashierToken();

    test()->patchJson("/api/v1/orders/{$orderId}", [
        'table_label' => 'Mesa Corregida 12',
        'notes' => 'Cliente cambió de mesa',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $order = OrderModel::query()->find($orderId);

    expect($order->table_label)->toBe('Mesa Corregida 12')
        ->and($order->notes)->toBe('Cliente cambió de mesa');
});

it('recalculates totals after corrections', function () {
    $waiter = corrWaiterToken();
    ['orderId' => $orderId, 'itemId' => $itemId] = corrCreateOpenOrder($waiter, 3);
    $cashier = corrCashierToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 1,
    ], nightposOperationalHeaders($cashier))->assertOk();

    expect((float) OrderModel::query()->find($orderId)->total)->toBe(30.0);
});

it('denies cross-tenant order modification', function () {
    $cashier = corrCashierToken();
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa-corr',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $foreignOrder = OrderModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $branchId,
        'order_number' => 'C-9001',
        'status' => 'OPEN',
        'table_label' => 'Mesa Ajena',
        'opened_by_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => 30,
        'total' => 30,
        'currency' => 'BOB',
    ]);

    $foreignItem = OrderItemModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $branchId,
        'order_id' => $foreignOrder->id,
        'product_id' => corrSeedProduct(),
        'product_name' => 'Ajeno',
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
        'unit_price' => 30,
        'line_total' => 30,
        'item_status' => 'PENDING',
    ]);

    test()->putJson("/api/v1/orders/{$foreignOrder->id}/items/{$foreignItem->id}", [
        'quantity' => 5,
    ], nightposOperationalHeaders($cashier))->assertNotFound();
});
