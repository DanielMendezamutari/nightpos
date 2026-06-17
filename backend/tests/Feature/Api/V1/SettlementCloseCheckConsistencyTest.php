<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function consistencyCashierToken(): string
{
    return nightposLoginPin('1234');
}

function consistencyAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function consistencyChargeGirlOrder(string $cashierToken): void
{
    $waiterToken = nightposLoginPin('5678');
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    nightposOpenCashSession($cashierToken, 500);

    $productId = nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => 80,
            'girl_amount' => 40,
            'house_amount' => 40,
        ],
    ]);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => 'Consistency girl',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    nightposResetApiAuth();
    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 80]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

it('close-check reports SETTLEMENTS_NOT_GENERATED when sources exist but nothing generated', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    $response = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    $types = collect($response->json('data.blockers'))->pluck('type')->all();
    expect($types)->toContain('SETTLEMENTS_NOT_GENERATED');
});

it('close-check reports SETTLEMENTS_PENDING_PAYMENT after generate and current-shift shows pending for cashier', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->assertJsonPath('data.created_items', fn ($v) => $v > 0);

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonPath('data.can_close', false)
        ->json('data');

    $types = collect($closeCheck['blockers'])->pluck('type')->all();
    expect($types)->toContain('SETTLEMENTS_PENDING_PAYMENT')
        ->and($closeCheck['summary']['generated_pending_count'])->toBeGreaterThan(0);

    $current = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    expect($current['context']['empty_overview'])->toBeFalse()
        ->and($current['settlement_summary']['generated_pending_count'])->toBeGreaterThan(0)
        ->and((float) $current['summary']['total_pending'])->toBeGreaterThan(0)
        ->and($current['girls'])->not->toBeEmpty();
});

it('generate again with existing pending does not create new items but reports pending', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $second = test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->json('data');

    expect($second['created_items'])->toBe(0)
        ->and($second['settlement_summary']['generated_pending_count'])->toBeGreaterThan(0);

    $current = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    expect($current['settlement_summary']['generated_pending_count'])->toBeGreaterThan(0)
        ->and($current['girls'])->not->toBeEmpty();
});

it('cashier can pay pending settlement and close-check clears payment blocker', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $pendingId = (int) StaffSettlementModel::query()->where('status', 'PENDING')->value('id');

    foreach (StaffSettlementModel::query()->where('status', 'PENDING')->pluck('id') as $id) {
        test()->postJson("/api/v1/settlements/{$id}/mark-paid", [
            'payment_method' => 'CASH',
        ], nightposOperationalHeaders($cashier))->assertOk();
    }

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    $paymentBlockers = collect($closeCheck['blockers'])
        ->where('type', 'SETTLEMENTS_PENDING_PAYMENT')
        ->count();

    expect($paymentBlockers)->toBe(0);
});

it('close-check and generate use the same official_shift_id as cash session', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    $sessionShiftId = (int) CashSessionModel::query()->where('status', 'OPEN')->value('official_shift_id');
    $openShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    expect($closeCheck['official_shift_id'])->toBe($sessionShiftId)
        ->and($closeCheck['context']['cash_session_official_shift_id'])->toBe($sessionShiftId)
        ->and($closeCheck['context']['current_shift_id'])->toBe($sessionShiftId)
        ->and($openShiftId)->toBe($sessionShiftId);

    $generate = test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->json('data');

    expect($generate['shift_id'])->toBe($sessionShiftId)
        ->and($generate['context']['current_shift_id'])->toBe($sessionShiftId);
});

it('close-check ignores waiter pending without cash session id in my_cash_session scope', function () {
    $cashier = consistencyCashierToken();
    consistencyChargeGirlOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $sessionId = (int) CashSessionModel::query()->where('status', 'OPEN')->value('id');

    StaffSettlementModel::query()
        ->where('status', 'PENDING')
        ->where('staff_role', 'WAITER')
        ->update(['cash_session_id' => null]);

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    expect($closeCheck['summary']['pending_waiters'])->toBe(0);

    StaffSettlementModel::query()
        ->where('status', 'PENDING')
        ->where('staff_role', 'WAITER')
        ->update(['cash_session_id' => $sessionId]);

    $closeCheckScoped = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    expect($closeCheckScoped['summary']['pending_waiters'])->toBeGreaterThan(0);
});

it('allows close when no sources and no pending settlements', function () {
    $cashier = consistencyCashierToken();
    nightposOpenCashSession($cashier, 100);

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data');

    $settlementBlockers = collect($closeCheck['blockers'])
        ->whereIn('type', ['SETTLEMENTS_NOT_GENERATED', 'SETTLEMENTS_PENDING_PAYMENT'])
        ->count();

    expect($settlementBlockers)->toBe(0);
});
