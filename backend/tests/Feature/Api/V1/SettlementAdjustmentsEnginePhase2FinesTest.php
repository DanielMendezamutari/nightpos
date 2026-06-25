<?php



declare(strict_types=1);



use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffFineModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Domain\Enums\SettlementAdjustmentType;

use App\Shared\Domain\Enums\StaffFineStatus;

use Database\Seeders\NightPosSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;



uses(RefreshDatabase::class);



beforeEach(function () {

    $this->seed(NightPosSeeder::class);

    nightposEnsureShiftOpen();

});



function sf2AdminToken(): string

{

    return nightposLoginPassword('admin.demo', 'AdminDemo123!');

}



function sf2CashierToken(): string

{

    return nightposLoginPin('1234');

}



function sf2WaiterToken(): string

{

    return nightposLoginPin('5678');

}



function sf2GirlId(): int

{

    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');

}



function sf2WaiterId(): int

{

    return (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

}



function sf2CreateFine(int $staffUserId, float $amount, string $reason, string $staffRole = 'GIRL', ?string $token = null): int

{

    $token ??= sf2AdminToken();

    nightposResetApiAuth();



    $response = test()->postJson('/api/v1/staff-fines', [

        'staff_user_id' => $staffUserId,

        'staff_role' => $staffRole,

        'amount' => $amount,

        'reason' => $reason,

    ], nightposOperationalHeaders($token));



    $response->assertCreated();



    return (int) $response->json('data.fine.id');

}



function sf2ChargeGirl(float $girlAmount, string $table = 'SF2 Girl'): void

{

    $cashier = sf2CashierToken();

    $waiter = sf2WaiterToken();

    $girlId = sf2GirlId();



    nightposOpenCashSession($cashier, 500);



    $productId = nightposSeedOrderProduct([

        [

            'sale_mode' => 'CON_ACOMPANANTE',

            'price' => $girlAmount * 2,

            'girl_amount' => $girlAmount,

            'house_amount' => $girlAmount,

        ],

    ]);



    $orderId = test()->postJson('/api/v1/orders', [

        'table_label' => $table,

        'waiter_user_id' => sf2WaiterId(),

    ], nightposOperationalHeaders($waiter))

        ->assertCreated()

        ->json('data.order.id');



    test()->postJson("/api/v1/orders/{$orderId}/items", [

        'product_id' => $productId,

        'sale_mode' => 'CON_ACOMPANANTE',

        'quantity' => 1,

        'girl_user_id' => $girlId,

    ], nightposOperationalHeaders($waiter))->assertCreated();



    test()->postJson("/api/v1/orders/{$orderId}/charge", [

        'payments' => [['method' => 'CASH', 'amount' => $girlAmount * 2]],

    ], nightposOperationalHeaders($cashier))->assertCreated();

}



function sf2Generate(?string $token = null): void

{

    $token ??= sf2AdminToken();

    nightposResetApiAuth();



    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))

        ->assertCreated();

}



function sf2GirlSettlement(): StaffSettlementModel

{

    return StaffSettlementModel::query()

        ->where('staff_user_id', sf2GirlId())

        ->where('settlement_type', 'GIRL')

        ->where('status', 'PENDING')

        ->latest('id')

        ->firstOrFail();

}



function sf2PayPreview(int $settlementId, array $appliedFineIds = [], ?string $token = null): array

{

    $token ??= sf2AdminToken();

    nightposResetApiAuth();



    $query = http_build_query(['applied_fine_ids' => $appliedFineIds]);



    return test()->getJson(

        "/api/v1/settlements/{$settlementId}/pay-preview?{$query}",

        nightposOperationalHeaders($token),

    )->assertOk()->json('data');

}



function sf2MarkPaid(int $settlementId, array $appliedFineIds = [], ?string $token = null): void

{

    $token ??= sf2AdminToken();

    nightposResetApiAuth();
    nightposOpenCashSession($token, 500);

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [

        'payment_method' => 'CASH',

        'applied_fine_ids' => $appliedFineIds,

    ], nightposOperationalHeaders($token))->assertOk();

}



