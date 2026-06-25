<?php

declare(strict_types=1);

use App\Application\Order\Support\CashierChargeableOrdersScope;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    rfccClearSeededChargeableOrders();
});

function rfccCashierToken(): string
{
    return nightposLoginPin('1234');
}

function rfccAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function rfccHeaders(string $token): array
{
    return nightposOperationalHeaders($token);
}

function rfccPrepareCash(string $token): void
{
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 200, false);
}

function rfccClearSeededChargeableOrders(): void
{
    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    if ($shiftId === 0) {
        return;
    }

    OrderModel::query()
        ->where('official_shift_id', $shiftId)
        ->where('status', 'SENT_TO_BAR')
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);
}

function rfccGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function rfccSeededRoomId(string $code = 'P1'): int
{
    return (int) RoomModel::query()->where('code', $code)->value('id');
}

function rfccCreatePieza(int $roomId, string $token): int
{
    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => rfccGirlId(),
        'room_id' => $roomId,
        'total_amount' => 120,
        'duration_minutes' => 60,
    ]), rfccHeaders($token))->assertCreated();

    return (int) $response->json('data.room_service.id');
}

function rfccChargeableCount(string $token): int
{
    return count(test()->getJson('/api/v1/orders?scope=cashier_chargeable&cashier_scope=1', rfccHeaders($token))
        ->assertOk()
        ->json('data.orders') ?? []);
}

function rfccCloseCheckOrderCount(string $token): int
{
    return (int) test()->getJson('/api/v1/cash/session/current/close-check', rfccHeaders($token))
        ->assertOk()
        ->json('data.summary.active_orders');
}

// --- Problem 1: finish pieza releases room ---

it('creating pieza occupies linked room', function () {
    $token = rfccAdminToken();
    rfccPrepareCash($token);
    $roomId = rfccSeededRoomId();

    rfccCreatePieza($roomId, $token);

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED');
});

it('cashier finish pieza sets room AVAILABLE', function () {
    $adminToken = rfccAdminToken();
    $cashierToken = rfccCashierToken();
    rfccPrepareCash($adminToken);
    $roomId = rfccSeededRoomId();
    $serviceId = rfccCreatePieza($roomId, $adminToken);

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], rfccHeaders($cashierToken))
        ->assertOk()
        ->assertJsonPath('message', 'Pieza terminada. Habitación disponible para nueva pieza.');

    expect(RoomModel::query()->find($roomId)?->status)->toBe('AVAILABLE')
        ->and(RoomServiceModel::query()->find($serviceId)?->status)->toBe('FINISHED');
});

it('cashier finish pieza allows another pieza in same room', function () {
    $token = rfccAdminToken();
    rfccPrepareCash($token);
    $roomId = rfccSeededRoomId();
    $firstId = rfccCreatePieza($roomId, $token);

    test()->postJson("/api/v1/room-services/{$firstId}/finish", [], rfccHeaders($token))->assertOk();

    rfccCreatePieza($roomId, $token);

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED');
});

it('finished pieza no longer blocks cash close-check', function () {
    $token = rfccAdminToken();
    rfccPrepareCash($token);
    $roomId = rfccSeededRoomId();
    $serviceId = rfccCreatePieza($roomId, $token);

    test()->getJson('/api/v1/cash/session/current/close-check', rfccHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.summary.active_room_services', 1);

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], rfccHeaders($token))->assertOk();

    test()->getJson('/api/v1/cash/session/current/close-check', rfccHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.summary.active_room_services', 0);
});

it('emits room.updated when cashier finishes pieza', function () {
    $adminToken = rfccAdminToken();
    rfccPrepareCash($adminToken);
    $roomId = rfccSeededRoomId();
    $serviceId = rfccCreatePieza($roomId, $adminToken);

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], rfccHeaders(rfccCashierToken()))->assertOk();

    expect(OperationalEventModel::query()
        ->where('type', 'room.updated')
        ->where('payload->entity->id', $roomId)
        ->exists())->toBeTrue();
});

// --- Problem 2: close-check aligned with charge queue ---

it('OPEN draft does not block cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    nightposCreateOrderWithItem($token, ['table_label' => 'Draft']);

    expect(rfccCloseCheckOrderCount($token))->toBe(0)
        ->and(rfccChargeableCount($token))->toBe(0);
});

it('SENT_TO_BAR blocks cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Cobrar']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], rfccHeaders($token))->assertOk();

    test()->getJson('/api/v1/cash/session/current/close-check', rfccHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false)
        ->assertJsonPath('data.summary.active_orders', 1);
});

it('BILLED order does not block cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Cobrada']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], rfccHeaders($token))->assertOk();
    test()->postJson("/api/v1/orders/{$result['order_id']}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], rfccHeaders($token))->assertCreated();

    expect(rfccCloseCheckOrderCount($token))->toBe(0);
});

it('CANCELLED order does not block cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Cancelada']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], rfccHeaders($token))->assertOk();
    test()->postJson("/api/v1/orders/{$result['order_id']}/cancel", [], rfccHeaders($token))->assertOk();

    expect(rfccCloseCheckOrderCount($token))->toBe(0);
});

it('order from another shift does not block cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $closedShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno cerrado RFCC',
        'shift_type' => 'NIGHT',
        'business_date' => '2020-01-01',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subDays(2)->addHours(8),
        'status' => 'CLOSED',
        'opened_by_user_id' => 1,
        'opened_at' => now()->subDays(2),
        'closed_at' => now()->subDays(2),
    ]);

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $closedShift->id,
        'order_number' => 'OTHER-SHIFT',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Otro turno',
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
        'opened_by_user_id' => 1,
    ]);

    expect(rfccCloseCheckOrderCount($token))->toBe(0);
});

it('order from another branch does not block cash close-check', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $otherBranchId = (int) BranchModel::query()->where('code', '!=', 'CENTRO')->value('id');

    if ($otherBranchId === 0) {
        $this->markTestSkipped('No alternate branch in seeder.');
    }

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranchId,
        'official_shift_id' => $shiftId,
        'order_number' => 'OTHER-BRANCH',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Otra sucursal',
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
        'opened_by_user_id' => 1,
    ]);

    expect(rfccCloseCheckOrderCount($token))->toBe(0);
});

it('close-check order count matches cashier chargeable queue', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);

    expect(rfccCloseCheckOrderCount($token))->toBe(rfccChargeableCount($token));

    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Cola']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], rfccHeaders($token))->assertOk();

    expect(rfccCloseCheckOrderCount($token))->toBe(1)
        ->and(rfccChargeableCount($token))->toBe(1);
});

it('scope helper only counts SENT_TO_BAR on current shift', function () {
    $token = rfccCashierToken();
    rfccPrepareCash($token);
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    nightposCreateOrderWithItem($token, ['table_label' => 'Open draft']);

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'order_number' => 'BAR-1',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Barra',
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
        'opened_by_user_id' => 1,
    ]);

    $scope = app(CashierChargeableOrdersScope::class);

    expect($scope->countForCashierScope($tenantId, $branchId))->toBe(1);
});
