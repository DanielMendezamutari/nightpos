<?php

declare(strict_types=1);

use App\Application\Cash\DTOs\SettlementSummaryDTO;
use App\Domain\Cash\Contracts\SettlementSummaryRepositoryInterface;
use App\Domain\Cash\Contracts\SummaryBuilders\SettlementSummaryBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSettlementSummaryRepository;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
    $this->app->bind(SettlementSummaryRepositoryInterface::class, EloquentSettlementSummaryRepository::class);
});

function settlementSummaryOpenSession(float $opening = 100): CashSessionModel
{
    $token = nightposLoginPin('1234');
    nightposOpenCashSession($token, $opening);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');

    return CashSessionModel::query()->findOrFail($sessionId);
}

function settlementSummaryBuild(
    CashSessionModel $session,
    ?int $cashSessionId = null,
    ?int $officialShiftId = null,
    ?int $tenantId = null,
    ?int $branchId = null,
): SettlementSummaryDTO {
    $builder = app(SettlementSummaryBuilder::class);

    return $builder->build(
        tenantId: $tenantId ?? (int) $session->tenant_id,
        branchId: $branchId ?? (int) $session->branch_id,
        cashSessionId: $cashSessionId,
        officialShiftId: $officialShiftId,
    );
}

function settlementSummaryInsertSettlement(CashSessionModel $session, array $overrides = []): StaffSettlementModel
{
    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $settlementType = (string) ($overrides['settlement_type'] ?? 'GIRL');
    $staffRole = (string) ($overrides['staff_role'] ?? $settlementType);
    $status = (string) ($overrides['status'] ?? 'PENDING');

    return StaffSettlementModel::query()->create(array_merge([
        'tenant_id' => (int) $session->tenant_id,
        'branch_id' => (int) $session->branch_id,
        'official_shift_id' => (int) $session->official_shift_id,
        'cash_session_id' => (int) $session->id,
        'staff_user_id' => $userId,
        'staff_role' => $staffRole,
        'settlement_type' => $settlementType,
        'compensation_mode' => null,
        'compensation_source' => null,
        'manual_amount_input' => null,
        'total_amount' => '0.00',
        'gross_amount' => '0.00',
        'adjustments_total' => '0.00',
        'net_amount' => '0.00',
        'status' => $status,
        'paid_by_user_id' => $status === 'PAID' ? $userId : null,
        'paid_at' => $status === 'PAID' ? now() : null,
        'payment_method' => $status === 'PAID' ? 'CASH' : null,
        'notes' => 'Settlement summary test row',
    ], $overrides));
}

function settlementSummaryInsertAdjustment(StaffSettlementModel $settlement, string $adjustmentType, float $amount): void
{
    StaffSettlementAdjustmentModel::query()->create([
        'tenant_id' => (int) $settlement->tenant_id,
        'branch_id' => (int) $settlement->branch_id,
        'staff_settlement_id' => (int) $settlement->id,
        'adjustment_type' => $adjustmentType,
        'amount' => number_format($amount, 2, '.', ''),
        'notes' => 'Adjustment summary test row',
    ]);
}

function settlementSummaryCreateShift(
    int $tenantId,
    int $branchId,
    string $name,
    ?\Illuminate\Support\Carbon $startsAt = null,
): OfficialShiftModel {
    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');
    $start = $startsAt ?? now()->startOfDay();

    return OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => $name,
        'shift_type' => 'DAY',
        'business_date' => $start->toDateString(),
        'starts_at' => $start,
        'ends_at' => $start->copy()->endOfDay(),
        'status' => 'OPEN',
        'opened_by_user_id' => $userId,
        'opened_at' => $start,
    ]);
}

function settlementSummaryCreateSession(int $tenantId, int $branchId, int $shiftId, float $opening = 100): CashSessionModel
{
    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    return CashSessionModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'opened_by_user_id' => $userId,
        'status' => 'OPEN',
        'opening_amount' => number_format($opening, 2, '.', ''),
        'opened_at' => now(),
    ]);
}

