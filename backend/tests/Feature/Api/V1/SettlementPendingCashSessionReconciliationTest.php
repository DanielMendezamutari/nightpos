<?php

declare(strict_types=1);

use App\Application\StaffSettlement\Services\PendingCashSessionSettlementReconciler;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function reconcileContext(): array
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('tenant_id', $tenantId)->value('id');
    $cashierUserId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $girlUserId = (int) StaffProfileModel::query()->where('staff_role', 'GIRL')->value('user_id');

    $shiftA = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno A',
        'shift_type' => 'DAY',
        'business_date' => '2026-07-15',
        'starts_at' => '2026-07-15 09:00:00',
        'ends_at' => '2026-07-15 21:00:00',
        'status' => 'CLOSED',
        'opened_by_user_id' => $cashierUserId,
        'closed_by_user_id' => $cashierUserId,
        'opened_at' => '2026-07-15 10:00:00',
        'closed_at' => '2026-07-15 10:10:00',
    ]);

    $shiftB = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno B',
        'shift_type' => 'DAY',
        'business_date' => '2026-07-15',
        'starts_at' => '2026-07-15 09:00:00',
        'ends_at' => '2026-07-15 21:00:00',
        'status' => 'OPEN',
        'opened_by_user_id' => $cashierUserId,
        'opened_at' => '2026-07-15 10:10:00',
    ]);

    $session = CashSessionModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $shiftA->id,
        'opened_by_user_id' => $cashierUserId,
        'status' => 'OPEN',
        'opening_amount' => '0.00',
        'opened_at' => '2026-07-15 10:07:39',
    ]);

    return [
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'girl_user_id' => $girlUserId,
        'shift_a_id' => (int) $shiftA->id,
        'shift_b_id' => (int) $shiftB->id,
        'cash_session_id' => (int) $session->id,
    ];
}

it('consolida pendientes de misma caja/persona/tipo y preserva official_shift de items', function () {
    $ctx = reconcileContext();

    $settlementA = StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_a_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PENDING',
        'total_amount' => '360.00',
        'gross_amount' => '360.00',
        'adjustments_total' => '0.00',
        'net_amount' => '360.00',
    ]);

    $settlementB = StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_b_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PENDING',
        'total_amount' => '120.00',
        'gross_amount' => '120.00',
        'adjustments_total' => '0.00',
        'net_amount' => '120.00',
    ]);

    StaffSettlementItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'staff_settlement_id' => (int) $settlementA->id,
        'official_shift_id' => $ctx['shift_a_id'],
        'source_id' => 900013,
        'source_type' => 'GIRL_ROOM',
        'description' => 'Pieza',
        'base_amount' => '600.00',
        'amount' => '360.00',
    ]);

    StaffSettlementItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'staff_settlement_id' => (int) $settlementB->id,
        'official_shift_id' => $ctx['shift_b_id'],
        'source_id' => 900041,
        'source_type' => 'GIRL_BRACELET_ALLOCATION',
        'description' => 'Consumo',
        'base_amount' => '40.00',
        'amount' => '120.00',
    ]);

    $result = app(PendingCashSessionSettlementReconciler::class)->reconcile(
        tenantId: $ctx['tenant_id'],
        branchId: $ctx['branch_id'],
        cashSessionId: $ctx['cash_session_id'],
        dryRun: false,
    );

    $pending = StaffSettlementModel::query()
        ->where('tenant_id', $ctx['tenant_id'])
        ->where('branch_id', $ctx['branch_id'])
        ->where('cash_session_id', $ctx['cash_session_id'])
        ->where('staff_user_id', $ctx['girl_user_id'])
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->first();

    expect($result['merged_headers'])->toBe(1)
        ->and($pending)->not->toBeNull()
        ->and((string) $pending->total_amount)->toBe('480.00');

    $items = StaffSettlementItemModel::query()
        ->where('staff_settlement_id', (int) $pending->id)
        ->orderBy('id')
        ->get();

    expect($items)->toHaveCount(2)
        ->and($items->pluck('source_type')->all())->toContain('GIRL_ROOM', 'GIRL_BRACELET_ALLOCATION')
        ->and($items->pluck('official_shift_id')->filter()->unique()->sort()->values()->all())
        ->toBe([$ctx['shift_a_id'], $ctx['shift_b_id']]);
});

