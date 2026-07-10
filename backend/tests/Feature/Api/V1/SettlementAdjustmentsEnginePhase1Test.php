<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
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

function saeSetCleaning(int $settlementId, float $amount, ?string $token = null): array
{
    $token ??= saeCashierToken();
    nightposResetApiAuth();

    return test()->patchJson("/api/v1/settlements/{$settlementId}/cleaning-deduction", [
        'amount' => $amount,
    ], nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data');
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

it('does not apply cleaning deduction automatically when girl gross reaches threshold', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    expect($settlement->gross_amount)->toBe('100.00')
        ->and($settlement->adjustments_total)->toBe('0.00')
        ->and($settlement->net_amount)->toBe('100.00')
        ->and($settlement->total_amount)->toBe('100.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->exists())->toBeFalse();
});

it('does not apply cleaning deduction automatically when girl gross is 500', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 500);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    expect($settlement->gross_amount)->toBe('500.00')
        ->and($settlement->adjustments_total)->toBe('0.00')
        ->and($settlement->net_amount)->toBe('500.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->where('staff_settlement_id', $settlement->id)
            ->count())->toBe(0);
});

it('does not apply cleaning automatically when settlements are regenerated', function () {
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
        ->count())->toBe(0)
        ->and($settlement->net_amount)->toBe('100.00');
});

it('does not charge cleaning automatically again on a partial cut after first cut was paid', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100, 'Cut 1');
    saeGenerate($admin);

    $first = saeGirlSettlement($girlId);
    expect($first->net_amount)->toBe('100.00');

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

it('exposes gross net and no automatic cleaning adjustments on settlement detail api', function () {
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
        ->and($response->json('data.settlement.net_amount'))->toBe('100.00')
        ->and($response->json('data.adjustments'))->toBe([]);
});

it('cashier can register manual cleaning and net decreases correctly', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);
    $response = saeSetCleaning($settlement->id, 10, $cashier);

    $settlement->refresh();

    expect($response['cleaning_amount'])->toBe('10.00')
        ->and($settlement->adjustments_total)->toBe('-10.00')
        ->and($settlement->net_amount)->toBe('90.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->count())->toBe(1);
});

it('removes cleaning deduction when cashier saves zero', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);
    saeSetCleaning($settlement->id, 10, $cashier);
    saeSetCleaning($settlement->id, 0, $cashier);

    $settlement->refresh();

    expect($settlement->adjustments_total)->toBe('0.00')
        ->and($settlement->net_amount)->toBe('100.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->exists())->toBeFalse();
});

it('updates cleaning from 10 to 20 without duplicating rows', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 200);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);
    saeSetCleaning($settlement->id, 10, $cashier);
    saeSetCleaning($settlement->id, 20, $cashier);

    $settlement->refresh();

    expect($settlement->adjustments_total)->toBe('-20.00')
        ->and($settlement->net_amount)->toBe('180.00')
        ->and(StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->count())->toBe(1);
});

it('rejects negative manual cleaning amount', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    test()->patchJson("/api/v1/settlements/{$settlement->id}/cleaning-deduction", [
        'amount' => -5,
    ], nightposOperationalHeaders($cashier))->assertStatus(422);
});

it('rejects manual cleaning amount greater than gross', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);

    test()->patchJson("/api/v1/settlements/{$settlement->id}/cleaning-deduction", [
        'amount' => 120,
    ], nightposOperationalHeaders($cashier))
        ->assertStatus(422)
        ->assertJsonPath('message', 'El monto de limpieza no puede superar el bruto de la liquidación.');
});

it('does not allow modifying paid settlement cleaning', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();
    $girlId = saeGirlId();

    saeChargeGirlConsumption($cashier, $waiter, $girlId, 100);
    saeGenerate($admin);

    $settlement = saeGirlSettlement($girlId);
    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashier))->assertOk();

    test()->patchJson("/api/v1/settlements/{$settlement->id}/cleaning-deduction", [
        'amount' => 10,
    ], nightposOperationalHeaders($cashier))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Solo se puede modificar una liquidación pendiente.');
});

it('does not allow manual cleaning for waiter settlements', function () {
    $admin = saeAdminToken();
    $cashier = saeCashierToken();
    $waiter = saeWaiterToken();

    nightposOpenCashSession($cashier, 500);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $orderId = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Waiter no cleaning',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    saeGenerate($admin);

    $waiterSettlement = StaffSettlementModel::query()
        ->where('staff_user_id', $waiterId)
        ->where('settlement_type', 'WAITER')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();

    test()->patchJson("/api/v1/settlements/{$waiterSettlement->id}/cleaning-deduction", [
        'amount' => 10,
    ], nightposOperationalHeaders($cashier))
        ->assertStatus(422)
        ->assertJsonPath('message', 'El cobro de limpieza manual solo aplica a liquidaciones de chicas.');
});

it('does not allow manual cleaning for cleaning staff settlements', function () {
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $shiftId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $cleaningUserId = (int) UserModel::query()->where('username', 'limpieza.demo')->value('id');

    $settlement = StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => null,
        'staff_user_id' => $cleaningUserId,
        'staff_role' => 'CLEANING',
        'settlement_type' => 'CLEANING',
        'total_amount' => '40.00',
        'gross_amount' => '40.00',
        'adjustments_total' => '0.00',
        'net_amount' => '40.00',
        'status' => 'PENDING',
    ]);

    test()->patchJson("/api/v1/settlements/{$settlement->id}/cleaning-deduction", [
        'amount' => 10,
    ], nightposOperationalHeaders(saeCashierToken()))
        ->assertStatus(422)
        ->assertJsonPath('message', 'El cobro de limpieza manual solo aplica a liquidaciones de chicas.');
});
