<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Domain\Enums\PrintJobType;
use App\Shared\Domain\Enums\SettlementAdjustmentType;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
    spaRegisterPrintDevice();
});

function spaAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function spaCashierToken(): string
{
    return nightposLoginPin('1234');
}

function spaWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function spaGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function spaWaiterId(): int
{
    return (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
}

function spaRegisterPrintDevice(): void
{
    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'Settlement Print '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders(spaAdminToken()))->assertCreated();
}

function spaChargeGirl(float $girlAmount, string $table = 'SPA Girl'): void
{
    $cashier = spaCashierToken();
    $waiter = spaWaiterToken();
    $girlId = spaGirlId();

    nightposOpenCashSession($cashier, 500);

    $productId = nightposSeedOrderProduct([[
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => $girlAmount * 2,
        'girl_amount' => $girlAmount,
        'house_amount' => $girlAmount,
    ]]);

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => $table,
        'waiter_user_id' => spaWaiterId(),
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

function spaGenerate(?string $token = null): void
{
    $token ??= spaAdminToken();
    nightposResetApiAuth();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated();
}

function spaGirlSettlement(): StaffSettlementModel
{
    return StaffSettlementModel::query()
        ->where('staff_user_id', spaGirlId())
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();
}

function spaWaiterSettlement(): StaffSettlementModel
{
    return StaffSettlementModel::query()
        ->where('staff_user_id', spaWaiterId())
        ->where('settlement_type', 'WAITER')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();
}

function spaCreateWaiterFine(float $amount, string $reason = 'Multa garzon'): int
{
    nightposResetApiAuth();

    return (int) test()->postJson('/api/v1/staff-fines', [
        'staff_user_id' => spaWaiterId(),
        'staff_role' => 'WAITER',
        'amount' => $amount,
        'reason' => $reason,
    ], nightposOperationalHeaders(spaAdminToken()))
        ->assertCreated()
        ->json('data.fine.id');
}

function spaCreateFine(float $amount, string $reason = 'Multa test'): int
{
    nightposResetApiAuth();

    return (int) test()->postJson('/api/v1/staff-fines', [
        'staff_user_id' => spaGirlId(),
        'staff_role' => 'GIRL',
        'amount' => $amount,
        'reason' => $reason,
    ], nightposOperationalHeaders(spaAdminToken()))
        ->assertCreated()
        ->json('data.fine.id');
}

function spaApplyDiscount(int $settlementId, string $mode, float $value, string $reason = 'Descuento gerencia'): void
{
    nightposResetApiAuth();
    nightposOpenCashSession(spaAdminToken(), 500);

    test()->postJson("/api/v1/settlements/{$settlementId}/manual-discount", [
        'discount_mode' => $mode,
        'discount_value' => $value,
        'reason' => $reason,
    ], nightposOperationalHeaders(spaAdminToken()))->assertOk();
}

function spaMarkPaid(int $settlementId, array $appliedFineIds = [], string $paymentMethod = 'CASH'): array
{
    nightposResetApiAuth();
    nightposOpenCashSession(spaAdminToken(), 500);

    return test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => $paymentMethod,
        'applied_fine_ids' => $appliedFineIds,
    ], nightposOperationalHeaders(spaAdminToken()))
        ->assertOk()
        ->json('data');
}

it('stores payment_method when paying settlement', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id, [], 'QR');

    $fresh = StaffSettlementModel::query()->findOrFail($settlement->id);

    expect($fresh->payment_method)->toBe('QR');
});

it('stores cash_movement_id when paying settlement', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id);

    $fresh = StaffSettlementModel::query()->findOrFail($settlement->id);

    expect($fresh->cash_movement_id)->not->toBeNull();

    $movement = CashMovementModel::query()->find($fresh->cash_movement_id);

    expect($movement)->not->toBeNull()
        ->and($movement->source_type)->toBe('STAFF_SETTLEMENT')
        ->and((int) $movement->source_id)->toBe($settlement->id);
});

