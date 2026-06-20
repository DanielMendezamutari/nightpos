<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function settlementPayCashierToken(): string
{
    return nightposLoginPin('1234');
}

function settlementPayAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function settlementPayWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function settlementPayCleaningToken(): string
{
    return nightposLoginPin('3333');
}

function settlementPayOpenCashierSession(string $token, float $opening = 100): int
{
    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => $opening,
    ], nightposOperationalHeaders($token))->assertCreated();

    return (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->json('data.session.id');
}

function settlementPayGenerateSettlements(): void
{
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(settlementPayAdminToken()))
        ->assertCreated();
}

function settlementPayChargeWaiterOrder(string $cashierToken): void
{
    $waiterId = nightposDemoWaiterUserId();
    $productId = nightposSeedOrderProduct();

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Liq pago',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($cashierToken));
    $orderResponse->assertCreated();
    $orderId = (int) $orderResponse->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 2,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($cashierToken))->assertOk();
    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

function settlementPayPendingId(string $type): int
{
    $id = StaffSettlementModel::query()
        ->where('settlement_type', $type)
        ->where('status', 'PENDING')
        ->value('id');

    expect($id)->not->toBeNull();

    return (int) $id;
}

function settlementPayMarkPaid(int $settlementId, ?string $token = null, string $paymentMethod = 'CASH'): TestResponse
{
    return test()->postJson(
        "/api/v1/settlements/{$settlementId}/mark-paid",
        ['payment_method' => $paymentMethod],
        nightposOperationalHeaders($token ?? settlementPayCashierToken()),
    );
}

function settlementPayPrepareWaiterSettlement(): int
{
    $cashier = settlementPayCashierToken();
    settlementPayOpenCashierSession($cashier);
    settlementPayChargeWaiterOrder($cashier);
    settlementPayGenerateSettlements();

    return settlementPayPendingId('WAITER');
}

it('mark-paid uses the same cash session as cash session current', function () {
    $cashier = settlementPayCashierToken();
    $sessionId = settlementPayOpenCashierSession($cashier);
    settlementPayChargeWaiterOrder($cashier);
    settlementPayGenerateSettlements();

    $settlementId = settlementPayPendingId('WAITER');

    $currentBefore = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->assertOk()
        ->json('data.session.id');

    expect($currentBefore)->toBe($sessionId);

    settlementPayMarkPaid($settlementId)
        ->assertOk()
        ->assertJsonPath('data.cash_session_id', $sessionId);

    $movement = CashMovementModel::query()
        ->where('cash_session_id', $sessionId)
        ->where('movement_type', 'EXPENSE')
        ->latest('id')
        ->first();

    expect($movement)->not->toBeNull()
        ->and((int) $movement->created_by_user_id)->toBe(
            (int) UserModel::query()->where('username', 'cajero.demo')->value('id')
        );
});

it('cashier with open cash can pay waiter settlement', function () {
    $settlementId = settlementPayPrepareWaiterSettlement();

    settlementPayMarkPaid($settlementId)
        ->assertOk()
        ->assertJsonPath('data.settlement.status', 'PAID');
});

it('cashier with open cash can pay girl settlement', function () {
    $cashier = settlementPayCashierToken();
    settlementPayOpenCashierSession($cashier);

    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 30,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashier))->assertCreated();

    settlementPayGenerateSettlements();

    settlementPayMarkPaid(settlementPayPendingId('GIRL'))
        ->assertOk()
        ->assertJsonPath('data.settlement.settlement_type', 'GIRL');
});

it('cashier with open cash can pay cleaning settlement', function () {
    settlementPayOpenCashierSession(settlementPayCashierToken());

    $roomId = (int) test()->postJson('/api/v1/rooms', [
        'code' => 'PAY1',
        'name' => 'Room PAY1',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders(settlementPayAdminToken()))->assertCreated()->json('data.room.id');

    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => (int) UserModel::query()->where('username', 'chica.centro')->value('id'),
        'room_id' => $roomId,
        'total_amount' => 80,
    ]), nightposOperationalHeaders(settlementPayCashierToken()))->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(settlementPayCleaningToken()))->assertOk();
    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(settlementPayCleaningToken()))->assertOk();

    settlementPayGenerateSettlements();

    settlementPayMarkPaid(settlementPayPendingId('CLEANING'))
        ->assertOk()
        ->assertJsonPath('data.settlement.settlement_type', 'CLEANING');
});

it('cashier without open cash receives 422 on mark-paid', function () {
    $settlementId = settlementPayPrepareWaiterSettlement();

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->assertOk()
        ->assertJsonPath('data.session', null);

    settlementPayMarkPaid($settlementId)
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja para pagar esta liquidación.');
});

it('cannot pay using another cashier open cash session', function () {
    $cashier = settlementPayCashierToken();
    settlementPayOpenCashierSession($cashier);
    settlementPayChargeWaiterOrder($cashier);
    settlementPayGenerateSettlements();

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayAdminToken()))
        ->assertOk()
        ->assertJsonPath('data.session', null);

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->assertOk()
        ->assertJsonPath('data.session.status', 'OPEN');

    settlementPayMarkPaid(settlementPayPendingId('WAITER'), settlementPayAdminToken())
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja para pagar esta liquidación.');
});

it('cannot pay settlement from another branch', function () {
    $settlementId = settlementPayPrepareWaiterSettlement();

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Sur',
        'code' => 'SUR',
        'status' => 'active',
    ]);

    test()->postJson(
        "/api/v1/settlements/{$settlementId}/mark-paid",
        ['payment_method' => 'CASH'],
        nightposOperationalHeaders(settlementPayCashierToken(), 'SUR'),
    )->assertForbidden();
});

it('expected cash decreases after settlement payment', function () {
    $settlementId = settlementPayPrepareWaiterSettlement();

    $before = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->json('data.session.financial_summary.expected_cash');

    settlementPayMarkPaid($settlementId)->assertOk();

    $after = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->json('data.session.financial_summary.expected_cash');

    expect((float) $after)->toBeLessThan((float) $before);
});

it('cash movement is linked to correct cash session', function () {
    $cashier = settlementPayCashierToken();
    $sessionId = settlementPayOpenCashierSession($cashier);
    settlementPayChargeWaiterOrder($cashier);
    settlementPayGenerateSettlements();
    $settlementId = settlementPayPendingId('WAITER');

    settlementPayMarkPaid($settlementId)->assertOk();

    expect(CashMovementModel::query()
        ->where('cash_session_id', $sessionId)
        ->where('movement_type', 'EXPENSE')
        ->exists())->toBeTrue();

    expect(StaffSettlementModel::query()->find($settlementId)->cash_session_id)->toBe($sessionId);
});

it('paid_by_user_id is the authenticated cashier', function () {
    $cashierUserId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $settlementId = settlementPayPrepareWaiterSettlement();

    settlementPayMarkPaid($settlementId)->assertOk();

    $settlement = StaffSettlementModel::query()->find($settlementId);

    expect((int) $settlement->paid_by_user_id)->toBe($cashierUserId)
        ->and($settlement->paid_at)->not->toBeNull();
});

it('returns cash session debug when mark-paid fails in debug mode', function () {
    config(['app.debug' => true]);

    $settlementId = settlementPayPrepareWaiterSettlement();

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);

    $response = settlementPayMarkPaid($settlementId)
        ->assertStatus(422);

    expect($response->json('message'))->toBe('Debe abrir caja para pagar esta liquidación.');
});
