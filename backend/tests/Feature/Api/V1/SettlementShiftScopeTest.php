<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);

    OfficialShiftModel::query()->where('status', 'OPEN')->update([
        'status' => 'CLOSED',
        'closed_at' => now(),
    ]);
});

afterEach(function () {
    \Illuminate\Support\Carbon::setTestNow();
});

function settlementScopeOpenCashAuto(string $token, float $opening = 0): void
{
    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => $opening,
    ], nightposOperationalHeaders($token))->assertCreated();
}

function settlementScopeAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function settlementScopeCashierToken(): string
{
    return nightposLoginPin('1234');
}

function settlementScopeChargeOrder(string $cashierToken): int
{
    $productId = nightposSeedOrderProduct();

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Venta scope',
        'waiter_user_id' => nightposDemoWaiterUserId(),
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

    return $orderId;
}

function settlementScopePayAllPending(string $cashierToken, int $shiftId): void
{
    $ids = StaffSettlementModel::query()
        ->where('official_shift_id', $shiftId)
        ->where('status', 'PENDING')
        ->pluck('id');

    foreach ($ids as $id) {
        test()->postJson("/api/v1/settlements/{$id}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($cashierToken))
            ->assertOk();
    }
}

function settlementScopeFullyCloseShift(string $adminToken, string $cashierToken, int $shiftId): void
{
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($adminToken))
        ->assertCreated();

    settlementScopePayAllPending($cashierToken, $shiftId);

    OrderModel::query()
        ->where('official_shift_id', $shiftId)
        ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 500,
    ], nightposOperationalHeaders($cashierToken))->assertOk();

    test()->postJson("/api/v1/shifts/{$shiftId}/close", [
        'counted_cash' => 500,
        'notes' => 'Cierre turno prueba',
    ], nightposOperationalHeaders($adminToken))->assertOk();
}

function settlementScopeOpenNewShift(string $adminToken): int
{
    nightposCloseOpenOfficialShifts();

    $response = test()->postJson('/api/v1/shifts/open', [
        'shift_type' => 'NIGHT',
        'business_date' => now()->format('Y-m-d'),
        'notes' => 'Turno nuevo prueba',
    ], nightposOperationalHeaders($adminToken))->assertCreated();

    return (int) $response->json('data.shift.id');
}

it('generate-current-shift does not take sales from previous closed shift', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    $shiftA = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    settlementScopeChargeOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->assertJsonPath('data.context.current_shift_id', $shiftA);

    expect(StaffSettlementModel::query()->where('official_shift_id', $shiftA)->count())->toBeGreaterThan(0);

    settlementScopeFullyCloseShift($admin, $cashier, $shiftA);

    $shiftB = settlementScopeOpenNewShift($admin);
    expect($shiftB)->not->toBe($shiftA);

    nightposOpenCashSession($cashier, 0);

    $generate = test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))
        ->assertCreated();

    expect($generate->json('data.created_items'))->toBe(0)
        ->and($generate->json('data.context.current_shift_id'))->toBe($shiftB)
        ->and($generate->json('data.sources_summary.sales'))->toBe(0);

    expect(StaffSettlementModel::query()->where('official_shift_id', $shiftB)->count())->toBe(0);
});

it('current settlements only lists open official shift', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    $shiftA = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    settlementScopeChargeOrder($cashier);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    settlementScopeFullyCloseShift($admin, $cashier, $shiftA);
    $shiftB = settlementScopeOpenNewShift($admin);

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.shift.id', $shiftB)
        ->assertJsonPath('data.summary.total_waiters', '0.00')
        ->assertJsonPath('data.context.current_shift_id', $shiftB);
});

it('close-check uses cash session official_shift_id', function () {
    $cashier = settlementScopeCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 0);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $response = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.context.current_shift_id'))->toBe($shiftId)
        ->and($response->json('data.official_shift_id'))->toBe($shiftId);
});

it('history can view settlements from previous shift', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    $shiftA = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    settlementScopeChargeOrder($cashier);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    settlementScopeFullyCloseShift($admin, $cashier, $shiftA);
    settlementScopeOpenNewShift($admin);

    test()->getJson("/api/v1/settlements/history?official_shift_id={$shiftA}", nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.settlements.0.official_shift_id', $shiftA);
});

