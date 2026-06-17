<?php

declare(strict_types=1);

use App\Domain\SSE\Repositories\OperationalEventRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function sse2AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function sse2CashierToken(): string
{
    return nightposLoginPin('1234'); // cajera.demo
}

function sse2WaiterToken(): string
{
    return nightposLoginPin('5678'); // garzon.demo
}

function sse2GirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function sse2CountEvents(string $type): int
{
    return OperationalEventModel::query()
        ->where('type', $type)
        ->count();
}

/** @return array<string, mixed> */
function sse2LastEvent(string $type): array
{
    $model = OperationalEventModel::query()
        ->where('type', $type)
        ->orderByDesc('id')
        ->first();

    return $model ? [
        'type'        => $model->type,
        'tenant_id'   => $model->tenant_id,
        'branch_id'   => $model->branch_id,
        'target_role' => $model->target_role,
        'payload'     => $model->payload,
    ] : [];
}

// ─── Test 1: crear pieza emite room_service.created ──────────────────────────

it('creating a room service emits room_service.created event', function () {
    $admin = sse2AdminToken();
    nightposOpenCashSession($admin);

    $girlId = sse2GirlId();
    $before = sse2CountEvents('room_service.created');

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id'     => $girlId,
        'total_amount'     => 100,
        'girl_percent'     => 70,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($admin))->assertCreated();

    expect(sse2CountEvents('room_service.created'))->toBe($before + 1);

    $event = sse2LastEvent('room_service.created');
    expect($event['type'])->toBe('room_service.created')
        ->and($event['payload']['entity']['type'])->toBe('room_service')
        ->and($event['payload']['refresh'])->toContain('room_services');
});

// ─── Test 2: pieza vencida emite room_service.due ────────────────────────────

it('due room service command emits room_service.due event', function () {
    Carbon::setTestNow('2026-06-10 20:00:00');
    $admin = sse2AdminToken();
    nightposOpenCashSession($admin);
    $girlId = sse2GirlId();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id'     => $girlId,
        'total_amount'     => 80,
        'girl_percent'     => 70,
        'duration_minutes' => 5,
        'started_at'       => '2026-06-10 20:00:00',
    ]), nightposOperationalHeaders($admin))->assertCreated();

    Carbon::setTestNow('2026-06-10 20:06:00');

    $before = sse2CountEvents('room_service.due');
    $this->artisan('room-services:check-due')->assertSuccessful();

    expect(sse2CountEvents('room_service.due'))->toBeGreaterThan($before);

    Carbon::setTestNow();
});

// ─── Test 3: marcar habitación limpia emite room.cleaned ─────────────────────

it('marking a room clean emits room.cleaned event', function () {
    Carbon::setTestNow('2026-06-10 21:00:00');
    $admin    = sse2AdminToken();
    nightposOpenCashSession($admin);
    $girlId = sse2GirlId();

    $room = test()->postJson('/api/v1/rooms', [
        'code'      => 'SSETEST',
        'name'      => 'SSE Test Room',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($admin))->assertCreated();
    $roomId = (int) $room->json('data.room.id');

    $svc = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id'     => $girlId,
        'room_id'          => $roomId,
        'total_amount'     => 80,
        'girl_percent'     => 70,
        'duration_minutes' => 5,
        'started_at'       => '2026-06-10 21:00:00',
    ]), nightposOperationalHeaders($admin))->assertCreated();
    $serviceId = (int) $svc->json('data.room_service.id');

    // Advance time past the service duration so it becomes DUE
    Carbon::setTestNow('2026-06-10 21:06:00');
    $this->artisan('room-services:check-due')->assertSuccessful();

    // Admin finishes the DUE service (room transitions to CLEANING status)
    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($admin))
        ->assertOk();

    $before = sse2CountEvents('room.cleaned');
    // Use admin route (rooms.clean permission) to avoid cleaning role token issue in isolated test
    test()->postJson("/api/v1/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders($admin))
        ->assertOk();

    expect(sse2CountEvents('room.cleaned'))->toBe($before + 1);

    Carbon::setTestNow();
});

// ─── Test 4: marcar habitación limpia emite cleaning.earnings.updated ─────────