it('creates a pending staff fine', function () {

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    $fine = StaffFineModel::query()->findOrFail($fineId);



    expect($fine->status)->toBe(StaffFineStatus::Pending->value)

        ->and($fine->amount)->toBe('30.00')

        ->and($fine->reason)->toBe('Vaso roto')

        ->and($fine->applied_settlement_id)->toBeNull();

});



it('cancels a pending staff fine', function () {

    $fineId = sf2CreateFine(sf2GirlId(), 20, 'Llegada tarde');



    test()->postJson("/api/v1/staff-fines/{$fineId}/cancel", [

        'cancellation_reason' => 'Error de registro',

    ], nightposOperationalHeaders(sf2AdminToken()))->assertOk();



    $fine = StaffFineModel::query()->findOrFail($fineId);



    expect($fine->status)->toBe(StaffFineStatus::Cancelled->value)

        ->and($fine->cancellation_reason)->toBe('Error de registro');

});



it('does not cancel an applied staff fine', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2MarkPaid($settlement->id, [$fineId]);



    test()->postJson("/api/v1/staff-fines/{$fineId}/cancel", [

        'cancellation_reason' => 'Intento tardío',

    ], nightposOperationalHeaders(sf2AdminToken()))->assertStatus(422);

});



it('shows fine created before settlement generation in pay preview', function () {

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $preview = sf2PayPreview($settlement->id, [$fineId]);



    expect($preview['gross_amount'])->toBe('100.00')

        ->and(collect($preview['available_fines'])->pluck('id'))->toContain($fineId)

        ->and(collect($preview['adjustments'])->firstWhere('type', 'MANUAL_FINE')['fine_id'])->toBe($fineId);

});



it('recalculates net amount in pay preview when fine is selected', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    $withoutFine = sf2PayPreview($settlement->id, []);

    $withFine = sf2PayPreview($settlement->id, [$fineId]);



    expect($withoutFine['net_amount'])->toBe('90.00')

        ->and($withFine['net_amount'])->toBe('60.00');

});



it('keeps unselected fine pending after mark paid', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $appliedFineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');

    $pendingFineId = sf2CreateFine(sf2GirlId(), 20, 'Llegada tarde');



    sf2MarkPaid($settlement->id, [$appliedFineId]);



    expect(StaffFineModel::query()->find($appliedFineId)->status)->toBe(StaffFineStatus::Applied->value)

        ->and(StaffFineModel::query()->find($pendingFineId)->status)->toBe(StaffFineStatus::Pending->value);

});



it('applies selected fine on mark paid', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2MarkPaid($settlement->id, [$fineId]);



    $fine = StaffFineModel::query()->findOrFail($fineId);



    expect($fine->status)->toBe(StaffFineStatus::Applied->value)

        ->and((int) $fine->applied_settlement_id)->toBe($settlement->id)

        ->and($fine->applied_at)->not->toBeNull()

        ->and($fine->applied_by_user_id)->not->toBeNull();

});



it('creates manual fine adjustment on mark paid', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2MarkPaid($settlement->id, [$fineId]);



    $adjustment = StaffSettlementAdjustmentModel::query()

        ->where('staff_settlement_id', $settlement->id)

        ->where('adjustment_type', SettlementAdjustmentType::ManualFine->value)

        ->where('staff_fine_id', $fineId)

        ->first();



    expect($adjustment)->not->toBeNull()

        ->and($adjustment->amount)->toBe('-30.00')

        ->and($adjustment->dedup_key)->toBe("fine:{$fineId}");

});



it('uses net amount including fines in cash movement', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2MarkPaid($settlement->id, [$fineId]);



    $movement = CashMovementModel::query()

        ->where('source_type', 'STAFF_SETTLEMENT')

        ->where('source_id', $settlement->id)

        ->firstOrFail();



    expect($movement->amount)->toBe('60.00');



    $settlement->refresh();

    expect($settlement->net_amount)->toBe('60.00')

        ->and($settlement->total_amount)->toBe('60.00');

});