it('uses net_amount for cash movement expense', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();
    spaApplyDiscount($settlement->id, 'AMOUNT', 15);

    $settlement->refresh();
    $expectedNet = (float) $settlement->net_amount;

    spaMarkPaid($settlement->id);

    $fresh = StaffSettlementModel::query()->findOrFail($settlement->id);
    $movement = CashMovementModel::query()->findOrFail($fresh->cash_movement_id);

    expect((float) $movement->amount)->toBe($expectedNet);
});

it('calculates percent manual discount on gross plus cleaning base', function () {
    spaChargeGirl(100);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaApplyDiscount($settlement->id, 'PERCENT', 5);

    $adjustment = StaffSettlementAdjustmentModel::query()
        ->where('staff_settlement_id', $settlement->id)
        ->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value)
        ->firstOrFail();

    expect((float) $adjustment->calculation_base)->toBe(90.0)
        ->and((float) $adjustment->amount)->toBe(-4.5);
});

it('applies fixed amount manual discount correctly', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaApplyDiscount($settlement->id, 'AMOUNT', 20);

    $adjustment = StaffSettlementAdjustmentModel::query()
        ->where('staff_settlement_id', $settlement->id)
        ->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value)
        ->firstOrFail();

    expect((float) $adjustment->amount)->toBe(-20.0);

    $settlement->refresh();

    expect((float) $settlement->net_amount)->toBe(90.0);
});

it('rejects manual discount greater than available balance', function () {
    spaChargeGirl(100);
    spaGenerate();
    $settlement = spaGirlSettlement();

    nightposResetApiAuth();
    nightposOpenCashSession(spaAdminToken(), 500);

    test()->postJson("/api/v1/settlements/{$settlement->id}/manual-discount", [
        'discount_mode' => 'AMOUNT',
        'discount_value' => 200,
        'reason' => 'Excesivo',
    ], nightposOperationalHeaders(spaAdminToken()))->assertStatus(422);
});

it('rejects manual discount on paid settlement', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id);

    nightposResetApiAuth();
    nightposOpenCashSession(spaAdminToken(), 500);

    test()->postJson("/api/v1/settlements/{$settlement->id}/manual-discount", [
        'discount_mode' => 'AMOUNT',
        'discount_value' => 10,
        'reason' => 'Tarde',
    ], nightposOperationalHeaders(spaAdminToken()))->assertStatus(422);
});

it('generates consecutive ticket numbers per branch and year', function () {
    spaChargeGirl(120, 'SPA Girl A');
    spaGenerate();
    $first = spaGirlSettlement();
    spaMarkPaid($first->id);

    spaChargeGirl(80, 'SPA Girl B');
    spaGenerate();
    $second = spaGirlSettlement();
    spaMarkPaid($second->id);

    $branch = BranchModel::query()->findOrFail($first->branch_id);
    $prefix = strtoupper((string) ($branch->code ?: ('B'.$branch->id)));
    $year = now()->format('Y');

    $firstFresh = StaffSettlementModel::query()->findOrFail($first->id);
    $secondFresh = StaffSettlementModel::query()->findOrFail($second->id);

    expect($firstFresh->ticket_number)->toBe("{$prefix}-{$year}-000001")
        ->and($secondFresh->ticket_number)->toBe("{$prefix}-{$year}-000002");
});

it('creates settlement payment print job when paying', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->first();

    expect($job)->not->toBeNull()
        ->and($job->content_text)->toContain('LIQUIDACION PAGADA');
});

it('includes gross cleaning discount fines and net in ticket content', function () {
    spaChargeGirl(100);
    spaGenerate();
    $settlement = spaGirlSettlement();
    spaApplyDiscount($settlement->id, 'PERCENT', 5);
    $fineId = spaCreateFine(20, 'Vaso roto');

    spaMarkPaid($settlement->id, [$fineId]);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->firstOrFail();

    $content = $job->content_text;

    expect($content)->toContain('100.00')
        ->and($content)->toContain('-10.00')
        ->and($content)->toContain('-4.50')
        ->and($content)->toContain('-20.00')
        ->and($content)->toContain('65.50');
});