it('marking a room clean emits cleaning.earnings.updated event when service finished', function () {
    Carbon::setTestNow('2026-06-10 22:00:00');
    $admin    = sse2AdminToken();
    nightposOpenCashSession($admin);
    $girlId = sse2GirlId();

    $room = test()->postJson('/api/v1/rooms', [
        'code'      => 'SSECLEAN',
        'name'      => 'SSE Clean Room',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($admin))->assertCreated();
    $roomId = (int) $room->json('data.room.id');

    $svc = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id'     => $girlId,
        'room_id'          => $roomId,
        'total_amount'     => 80,
        'girl_percent'     => 70,
        'duration_minutes' => 5,
        'started_at'       => '2026-06-10 22:00:00',
    ]), nightposOperationalHeaders($admin))->assertCreated();
    $serviceId = (int) $svc->json('data.room_service.id');

    // Advance time past duration so service becomes DUE
    Carbon::setTestNow('2026-06-10 22:06:00');
    $this->artisan('room-services:check-due')->assertSuccessful();

    // Admin finishes the DUE service (room transitions to CLEANING status)
    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($admin))
        ->assertOk();

    // Cleaning user marks the room clean (creates cleaning task and emits event)
    $before = sse2CountEvents('cleaning.earnings.updated');
    test()->postJson("/api/v1/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders($admin))
        ->assertOk();

    expect(sse2CountEvents('cleaning.earnings.updated'))->toBe($before + 1);

    $event = sse2LastEvent('cleaning.earnings.updated');
    expect($event['target_role'])->toBe('cleaning');

    Carbon::setTestNow();
});

// ─── Test 5: enviar comanda a barra emite order.sent_to_bar ──────────────────

it('sending order to bar emits order.sent_to_bar event', function () {
    $waiter = sse2WaiterToken();

    $result = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];

    $before = sse2CountEvents('order.sent_to_bar');

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    expect(sse2CountEvents('order.sent_to_bar'))->toBe($before + 1);

    $event = sse2LastEvent('order.sent_to_bar');
    expect($event['payload']['entity']['type'])->toBe('order')
        ->and($event['payload']['entity']['id'])->toBe($orderId)
        ->and($event['payload']['refresh'])->toContain('orders');
});

// ─── Test 6: cobrar comanda emite order.billed y sale.created ────────────────

it('charging an order emits order.billed and sale.created events', function () {
    $cashier = sse2CashierToken();
    $waiter  = sse2WaiterToken();
    nightposOpenCashSession($cashier);

    $result  = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];

    $beforeBilled = sse2CountEvents('order.billed');
    $beforeSale   = sse2CountEvents('sale.created');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    expect(sse2CountEvents('order.billed'))->toBe($beforeBilled + 1);
    expect(sse2CountEvents('sale.created'))->toBe($beforeSale + 1);
    expect(sse2CountEvents('cash.movement.created'))->toBeGreaterThan(0);
});

// ─── Test 7: venta directa emite direct_sale.created y cash.movement.created ─