it('current-shift devuelve una sola fila consolidada y mantiene breakdown por fuente', function () {
    $ctx = reconcileContext();

    $settlementA = StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_a_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PENDING',
        'total_amount' => '360.00',
        'gross_amount' => '360.00',
        'adjustments_total' => '0.00',
        'net_amount' => '360.00',
    ]);

    $settlementB = StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_b_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PENDING',
        'total_amount' => '120.00',
        'gross_amount' => '120.00',
        'adjustments_total' => '0.00',
        'net_amount' => '120.00',
    ]);

    StaffSettlementItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'staff_settlement_id' => (int) $settlementA->id,
        'official_shift_id' => $ctx['shift_a_id'],
        'source_id' => 900013,
        'source_type' => 'GIRL_ROOM',
        'description' => 'Pieza',
        'base_amount' => '600.00',
        'amount' => '360.00',
    ]);

    StaffSettlementItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'staff_settlement_id' => (int) $settlementB->id,
        'official_shift_id' => $ctx['shift_b_id'],
        'source_id' => 900041,
        'source_type' => 'GIRL_BRACELET_ALLOCATION',
        'description' => 'Consumo',
        'base_amount' => '40.00',
        'amount' => '120.00',
    ]);

    app(PendingCashSessionSettlementReconciler::class)->reconcile(
        tenantId: $ctx['tenant_id'],
        branchId: $ctx['branch_id'],
        cashSessionId: $ctx['cash_session_id'],
        dryRun: false,
    );

    $cashier = nightposLoginPin('1234');
    $girlName = (string) UserModel::query()->whereKey($ctx['girl_user_id'])->value('name');

    $overview = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonCount(1, 'data.girls')
        ->assertJsonPath('data.summary.total_girls', '480.00')
        ->assertJsonPath('data.girls.0.staff_name', $girlName)
        ->assertJsonPath('data.girls.0.total_amount', '480.00')
        ->assertJsonPath('data.girls.0.pieces_total', '360.00')
        ->assertJsonPath('data.girls.0.bracelets_total', '120.00');

    $settlementId = (int) $overview->json('data.girls.0.id');

    test()->getJson("/api/v1/settlements/{$settlementId}", nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonCount(2, 'data.items')
        ->assertJsonPath('data.items.0.official_shift_id', $ctx['shift_a_id'])
        ->assertJsonPath('data.items.0.amount', '360.00')
        ->assertJsonPath('data.items.1.official_shift_id', $ctx['shift_b_id'])
        ->assertJsonPath('data.items.1.amount', '120.00');
});

it('no fusiona settlements PAID', function () {
    $ctx = reconcileContext();

    StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_a_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PAID',
        'total_amount' => '360.00',
        'gross_amount' => '360.00',
        'adjustments_total' => '0.00',
        'net_amount' => '360.00',
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_b_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'status' => 'PENDING',
        'total_amount' => '120.00',
        'gross_amount' => '120.00',
        'adjustments_total' => '0.00',
        'net_amount' => '120.00',
    ]);

    $result = app(PendingCashSessionSettlementReconciler::class)->reconcile(
        tenantId: $ctx['tenant_id'],
        branchId: $ctx['branch_id'],
        cashSessionId: $ctx['cash_session_id'],
        dryRun: false,
    );

    expect($result['merged_headers'])->toBe(0)
        ->and(StaffSettlementModel::query()->where('cash_session_id', $ctx['cash_session_id'])->where('status', 'PAID')->count())->toBe(1)
        ->and(StaffSettlementModel::query()->where('cash_session_id', $ctx['cash_session_id'])->where('status', 'PENDING')->count())->toBe(1);
});