it('1) includes GIRL pending', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '100.00',
        'adjustments_total' => '-5.00',
        'net_amount' => '95.00',
        'status' => 'PENDING',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->girls['pending_count'])->toBe(1)
        ->and($summary->girls['pending_gross_amount'])->toBe('100.00')
        ->and($summary->girls['pending_adjustments_amount'])->toBe('-5.00')
        ->and($summary->girls['pending_net_amount'])->toBe('95.00');
});

it('2) includes GIRL paid', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '120.00',
        'adjustments_total' => '-10.00',
        'net_amount' => '110.00',
        'status' => 'PAID',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->girls['paid_count'])->toBe(1)
        ->and($summary->girls['paid_gross_amount'])->toBe('120.00')
        ->and($summary->girls['paid_adjustments_amount'])->toBe('-10.00')
        ->and($summary->girls['paid_net_amount'])->toBe('110.00');
});

it('3) includes WAITER automatic pending', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'compensation_mode' => 'AUTO_PERCENT',
        'gross_amount' => '40.00',
        'adjustments_total' => '0.00',
        'net_amount' => '40.00',
        'status' => 'PENDING',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->waiters['pending_count'])->toBe(1)
        ->and($summary->waiters['pending_net_amount'])->toBe('40.00')
        ->and($summary->manual_compensation['waiter_manual_pending_count'])->toBe(0);
});

it('4) includes WAITER automatic paid', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'compensation_mode' => 'AUTO_PERCENT',
        'gross_amount' => '55.00',
        'adjustments_total' => '-5.00',
        'net_amount' => '50.00',
        'status' => 'PAID',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->waiters['paid_count'])->toBe(1)
        ->and($summary->waiters['paid_net_amount'])->toBe('50.00')
        ->and($summary->manual_compensation['waiter_manual_paid_count'])->toBe(0);
});

it('5) includes WAITER manual commission zero pending', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'compensation_mode' => 'MANUAL',
        'compensation_source' => 'REQUIRES_MANUAL_INPUT',
        'manual_amount_input' => '0.00',
        'gross_amount' => '0.00',
        'adjustments_total' => '0.00',
        'net_amount' => '0.00',
        'status' => 'PENDING',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->waiters['pending_count'])->toBe(1)
        ->and($summary->manual_compensation['waiter_manual_pending_count'])->toBe(1)
        ->and($summary->manual_compensation['waiter_manual_pending_amount'])->toBe('0.00');
});

it('6) includes WAITER manual paid', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'compensation_mode' => 'MANUAL',
        'compensation_source' => 'REQUIRES_MANUAL_INPUT',
        'manual_amount_input' => '18.50',
        'gross_amount' => '18.50',
        'adjustments_total' => '0.00',
        'net_amount' => '18.50',
        'status' => 'PAID',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->waiters['paid_count'])->toBe(1)
        ->and($summary->manual_compensation['waiter_manual_paid_count'])->toBe(1)
        ->and($summary->manual_compensation['waiter_manual_paid_amount'])->toBe('18.50');
});

it('7) includes CLEANING pending', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'CLEANING',
        'gross_amount' => '22.00',
        'adjustments_total' => '0.00',
        'net_amount' => '22.00',
        'status' => 'PENDING',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->cleaning['pending_count'])->toBe(1)
        ->and($summary->cleaning['pending_net_amount'])->toBe('22.00');
});

it('8) includes CLEANING paid', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'CLEANING',
        'gross_amount' => '30.00',
        'adjustments_total' => '-2.00',
        'net_amount' => '28.00',
        'status' => 'PAID',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->cleaning['paid_count'])->toBe(1)
        ->and($summary->cleaning['paid_adjustments_amount'])->toBe('-2.00')
        ->and($summary->cleaning['paid_net_amount'])->toBe('28.00');
});

it('9) excludes CANCELLED from pending and paid', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '15.00',
        'net_amount' => '15.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'gross_amount' => '999.00',
        'net_amount' => '999.00',
        'status' => 'CANCELLED',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->totals['pending_total_count'])->toBe(1)
        ->and($summary->totals['paid_total_count'])->toBe(0)
        ->and($summary->totals['pending_total_net'])->toBe('15.00');
});