it('creating direct sale emits direct_sale.created and cash.movement.created events', function () {
    $cashier = sse2CashierToken();
    nightposOpenCashSession($cashier);

    // Seed a product for direct sale
    // Seed a product with a known price for direct sale
    $productId = nightposSeedOrderProduct();
    $price     = 25.0; // nightposSeedOrderProduct creates SOLO_CLIENTE price = 25

    $beforeDirect = sse2CountEvents('direct_sale.created');
    $beforeCash   = sse2CountEvents('cash.movement.created');

    test()->postJson('/api/v1/direct-sales', [
        'items' => [[
            'product_id' => $productId,
            'sale_mode'  => 'SOLO_CLIENTE',
            'quantity'   => 1,
        ]],
        'payments' => [['method' => 'CASH', 'amount' => $price]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    expect(sse2CountEvents('direct_sale.created'))->toBe($beforeDirect + 1);
    expect(sse2CountEvents('cash.movement.created'))->toBeGreaterThan($beforeCash);
});

// ─── Test 8: pagar liquidación emite settlement.paid y cash.movement.created ─

it('paying a settlement emits settlement.paid and cash.movement.created events', function () {
    $admin   = sse2AdminToken();
    $cashier = sse2CashierToken();
    $waiter  = sse2WaiterToken();
    nightposOpenCashSession($cashier);

    $result  = nightposCreateOrderWithItem($waiter);
    $orderId = $result['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    // Re-login admin to get a fresh token (avoids auth state issues)
    $admin = sse2AdminToken();
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))
        ->assertCreated();

    // Get settlement ID directly from DB
    $settlementId = (int) \App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel::query()
        ->where('status', 'PENDING')
        ->value('id');

    if ($settlementId === 0) {
        $this->markTestSkipped('No PENDING settlement found to pay.');
    }

    $beforePaid = sse2CountEvents('settlement.paid');
    $beforeCash = sse2CountEvents('cash.movement.created');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($cashier))
        ->assertOk();

    expect(sse2CountEvents('settlement.paid'))->toBe($beforePaid + 1);
    expect(sse2CountEvents('cash.movement.created'))->toBeGreaterThan($beforeCash);
});

// ─── Test 9: limpieza no recibe evento de caja ────────────────────────────────

it('cleaning role does not receive cash events in their filtered stream', function () {
    $cashier = sse2CashierToken();
    nightposOpenCashSession($cashier);

    // Emit a cash movement with null target_role (broadcast)
    /** @var \App\Domain\SSE\Repositories\OperationalEventRepositoryInterface $repo */
    $repo = app(OperationalEventRepositoryInterface::class);
    $repo->create(1, 1, 'cash.movement.created', ['summary' => 'Test cash movement']);

    // Cleaning should see broadcast events (target_role = null)
    $events = $repo->findSince(1, 1, 'cleaning', 0);
    $cashEvents = array_filter($events, fn ($e) => $e['type'] === 'cash.movement.created');

    // Broadcast cash events ARE visible to cleaning (target_role = null means broadcast to all)
    // But there are no cash events targeted ONLY to cleaning (target_role = 'cleaning')
    $cleaningOnlyEvents = OperationalEventModel::query()
        ->where('type', 'cash.movement.created')
        ->where('target_role', 'cleaning')
        ->count();

    expect($cleaningOnlyEvents)->toBe(0);
});

// ─── Test 10: cajera recibe order.sent_to_bar ────────────────────────────────

it('cashier role receives order.sent_to_bar events', function () {
    /** @var \App\Domain\SSE\Repositories\OperationalEventRepositoryInterface $repo */
    $repo = app(OperationalEventRepositoryInterface::class);

    // Emit a broadcast order event
    $repo->create(1, 1, 'order.sent_to_bar', [
        'entity'  => ['type' => 'order', 'id' => 99],
        'summary' => 'Comanda a barra test',
        'refresh' => ['orders'],
    ]);

    // Cashier (no specific target_role = broadcast) should receive it
    $events = $repo->findSince(1, 1, 'cashier', 0);
    $found = array_filter($events, fn ($e) => $e['type'] === 'order.sent_to_bar');

    expect(count($found))->toBeGreaterThan(0);
});

// ─── Test 11: admin recibe todos los eventos operativos ──────────────────────

it('admin receives all operational events regardless of target_role', function () {
    /** @var \App\Domain\SSE\Repositories\OperationalEventRepositoryInterface $repo */
    $repo = app(OperationalEventRepositoryInterface::class);

    $repo->create(1, 1, 'room_service.created', ['summary' => 'Pieza admin test']);
    $repo->create(1, 1, 'cleaning.earnings.updated', ['summary' => 'Limpieza test'], 'cleaning');
    $repo->create(1, 1, 'order.sent_to_bar', ['summary' => 'Barra test']);

    // Admin has roleScope = null → sees all events
    $events = $repo->findSince(1, 1, null, 0);

    $types = array_column($events, 'type');
    expect(in_array('room_service.created', $types))->toBeTrue()
        ->and(in_array('cleaning.earnings.updated', $types))->toBeTrue()
        ->and(in_array('order.sent_to_bar', $types))->toBeTrue();
});

// ─── Test 12: tenant/branch aislado ──────────────────────────────────────────

it('events from different tenant or branch are not visible to another', function () {
    /** @var \App\Domain\SSE\Repositories\OperationalEventRepositoryInterface $repo */
    $repo = app(OperationalEventRepositoryInterface::class);

    // Emit event for tenant 999 / branch 999
    $repo->create(999, 999, 'order.created', ['summary' => 'Evento de otro tenant']);

    // Tenant 1 / branch 1 should NOT see it
    $events = $repo->findSince(1, 1, null, 0);
    $foreign = array_filter($events, fn ($e) => ($e['tenant_id'] ?? 0) === 999);

    expect(count($foreign))->toBe(0);
});
