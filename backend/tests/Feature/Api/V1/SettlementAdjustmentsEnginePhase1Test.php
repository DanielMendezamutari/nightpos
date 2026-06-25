<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function saeAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function saeCashierToken(): string
{
    return nightposLoginPin('1234');
}

function saeWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function saeGirlId(): int
{
    return (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');
}

function saeGenerate(string $token = null): void
{
    $token ??= saeAdminToken();
    nightposResetApiAuth();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated();
}

function saeChargeGirlConsumption(string $cashierToken, string $waiterToken, int $girlUserId, float $girlAmount, string $table = 'SAE Girl'): void
{
    nightposOpenCashSession($cashierToken, 500);

    $productId = nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => $girlAmount * 2,
            'girl_amount' => $girlAmount,
            'house_amount' => $girlAmount,
        ],
    ]);

    $waiterId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'garzon.demo')
        ->value('id');

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => $table,
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlUserId,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $girlAmount * 2]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

function saeFinishRoom(string $adminToken, int $girlId, float $girlAmount, string $label = 'SAE Room'): void
{
    $roomId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => $label,
        'total_amount' => $girlAmount,
        'girl_percent' => 100,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($adminToken))->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomId}/finish", [], nightposOperationalHeaders($adminToken))->assertOk();
}

function saeGirlSettlement(int $girlId): StaffSettlementModel
{
    return StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();
}

it('does not apply cleaning deduction when girl gross is below threshold', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 80);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    expect($settlement->gross_amount)->toBe('80.00')
        ->and($settlement->adjustments_total)->toBe('0.00')
        ->and($settlement->net_amount)->toBe('80.00')
        ->and($settlement->total_amount)->toBe('80.00')
        ->and(StaffSettlementAdjustmentModel::query()->where('staff_settlement_id', $settlement->id)->count())->toBe(0);
});

it('applies cleaning deduction when girl gross reaches threshold', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    expect($settlement->gross_amount)->toBe('100.00')
        ->and($settlement->adjustments_total)->toBe('-10.00')
        ->and($settlement->net_amount)->toBe('90.00')
        ->and($settlement->total_amount)->toBe('90.00');

    $adjustment = StaffSettlementAdjustmentModel::query()
        ->where('staff_settlement_id', $settlement->id)
        ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
        ->first();

    expect($adjustment)->not->toBeNull()
        ->and($adjustment->amount)->toBe('-10.00');
});

it('does not duplicate cleaning deduction when settlements are regenerated', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    expect(StaffSettlementAdjustmentModel::query()
        ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
        ->where('staff_settlement_id', $settlement->id)
        ->count())->toBe(1)
        ->and($settlement->net_amount)->toBe('90.00');
});

it('does not charge cleaning again on a partial cut after first cut was paid', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100, 'Cut 1');
    saeGenerate($admin);

    $first = saeGirlSettlement($girlId);
    expect($first->net_amount)->toBe('90.00');

    test()->postJson("/api/v1/settlements/{$first->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertOk();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 50, 'Cut 2');
    saeGenerate($admin);

    $second = StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();

    expect($second->gross_amount)->toBe('50.00')
        ->and($second->adjustments_total)->toBe('0.00')
        ->and($second->net_amount)->toBe('50.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $second->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->exists())->toBeFalse();
});

it('does not apply girl cleaning deduction to waiter settlements', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();

    nightposOpenCashSession($cashier, 500);

    $waiterId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'garzon.demo')
        ->value('id');

    $orderId = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Waiter SAE',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    saeGenerate($admin);

    $waiterSettlement = StaffSettlementModel::query()
        ->where('staff_user_id', $waiterId)
        ->where('settlement_type', 'WAITER')
        ->firstOrFail();

    expect($waiterSettlement->gross_amount)->not->toBe('0.00')
        ->and($waiterSettlement->adjustments_total)->toBe('0.00')
        ->and($waiterSettlement->net_amount)->toBe($waiterSettlement->gross_amount);
});

it('exposes gross net and adjustments on settlement detail api', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    $response = test()->getJson("/api/v1/settlements/{$settlement->id}", nightposOperationalHeaders($admin))
        ->assertOk();

    expect($response->json('data.settlement.gross_amount'))->toBe('100.00')
        ->and($response->json('data.settlement.net_amount'))->toBe('90.00')
        ->and($response->json('data.adjustments.0.adjustment_type'))->toBe('CLEANING_DEDUCTION')
        ->and($response->json('data.adjustments.0.amount'))->toBe('-10.00');
});