it('increments print_count on reprint', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();
    spaMarkPaid($settlement->id);

    nightposResetApiAuth();

    test()->postJson("/api/v1/settlements/{$settlement->id}/print", [
        'reprint' => true,
    ], nightposOperationalHeaders(spaAdminToken()))->assertOk();

    $fresh = StaffSettlementModel::query()->findOrFail($settlement->id);

    expect($fresh->print_count)->toBe(1);
});

it('shows reprint banner in ticket content', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();
    spaMarkPaid($settlement->id);

    nightposResetApiAuth();

    test()->postJson("/api/v1/settlements/{$settlement->id}/print", [
        'reprint' => true,
    ], nightposOperationalHeaders(spaAdminToken()))->assertOk();

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->orderByDesc('id')
        ->firstOrFail();

    expect($job->content_text)->toContain('REIMPRESION')
        ->and($job->content_text)->toContain('N° 1');
});

it('records SETTLEMENT_PAID audit log', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id);

    $log = AuditLogModel::query()
        ->where('action', 'SETTLEMENT_PAID')
        ->where('subject_type', 'staff_settlement')
        ->where('subject_id', $settlement->id)
        ->first();

    expect($log)->not->toBeNull();
});

it('records SETTLEMENT_REPRINTED audit log', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();
    spaMarkPaid($settlement->id);

    nightposResetApiAuth();

    test()->postJson("/api/v1/settlements/{$settlement->id}/print", [
        'reprint' => true,
    ], nightposOperationalHeaders(spaAdminToken()))->assertOk();

    $log = AuditLogModel::query()
        ->where('action', 'SETTLEMENT_REPRINTED')
        ->where('subject_type', 'staff_settlement')
        ->where('subject_id', $settlement->id)
        ->first();

    expect($log)->not->toBeNull();
});

it('includes applied fines only in settlement ticket', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();
    $appliedFineId = spaCreateFine(15, 'Multa aplicada');
    spaCreateFine(25, 'Multa no aplicada');

    spaMarkPaid($settlement->id, [$appliedFineId]);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->firstOrFail();

    expect($job->content_text)->toContain('Multa aplicada')
        ->and($job->content_text)->not->toContain('Multa no aplicada');
});

it('waiter settlement includes sales total commission percent and amount', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaWaiterSettlement();

    nightposResetApiAuth();

    $response = test()->getJson("/api/v1/settlements/{$settlement->id}", nightposOperationalHeaders(spaAdminToken()))
        ->assertOk();

    expect($response->json('data.settlement.waiter_sales_total'))->toBe('240.00')
        ->and($response->json('data.settlement.commission_percent'))->toBe('5.00')
        ->and($response->json('data.settlement.commission_amount'))->toBe('12.00');
});

it('waiter ticket includes venta garzon block with percent and commission', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaWaiterSettlement();

    spaMarkPaid($settlement->id);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->firstOrFail();

    $content = $job->content_text;

    expect($content)->toContain('VENTA GARZON')
        ->and($content)->toContain('240.00')
        ->and($content)->toContain('5%')
        ->and($content)->toContain('12.00');
});

it('girl ticket does not include venta garzon block', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaGirlSettlement();

    spaMarkPaid($settlement->id);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->firstOrFail();

    expect($job->content_text)->not->toContain('VENTA GARZON');
});

it('waiter fine reduces net without changing sales total on ticket', function () {
    spaChargeGirl(120);
    spaGenerate();
    $settlement = spaWaiterSettlement();
    $fineId = spaCreateWaiterFine(5, 'Retraso');

    spaMarkPaid($settlement->id, [$fineId]);

    $job = PrintJobModel::query()
        ->where('source_id', $settlement->id)
        ->where('type', PrintJobType::SettlementPayment->value)
        ->latest('id')
        ->firstOrFail();

    $content = $job->content_text;

    expect($content)->toContain('240.00')
        ->and($content)->toContain('12.00')
        ->and($content)->toContain('-5.00')
        ->and($content)->toContain('7.00');
});