it('10) includes fines in adjustments breakdown', function () {
    $session = settlementSummaryOpenSession();
    $settlement = settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '50.00',
        'adjustments_total' => '-5.00',
        'net_amount' => '45.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertAdjustment($settlement, 'STAFF_FINE', -5.00);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->adjustments_breakdown['manual_fines'])->toBe('-5.00');
});

it('11) includes manual discount in adjustments breakdown', function () {
    $session = settlementSummaryOpenSession();
    $settlement = settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '80.00',
        'adjustments_total' => '-8.00',
        'net_amount' => '72.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertAdjustment($settlement, 'MANUAL_DISCOUNT', -8.00);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->adjustments_breakdown['manual_discounts'])->toBe('-8.00');
});

it('12) includes GIRL cleaning deduction in adjustments breakdown', function () {
    $session = settlementSummaryOpenSession();
    $settlement = settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '90.00',
        'adjustments_total' => '-12.00',
        'net_amount' => '78.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertAdjustment($settlement, 'CLEANING_DEDUCTION', -12.00);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->adjustments_breakdown['cleaning_deduction'])->toBe('-12.00');
});

it('13) does not generate cleaning deduction when manual cleaning is zero', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '70.00',
        'adjustments_total' => '0.00',
        'net_amount' => '70.00',
        'status' => 'PENDING',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->adjustments_breakdown['cleaning_deduction'])->toBe('0.00');
});

it('14) applies scope by cash session', function () {
    $sessionA = settlementSummaryOpenSession();

    $shiftB = settlementSummaryCreateShift((int) $sessionA->tenant_id, (int) $sessionA->branch_id, 'Shift B Scope');
    $sessionB = settlementSummaryCreateSession((int) $sessionA->tenant_id, (int) $sessionA->branch_id, (int) $shiftB->id, 120);

    settlementSummaryInsertSettlement($sessionA, [
        'settlement_type' => 'GIRL',
        'net_amount' => '20.00',
        'gross_amount' => '20.00',
    ]);

    settlementSummaryInsertSettlement($sessionB, [
        'settlement_type' => 'GIRL',
        'net_amount' => '999.00',
        'gross_amount' => '999.00',
    ]);

    $summary = settlementSummaryBuild($sessionA, cashSessionId: (int) $sessionA->id);

    expect($summary->totals['pending_total_count'])->toBe(1)
        ->and($summary->totals['pending_total_net'])->toBe('20.00');
});

it('15) applies scope by official shift', function () {
    $sessionA = settlementSummaryOpenSession();

    $shiftB = settlementSummaryCreateShift((int) $sessionA->tenant_id, (int) $sessionA->branch_id, 'Shift B By Shift');
    $sessionB = settlementSummaryCreateSession((int) $sessionA->tenant_id, (int) $sessionA->branch_id, (int) $shiftB->id, 120);

    settlementSummaryInsertSettlement($sessionA, [
        'settlement_type' => 'WAITER',
        'net_amount' => '33.00',
        'gross_amount' => '33.00',
    ]);

    settlementSummaryInsertSettlement($sessionB, [
        'settlement_type' => 'WAITER',
        'net_amount' => '444.00',
        'gross_amount' => '444.00',
    ]);

    $summary = settlementSummaryBuild($sessionA, officialShiftId: (int) $sessionA->official_shift_id);

    expect($summary->totals['pending_total_count'])->toBe(1)
        ->and($summary->totals['pending_total_net'])->toBe('33.00');
});

it('16) cash session scope includes settlements that cross official shifts', function () {
    $session = settlementSummaryOpenSession();

    $otherShift = settlementSummaryCreateShift((int) $session->tenant_id, (int) $session->branch_id, 'Cross Shift Target', now()->addDay()->startOfDay());

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'official_shift_id' => (int) $session->official_shift_id,
        'cash_session_id' => (int) $session->id,
        'net_amount' => '10.00',
        'gross_amount' => '10.00',
    ]);

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'official_shift_id' => (int) $otherShift->id,
        'cash_session_id' => (int) $session->id,
        'net_amount' => '15.00',
        'gross_amount' => '15.00',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->totals['pending_total_count'])->toBe(2)
        ->and($summary->totals['pending_total_net'])->toBe('25.00');
});