it('does not allow applying fine for another staff member', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $waiterFineId = sf2CreateFine(sf2WaiterId(), 25, 'Multa garzón', 'WAITER');



    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [

        'payment_method' => 'CASH',

        'applied_fine_ids' => [$waiterFineId],

    ], nightposOperationalHeaders(sf2AdminToken()))->assertStatus(422);

});



it('does not allow applying fine from another branch', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $tenant = TenantModel::query()->firstOrFail();

    $otherBranch = BranchModel::query()->create([

        'tenant_id' => $tenant->id,

        'name' => 'Otra sucursal',

        'code' => 'OTRA',

        'status' => 'active',

    ]);



    $otherFine = StaffFineModel::query()->create([

        'tenant_id' => $tenant->id,

        'branch_id' => $otherBranch->id,

        'official_shift_id' => $settlement->official_shift_id,

        'cash_session_id' => null,

        'staff_user_id' => sf2GirlId(),

        'staff_role' => 'GIRL',

        'amount' => '40.00',

        'reason' => 'Multa otra sucursal',

        'status' => StaffFineStatus::Pending->value,

        'created_by_user_id' => (int) UserModel::query()->where('username', 'admin.demo')->value('id'),

    ]);



    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [

        'payment_method' => 'CASH',

        'applied_fine_ids' => [$otherFine->id],

    ], nightposOperationalHeaders(sf2AdminToken()))->assertStatus(422);

});



it('does not allow applying cancelled fine', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    test()->postJson("/api/v1/staff-fines/{$fineId}/cancel", [

        'cancellation_reason' => 'Perdonada',

    ], nightposOperationalHeaders(sf2AdminToken()))->assertOk();



    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [

        'payment_method' => 'CASH',

        'applied_fine_ids' => [$fineId],

    ], nightposOperationalHeaders(sf2AdminToken()))->assertStatus(422);

});



it('does not duplicate fine adjustment when settlement is already paid', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineId = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');



    sf2MarkPaid($settlement->id, [$fineId]);



    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [

        'payment_method' => 'CASH',

        'applied_fine_ids' => [$fineId],

    ], nightposOperationalHeaders(sf2AdminToken()))->assertStatus(422);



    expect(StaffSettlementAdjustmentModel::query()

        ->where('staff_settlement_id', $settlement->id)

        ->where('adjustment_type', SettlementAdjustmentType::ManualFine->value)

        ->count())->toBe(1);

});



it('calculates net correctly with cleaning and selected fines', function () {

    sf2ChargeGirl(100);

    sf2Generate();



    $settlement = sf2GirlSettlement();

    $fineA = sf2CreateFine(sf2GirlId(), 30, 'Vaso roto');

    $fineB = sf2CreateFine(sf2GirlId(), 20, 'Llegada tarde');



    $preview = sf2PayPreview($settlement->id, [$fineA, $fineB]);



    expect($preview['gross_amount'])->toBe('100.00')

        ->and(collect($preview['adjustments'])->firstWhere('type', 'CLEANING_DEDUCTION')['amount'])->toBe('-10.00')

        ->and($preview['net_amount'])->toBe('40.00');



    sf2MarkPaid($settlement->id, [$fineA, $fineB]);



    $settlement->refresh();

    expect($settlement->gross_amount)->toBe('100.00')

        ->and($settlement->adjustments_total)->toBe('-60.00')

        ->and($settlement->net_amount)->toBe('40.00');

});



it('lists staff fines via api', function () {

    sf2CreateFine(sf2GirlId(), 15, 'Multa listada');



    $response = test()->getJson('/api/v1/staff-fines?status=PENDING&staff_user_id='.sf2GirlId(), nightposOperationalHeaders(sf2AdminToken()))

        ->assertOk();



    expect($response->json('data.fines'))->toHaveCount(1)

        ->and($response->json('data.fines.0.reason'))->toBe('Multa listada');

});


