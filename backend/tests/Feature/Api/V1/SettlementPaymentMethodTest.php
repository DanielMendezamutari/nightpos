<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function settlementMethodPendingId(string $type): int
{
    $id = StaffSettlementModel::query()
        ->where('settlement_type', $type)
        ->where('status', 'PENDING')
        ->value('id');

    expect($id)->not->toBeNull();

    return (int) $id;
}

function settlementMethodMarkPaid(int $settlementId, string $paymentMethod = 'CASH'): void
{
    settlementPayMarkPaid($settlementId, settlementPayCashierToken(), $paymentMethod)->assertOk();
}

function settlementMethodLatestExpense(): CashMovementModel
{
    return CashMovementModel::query()
        ->where('movement_type', 'EXPENSE')
        ->latest('id')
        ->firstOrFail();
}

function settlementMethodFinancial(): array
{
    return test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders(settlementPayCashierToken()))
        ->assertOk()
        ->json('data.session.financial_summary');
}

it('paying waiter settlement with CASH creates EXPENSE CASH movement', function () {
    settlementPayPrepareWaiterSettlement();
    $settlementId = settlementMethodPendingId('WAITER');

    settlementMethodMarkPaid($settlementId, 'CASH');

    $movement = settlementMethodLatestExpense();

    expect($movement->payment_method)->toBe('CASH')
        ->and($movement->source_type)->toBe('STAFF_SETTLEMENT')
        ->and((int) $movement->source_id)->toBe($settlementId);
});

it('paying girl settlement with QR creates EXPENSE QR movement', function () {
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
    $settlementId = settlementMethodPendingId('GIRL');

    settlementMethodMarkPaid($settlementId, 'QR');

    expect(settlementMethodLatestExpense()->payment_method)->toBe('QR');
});

it('paying cleaning settlement with CARD creates EXPENSE CARD movement', function () {
    settlementPayOpenCashierSession(settlementPayCashierToken());

    $roomId = (int) test()->postJson('/api/v1/rooms', [
        'code' => 'PM1',
        'name' => 'Room PM1',
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
    $settlementId = settlementMethodPendingId('CLEANING');

    settlementMethodMarkPaid($settlementId, 'CARD');

    expect(settlementMethodLatestExpense()->payment_method)->toBe('CARD');
});

it('expected cash decreases only with CASH settlement expense', function () {
    settlementPayPrepareWaiterSettlement();
    $settlementId = settlementMethodPendingId('WAITER');

    $before = settlementMethodFinancial();

    settlementMethodMarkPaid($settlementId, 'CASH');

    $after = settlementMethodFinancial();

    expect((float) $after['expected_cash'])->toBeLessThan((float) $before['expected_cash'])
        ->and((float) $after['expected_by_method']['qr'])->toBe((float) ($before['expected_by_method']['qr'] ?? 0));
});

it('qr net decreases with QR settlement expense', function () {
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
    $settlementId = settlementMethodPendingId('GIRL');

    $before = settlementMethodFinancial();

    settlementMethodMarkPaid($settlementId, 'QR');

    $after = settlementMethodFinancial();

    expect((float) $after['expected_by_method']['qr'])->toBeLessThan((float) $before['expected_by_method']['qr'])
        ->and((float) $after['expected_cash'])->toBe((float) $before['expected_cash']);
});

it('card net decreases with CARD settlement expense', function () {
    settlementPayPrepareWaiterSettlement();
    $settlementId = settlementMethodPendingId('WAITER');

    $before = settlementMethodFinancial();

    settlementMethodMarkPaid($settlementId, 'CARD');

    $after = settlementMethodFinancial();

    expect((float) $after['expected_by_method']['card'])->toBeLessThan((float) $before['expected_by_method']['card'])
        ->and((float) $after['expected_cash'])->toBe((float) $before['expected_cash']);
});

it('rejects mark-paid without payment_method', function () {
    settlementPayPrepareWaiterSettlement();
    $settlementId = settlementMethodPendingId('WAITER');

    test()->postJson(
        "/api/v1/settlements/{$settlementId}/mark-paid",
        [],
        nightposOperationalHeaders(settlementPayCashierToken()),
    )->assertStatus(422);
});

it('cannot pay settlement without open cash session', function () {
    settlementPayPrepareWaiterSettlement();
    $settlementId = settlementMethodPendingId('WAITER');

    \App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel::query()
        ->where('status', 'OPEN')
        ->update(['status' => 'CLOSED', 'closed_at' => now()]);

    settlementPayMarkPaid($settlementId)->assertStatus(422);
});

it('quick cash movement requires open cash session', function () {
    \App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel::query()
        ->where('status', 'OPEN')
        ->update(['status' => 'CLOSED', 'closed_at' => now()]);

    $reasonId = (int) \App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel::query()
        ->where('name', 'Pago cajera')
        ->value('id');

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 50,
        'cash_movement_reason_id' => $reasonId,
        'payment_method' => 'CASH',
        'notes' => 'Pago turno',
    ], nightposOperationalHeaders(settlementPayCashierToken()))
        ->assertStatus(422);
});

it('quick cash movement is linked to current user cash session', function () {
    $cashier = settlementPayCashierToken();
    $sessionId = settlementPayOpenCashierSession($cashier);

    $reasonId = (int) \App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel::query()
        ->where('name', 'Pago cajera')
        ->value('id');

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 100,
        'cash_movement_reason_id' => $reasonId,
        'payment_method' => 'CASH',
        'notes' => 'Pago turno noche',
    ], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->assertJsonPath('data.session.id', $sessionId);

    expect(CashMovementModel::query()
        ->where('cash_session_id', $sessionId)
        ->where('payment_method', 'CASH')
        ->where('amount', 100)
        ->exists())->toBeTrue();
});