it('es idempotente al reejecutar reconciliacion', function () {
    $ctx = reconcileContext();

    StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_a_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'status' => 'PENDING',
        'total_amount' => '10.00',
        'gross_amount' => '10.00',
        'adjustments_total' => '0.00',
        'net_amount' => '10.00',
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_b_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'staff_user_id' => $ctx['girl_user_id'],
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'status' => 'PENDING',
        'total_amount' => '5.00',
        'gross_amount' => '5.00',
        'adjustments_total' => '0.00',
        'net_amount' => '5.00',
    ]);

    $first = app(PendingCashSessionSettlementReconciler::class)->reconcile(
        tenantId: $ctx['tenant_id'],
        branchId: $ctx['branch_id'],
        cashSessionId: $ctx['cash_session_id'],
        dryRun: false,
    );

    $second = app(PendingCashSessionSettlementReconciler::class)->reconcile(
        tenantId: $ctx['tenant_id'],
        branchId: $ctx['branch_id'],
        cashSessionId: $ctx['cash_session_id'],
        dryRun: false,
    );

    expect($first['merged_headers'])->toBe(1)
        ->and($second['merged_headers'])->toBe(0);
});

it('consolida autosync de pieza y consumo en una sola liquidacion por cash_session', function () {
    $ctx = reconcileContext();
    $cashierUserId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $waiterUserId = (int) StaffProfileModel::query()
        ->where('staff_role', 'WAITER')
        ->value('user_id');

    $roomService = RoomServiceModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_a_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'order_id' => null,
        'girl_user_id' => $ctx['girl_user_id'],
        'unit_price' => '600.00',
        'total_amount' => '600.00',
        'payment_method' => 'CASH',
        'girl_percent' => '60.00',
        'gross_girl_amount' => '360.00',
        'cleaning_amount' => '0.00',
        'girl_amount' => '360.00',
        'house_amount' => '240.00',
        'registered_by_user_id' => $cashierUserId,
        'registered_at' => '2026-07-15 10:10:05',
        'duration_minutes' => 60,
        'status' => 'FINISHED',
    ]);

    $sale = SaleModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_b_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'order_id' => null,
        'sale_number' => 'V-TEST-480',
        'cashier_user_id' => $cashierUserId,
        'waiter_user_id' => $waiterUserId,
        'subtotal' => '240.00',
        'total' => '240.00',
        'payment_mode' => 'CASH',
        'status' => 'PAID',
        'paid_at' => '2026-07-15 10:10:50',
    ]);

    $saleItem = SaleItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_id' => (int) $sale->id,
        'product_id' => 1,
        'product_name_snapshot' => 'combo 3 corona',
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'unit_price_snapshot' => '240.00',
        'line_total' => '240.00',
        'girl_user_id' => null,
        'girl_amount_snapshot' => '120.00',
        'house_amount_snapshot' => '120.00',
        'waiter_commission_percent_snapshot' => '0.00',
        'waiter_commission_amount_snapshot' => '0.00',
    ]);

    $allocation = SaleItemAllocationModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_item_id' => (int) $saleItem->id,
        'girl_user_id' => $ctx['girl_user_id'],
        'units' => 3,
        'unit_amount_snapshot' => '40.00',
        'total_amount_snapshot' => '120.00',
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    $repo = app(StaffSettlementRepositoryInterface::class);
    $repo->syncFromRoomService($ctx['tenant_id'], $ctx['branch_id'], (int) $roomService->id);
    $repo->syncFromSale($ctx['tenant_id'], $ctx['branch_id'], (int) $sale->id);

    $pending = StaffSettlementModel::query()
        ->where('tenant_id', $ctx['tenant_id'])
        ->where('branch_id', $ctx['branch_id'])
        ->where('cash_session_id', $ctx['cash_session_id'])
        ->where('staff_user_id', $ctx['girl_user_id'])
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->get();

    expect($pending)->toHaveCount(1)
        ->and((string) $pending->first()->total_amount)->toBe('480.00');

    $items = StaffSettlementItemModel::query()
        ->where('staff_settlement_id', (int) $pending->first()->id)
        ->orderBy('id')
        ->get();

    expect($items)->toHaveCount(2)
        ->and($items->pluck('source_type')->all())->toContain('GIRL_ROOM', 'GIRL_BRACELET_ALLOCATION')
        ->and($items->pluck('source_id')->all())->toContain((int) $roomService->id, (int) $allocation->id);
});