it('17) keeps tenant isolated', function () {
    $session = settlementSummaryOpenSession();

    $tenant = TenantModel::query()->create([
        'name' => 'Tenant Aislado Settlement',
        'slug' => 'tenant-aislado-settlement',
        'status' => 'active',
    ]);

    $branch = BranchModel::query()->create([
        'tenant_id' => (int) $tenant->id,
        'name' => 'Branch Aislada Settlement',
        'code' => 'TAS',
        'status' => 'active',
    ]);

    $shift = settlementSummaryCreateShift((int) $tenant->id, (int) $branch->id, 'Shift Tenant Isolated');
    $foreignSession = settlementSummaryCreateSession((int) $tenant->id, (int) $branch->id, (int) $shift->id, 100);

    settlementSummaryInsertSettlement($foreignSession, [
        'settlement_type' => 'CLEANING',
        'net_amount' => '500.00',
        'gross_amount' => '500.00',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->totals['pending_total_count'])->toBe(0)
        ->and($summary->totals['pending_total_net'])->toBe('0.00');
});

it('18) keeps branch isolated', function () {
    $session = settlementSummaryOpenSession();

    $branch = BranchModel::query()->create([
        'tenant_id' => (int) $session->tenant_id,
        'name' => 'Branch Aislada Scope',
        'code' => 'BAS',
        'status' => 'active',
    ]);

    $shift = settlementSummaryCreateShift((int) $session->tenant_id, (int) $branch->id, 'Shift Branch Isolated');
    $foreignSession = settlementSummaryCreateSession((int) $session->tenant_id, (int) $branch->id, (int) $shift->id, 100);

    settlementSummaryInsertSettlement($foreignSession, [
        'settlement_type' => 'GIRL',
        'net_amount' => '700.00',
        'gross_amount' => '700.00',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->totals['pending_total_count'])->toBe(0)
        ->and($summary->totals['pending_total_net'])->toBe('0.00');
});

it('19) does not duplicate settlements when adjustments exist', function () {
    $session = settlementSummaryOpenSession();

    $first = settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '40.00',
        'adjustments_total' => '-2.00',
        'net_amount' => '38.00',
    ]);
    $second = settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'gross_amount' => '60.00',
        'adjustments_total' => '-3.00',
        'net_amount' => '57.00',
    ]);

    settlementSummaryInsertAdjustment($first, 'MANUAL_DISCOUNT', -2.00);
    settlementSummaryInsertAdjustment($second, 'STAFF_FINE', -3.00);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    expect($summary->totals['pending_total_count'])->toBe(2)
        ->and($summary->totals['pending_total_gross'])->toBe('100.00')
        ->and($summary->totals['pending_total_net'])->toBe('95.00');
});

it('20) keeps totals equal to per-type sums', function () {
    $session = settlementSummaryOpenSession();

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'GIRL',
        'gross_amount' => '50.00',
        'adjustments_total' => '-5.00',
        'net_amount' => '45.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'WAITER',
        'gross_amount' => '40.00',
        'adjustments_total' => '0.00',
        'net_amount' => '40.00',
        'status' => 'PENDING',
    ]);

    settlementSummaryInsertSettlement($session, [
        'settlement_type' => 'CLEANING',
        'gross_amount' => '30.00',
        'adjustments_total' => '-2.00',
        'net_amount' => '28.00',
        'status' => 'PAID',
    ]);

    $summary = settlementSummaryBuild($session, cashSessionId: (int) $session->id);

    $pendingByRole = (float) $summary->girls['pending_net_amount']
        + (float) $summary->waiters['pending_net_amount']
        + (float) $summary->cleaning['pending_net_amount'];

    $paidByRole = (float) $summary->girls['paid_net_amount']
        + (float) $summary->waiters['paid_net_amount']
        + (float) $summary->cleaning['paid_net_amount'];

    expect(number_format($pendingByRole, 2, '.', ''))->toBe($summary->totals['pending_total_net'])
        ->and(number_format($paidByRole, 2, '.', ''))->toBe($summary->totals['paid_total_net']);
});
