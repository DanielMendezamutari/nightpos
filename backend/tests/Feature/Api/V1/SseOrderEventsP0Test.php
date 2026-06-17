<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function sseP0WaiterToken(): string
{
    return nightposLoginPin('5678');
}

function sseP0CashierToken(): string
{
    return nightposLoginPin('1234');
}

function sseP0GirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function sseP0CountEvents(string $type): int
{
    return OperationalEventModel::query()
        ->where('type', $type)
        ->count();
}

/** @return array<string, mixed> */
function sseP0LastEvent(string $type): array
{
    $model = OperationalEventModel::query()
        ->where('type', $type)
        ->orderByDesc('id')
        ->first();

    return $model ? [
        'type' => $model->type,
        'payload' => $model->payload,
    ] : [];
}

function sseP0AssertOrderPayload(array $payload, int $orderId, string $status, string $source): void
{
    expect($payload['order_id'])->toBe($orderId)
        ->and($payload['entity']['type'])->toBe('order')
        ->and($payload['entity']['id'])->toBe($orderId)
        ->and($payload['refresh'])->toContain('orders')
        ->and($payload['status'])->toBe($status)
        ->and($payload['source'])->toBe($source);
}

function sseP0CreateOrderOnly(string $token): int
{
    $response = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa SSE P0',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))->assertCreated();

    return (int) $response->json('data.order.id');
}

function sseP0OrderItemId(int $orderId, string $token): int
{
    return (int) test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($token))
        ->json('data.order.items.0.id');
}

// ─── 1. crear comanda emite order.created ────────────────────────────────────

it('creating an order emits order.created with standard payload', function () {
    $waiter = sseP0WaiterToken();
    $before = sseP0CountEvents('order.created');

    $orderId = sseP0CreateOrderOnly($waiter);

    expect(sseP0CountEvents('order.created'))->toBe($before + 1);

    $event = sseP0LastEvent('order.created');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'create_order');
});

// ─── 2. agregar producto emite order.updated ─────────────────────────────────

it('adding an order item emits order.updated with standard payload', function () {
    $waiter = sseP0WaiterToken();
    $orderId = sseP0CreateOrderOnly($waiter);
    $productId = nightposSeedOrderProduct();

    $before = sseP0CountEvents('order.updated');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 2,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'add_order_item');
});

// ─── 3. editar cantidad emite order.updated ──────────────────────────────────

it('updating item quantity emits order.updated', function () {
    $waiter = sseP0WaiterToken();
    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];
    $itemId = sseP0OrderItemId($orderId, $waiter);

    $before = sseP0CountEvents('order.updated');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 3,
    ], nightposOperationalHeaders($waiter))->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'update_order_item');
});

// ─── 4. cambiar producto emite order.updated ─────────────────────────────────

it('changing order item product emits order.updated', function () {
    $waiter = sseP0WaiterToken();
    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];
    $itemId = sseP0OrderItemId($orderId, $waiter);
    $newProductId = nightposSeedOrderProduct();

    $before = sseP0CountEvents('order.updated');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $newProductId,
    ], nightposOperationalHeaders($waiter))->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'update_order_item');
});

// ─── 5. asignar chica emite order.updated ─────────────────────────────────────

it('assigning girl to order item emits order.updated', function () {
    $waiter = sseP0WaiterToken();
    $girlId = sseP0GirlId();
    $productId = nightposSeedOrderProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 80, 'girl_amount' => 40, 'house_amount' => 40],
    ]);

    $orderId = sseP0CreateOrderOnly($waiter);

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    $itemId = sseP0OrderItemId($orderId, $waiter);
    $before = sseP0CountEvents('order.updated');

    test()->patchJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($waiter))->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'assign_order_item_girl');
});

// ─── 6. editar cabecera emite order.updated ──────────────────────────────────

it('updating order header emits order.updated', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $orderId = sseP0CreateOrderOnly($admin);

    $before = sseP0CountEvents('order.updated');

    test()->patchJson("/api/v1/orders/{$orderId}", [
        'table_label' => 'Mesa SSE Actualizada',
        'notes' => 'Nota operativa',
    ], nightposOperationalHeaders($admin))->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'update_order_header');
});

// ─── 7. quitar línea emite order.updated ─────────────────────────────────────

it('removing order item emits order.updated', function () {
    $waiter = sseP0WaiterToken();
    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];
    $itemId = sseP0OrderItemId($orderId, $waiter);

    $before = sseP0CountEvents('order.updated');

    test()->deleteJson("/api/v1/orders/{$orderId}/items/{$itemId}", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'OPEN', 'remove_order_item');
});

// ─── 8. cancelar línea emite order.updated ───────────────────────────────────

it('cancelling order item emits order.updated', function () {
    $waiter = sseP0WaiterToken();
    $cashier = sseP0CashierToken();
    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];
    $itemId = sseP0OrderItemId($orderId, $waiter);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    $before = sseP0CountEvents('order.updated');

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Error de garzón',
    ], nightposOperationalHeaders($cashier))->assertOk();

    expect(sseP0CountEvents('order.updated'))->toBe($before + 1);

    $event = sseP0LastEvent('order.updated');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'SENT_TO_BAR', 'cancel_order_item');
});

// ─── 9. enviar a barra emite order.sent_to_bar ───────────────────────────────

it('sending order to bar emits order.sent_to_bar with standard payload', function () {
    $waiter = sseP0WaiterToken();
    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];

    $before = sseP0CountEvents('order.sent_to_bar');

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    expect(sseP0CountEvents('order.sent_to_bar'))->toBe($before + 1);

    $event = sseP0LastEvent('order.sent_to_bar');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'SENT_TO_BAR', 'send_order_to_bar');
});

// ─── 10. cobrar emite order.billed ───────────────────────────────────────────

it('charging an order emits order.billed with standard payload', function () {
    $cashier = sseP0CashierToken();
    $waiter = sseP0WaiterToken();
    nightposOpenCashSession($cashier);

    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];

    $before = sseP0CountEvents('order.billed');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    expect(sseP0CountEvents('order.billed'))->toBe($before + 1);

    $event = sseP0LastEvent('order.billed');
    sseP0AssertOrderPayload($event['payload'], $orderId, 'BILLED', 'charge_order');
});