it('new shift with no sales shows empty settlement overview', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    OrderModel::query()
        ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);

    nightposCloseOpenOfficialShifts();
    $shiftB = settlementScopeOpenNewShift($admin);
    nightposOpenCashSession($cashier, 0);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.context.current_shift_id'))->toBe($shiftB)
        ->and($response->json('data.sources_summary.sales'))->toBe(0)
        ->and($response->json('data.summary.total_pending'))->toBe('0.00')
        ->and($response->json('data.waiters'))->toBeEmpty();
});

it('cashier with a fresh empty cash session sees zero even if previous shift had settlements', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    $shiftA = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    settlementScopeChargeOrder($cashier);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    expect(StaffSettlementModel::query()->where('official_shift_id', $shiftA)->count())->toBeGreaterThan(0);

    settlementScopeFullyCloseShift($admin, $cashier, $shiftA);
    $shiftB = settlementScopeOpenNewShift($admin);
    $cashier = settlementScopeCashierToken();
    settlementScopeOpenCashAuto($cashier, 0);

    // Cajera: su caja nueva está en el turno B → totales en 0, scope my_cash_session
    $cashierView = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($cashierView->json('data.context.scope'))->toBe('my_cash_session')
        ->and($cashierView->json('data.context.current_shift_id'))->toBe($shiftB)
        ->and($cashierView->json('data.summary.total_girls'))->toBe('0.00')
        ->and($cashierView->json('data.summary.total_pending'))->toBe('0.00');

    // Admin: scope turno completo (sucursal) sobre el turno abierto B
    $adminView = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(settlementScopeAdminToken()))
        ->assertOk();

    expect($adminView->json('data.context.scope'))->toBe('shift')
        ->and($adminView->json('data.context.current_shift_id'))->toBe($shiftB);
});

it('stale auto shift with settlements shows zero for new cashier after rotation', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    \Illuminate\Support\Carbon::setTestNow('2026-06-14 22:00:00');

    $cashier = settlementScopeCashierToken();
    settlementScopeOpenCashAuto($cashier, 100);
    $shiftA = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
        ->where('status', 'OPEN')->value('id');

    settlementScopeChargeOrder($cashier);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    expect(StaffSettlementModel::query()->where('official_shift_id', $shiftA)->count())->toBeGreaterThan(0);

    nightposPrepareCashSessionClose($cashier, $admin);
    test()->postJson('/api/v1/cash/session/close', ['declared_closing_amount' => 500], nightposOperationalHeaders($cashier))
        ->assertOk();

    \Illuminate\Support\Carbon::setTestNow('2026-06-15 22:00:00');
    $cashier = settlementScopeCashierToken();
    settlementScopeOpenCashAuto($cashier, 0);

    $shiftB = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
        ->where('status', 'OPEN')->value('id');

    expect($shiftB)->not->toBe($shiftA);

    $view = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($view->json('data.context.scope'))->toBe('my_cash_session')
        ->and($view->json('data.context.current_shift_id'))->toBe($shiftB)
        ->and($view->json('data.summary.total_girls'))->toBe('0.00')
        ->and($view->json('data.summary.total_pending'))->toBe('0.00')
        ->and($view->json('data.context.empty_overview'))->toBeTrue();
});

it('pending sources does not warn waiter without commission when they had no sales in scope', function () {
    $cashier = settlementScopeCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 0);

    $response = test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.waiters_without_commission_count'))->toBe(0);
});

it('admin can still view a previous shift settlements through history', function () {
    $admin = settlementScopeAdminToken();
    $cashier = settlementScopeCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($cashier, 100);

    $shiftA = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    settlementScopeChargeOrder($cashier);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    settlementScopeFullyCloseShift($admin, $cashier, $shiftA);
    settlementScopeOpenNewShift($admin);

    $history = test()->getJson("/api/v1/settlements/history?official_shift_id={$shiftA}", nightposOperationalHeaders($admin))
        ->assertOk();

    expect(collect($history->json('data.settlements'))->pluck('official_shift_id')->unique()->all())
        ->toBe([$shiftA]);
});
