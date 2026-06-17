<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function tablesPartialAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function tablesPartialCashierToken(): string
{
    return nightposLoginPin('1234');
}

function tablesPartialWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function tablesPartialGirlAId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function tablesPartialGirlBId(): int
{
    return (int) UserModel::query()->where('username', 'chica2.demo')->value('id');
}

function tablesPartialWaiterId(): int
{
    return nightposDemoWaiterUserId();
}

function tablesPartialSetupTable(): array
{
    $headers = nightposOperationalHeaders(tablesPartialAdminToken());

    $areaId = (int) test()->postJson('/api/v1/service-areas', [
        'code' => 'PARTIAL-T',
        'name' => 'Salón Partial Tables',
        'area_type' => 'VIP',
    ], $headers)->assertCreated()->json('data.service_area.id');

    $tableId = (int) test()->postJson('/api/v1/service-tables', [
        'service_area_id' => $areaId,
        'code' => 'PT-01',
        'label' => 'Mesa Partial',
        'sort_order' => 1,
    ], $headers)->assertCreated()->json('data.service_table.id');

    test()->putJson('/api/v1/waiter-table-assignments/sync', [
        'waiter_user_id' => tablesPartialWaiterId(),
        'service_table_ids' => [$tableId],
    ], $headers)->assertOk();

    return ['table_id' => $tableId];
}

function tablesPartialGirlProductId(): int
{
    return nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => 80,
            'girl_amount' => 40,
            'house_amount' => 40,
        ],
    ]);
}

function tablesPartialComboProductId(int $braceletUnits = 6): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Combo Partial Tables',
        'product_type' => 'beverage',
        'unit' => 'combo',
        'status' => 'active',
        'settlement_behavior' => 'GIRL_BRACELET_ALLOCATION',
        'bracelet_units_per_line' => $braceletUnits,
        'requires_allocation' => true,
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 120,
        'girl_amount' => 60,
        'house_amount' => 60,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

/**
 * @return array{order_id: int, sale_id: int, sale_item_id: int}
 */
function tablesPartialOpenChargeGirl(
    int $tableId,
    int $girlUserId,
    ?int $productId = null,
): array {
    $productId ??= tablesPartialGirlProductId();
    $waiterToken = tablesPartialWaiterToken();
    $waiterHeaders = nightposOperationalHeaders($waiterToken);

    $orderId = (int) test()->postJson(
        "/api/v1/waiter/my-tables/{$tableId}/open",
        [],
        $waiterHeaders,
    )->assertCreated()->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlUserId,
    ], $waiterHeaders)->assertCreated();

    $cashierToken = tablesPartialCashierToken();
    nightposOpenCashSession($cashierToken, 500);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 80]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $sale = SaleModel::query()->where('order_id', $orderId)->firstOrFail();
    $saleItem = SaleItemModel::query()->where('sale_id', $sale->id)->firstOrFail();

    return [
        'order_id' => $orderId,
        'sale_id' => (int) $sale->id,
        'sale_item_id' => (int) $saleItem->id,
    ];
}

/**
 * @return array{order_id: int, sale_id: int, sale_item_id: int}
 */
function tablesPartialOpenChargeCombo(
    int $tableId,
    int $girlUserId,
    int $units = 6,
): array {
    $productId = tablesPartialComboProductId($units);
    $waiterToken = tablesPartialWaiterToken();
    $waiterHeaders = nightposOperationalHeaders($waiterToken);

    $orderId = (int) test()->postJson(
        "/api/v1/waiter/my-tables/{$tableId}/open",
        [],
        $waiterHeaders,
    )->assertCreated()->json('data.order.id');

    $itemId = (int) test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], $waiterHeaders)->assertCreated()->json('data.order.items.0.id');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [['girl_user_id' => $girlUserId, 'units' => $units]],
    ], $waiterHeaders)->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], $waiterHeaders)->assertOk();

    $cashierToken = tablesPartialCashierToken();
    nightposOpenCashSession($cashierToken, 500);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 120]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $sale = SaleModel::query()->where('order_id', $orderId)->firstOrFail();
    $saleItem = SaleItemModel::query()->where('sale_id', $sale->id)->firstOrFail();

    return [
        'order_id' => $orderId,
        'sale_id' => (int) $sale->id,
        'sale_item_id' => (int) $saleItem->id,
    ];
}

function tablesPartialGenerate(string $token): array
{
    nightposResetApiAuth();

    return test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated()
        ->json('data');
}

function tablesPartialMarkPaid(int $settlementId, string $token): void
{
    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertOk();
}

it('allows multiple settlement headers per shift staff type after migration', function () {
    $indexes = Schema::getIndexes('staff_settlements');
    $uniqueNames = collect($indexes)
        ->filter(fn (array $idx) => $idx['unique'] ?? false)
        ->pluck('name')
        ->all();

    expect($uniqueNames)->not->toContain('staff_settlements_shift_staff_type_unique');
});

it('waiter can open assigned table for partial settlements fixture', function () {
    $setup = tablesPartialSetupTable();

    test()->postJson(
        "/api/v1/waiter/my-tables/{$setup['table_id']}/open",
        [],
        nightposOperationalHeaders(tablesPartialWaiterToken()),
    )->assertCreated();
});

it('creates second girl cut via my-tables after first cut is paid same girl', function () {
    $setup = tablesPartialSetupTable();
    $girlId = tablesPartialGirlAId();

    $first = tablesPartialOpenChargeGirl($setup['table_id'], $girlId);
    tablesPartialGenerate(tablesPartialAdminToken());

    $firstSettlementId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->value('id');

    tablesPartialMarkPaid($firstSettlementId, tablesPartialAdminToken());

    $second = tablesPartialOpenChargeGirl($setup['table_id'], $girlId);
    $generate = tablesPartialGenerate(tablesPartialAdminToken());

    expect($generate['created_items'])->toBeGreaterThan(0);

    $secondSettlement = StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->first();

    expect($secondSettlement)->not->toBeNull()
        ->and($secondSettlement->id)->not->toBe($firstSettlementId)
        ->and(StaffSettlementItemModel::query()->where('staff_settlement_id', $secondSettlement->id)->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()
            ->where('sale_item_id', $second['sale_item_id'])
            ->where('source_type', 'GIRL_CONSUMPTION')
            ->exists())->toBeTrue()
        ->and(SaleItemModel::query()->find($second['sale_item_id'])?->girl_user_id)->toBe($girlId)
        ->and((float) SaleItemModel::query()->find($second['sale_item_id'])?->girl_amount_snapshot)->toBeGreaterThan(0);
});

it('creates pending settlement for another girl via my-tables after first girl paid', function () {
    $setup = tablesPartialSetupTable();
    $girlA = tablesPartialGirlAId();
    $girlB = tablesPartialGirlBId();

    tablesPartialOpenChargeGirl($setup['table_id'], $girlA);
    tablesPartialGenerate(tablesPartialAdminToken());

    $firstId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlA)
        ->where('settlement_type', 'GIRL')
        ->value('id');

    tablesPartialMarkPaid($firstId, tablesPartialAdminToken());

    tablesPartialOpenChargeGirl($setup['table_id'], $girlB);
    $generate = tablesPartialGenerate(tablesPartialAdminToken());

    expect($generate['created_items'])->toBeGreaterThan(0)
        ->and(StaffSettlementModel::query()
            ->where('staff_user_id', $girlB)
            ->where('settlement_type', 'GIRL')
            ->where('status', 'PENDING')
            ->exists())->toBeTrue();
});

it('creates second combo allocation cut via my-tables after first cut paid', function () {
    $setup = tablesPartialSetupTable();
    $girlId = tablesPartialGirlAId();

    tablesPartialOpenChargeCombo($setup['table_id'], $girlId);
    tablesPartialGenerate(tablesPartialAdminToken());

    $firstId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->value('id');

    tablesPartialMarkPaid($firstId, tablesPartialAdminToken());

    tablesPartialOpenChargeCombo($setup['table_id'], $girlId);
    $generate = tablesPartialGenerate(tablesPartialAdminToken());

    expect($generate['created_items'])->toBeGreaterThan(0)
        ->and(StaffSettlementModel::query()
            ->where('staff_user_id', $girlId)
            ->where('settlement_type', 'GIRL')
            ->where('status', 'PENDING')
            ->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'GIRL_BRACELET_ALLOCATION')->count())->toBe(2);
});

it('does not create girl settlement for solo cliente sale via my-tables', function () {
    $setup = tablesPartialSetupTable();
    $waiter = tablesPartialWaiterToken();
    $waiterHeaders = nightposOperationalHeaders($waiter);
    $productId = nightposSeedOrderProduct([
        ['sale_mode' => 'SOLO_CLIENTE', 'price' => 50, 'girl_amount' => 0, 'house_amount' => 50],
    ]);

    $orderId = (int) test()->postJson(
        "/api/v1/waiter/my-tables/{$setup['table_id']}/open",
        [],
        $waiterHeaders,
    )->assertCreated()->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], $waiterHeaders)->assertCreated();

    nightposOpenCashSession($cashierToken = tablesPartialCashierToken(), 500);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 25]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $saleItem = SaleItemModel::query()
        ->whereHas('sale', fn ($q) => $q->where('order_id', $orderId))
        ->firstOrFail();

    expect($saleItem->girl_user_id)->toBeNull();

    tablesPartialGenerate(tablesPartialAdminToken());

    expect(StaffSettlementItemModel::query()
        ->where('sale_item_id', $saleItem->id)
        ->where('source_type', 'GIRL_CONSUMPTION')
        ->exists())->toBeFalse();
});

it('does not duplicate settlement items on regenerate after my-tables flow', function () {
    $setup = tablesPartialSetupTable();

    tablesPartialOpenChargeGirl($setup['table_id'], tablesPartialGirlAId());
    tablesPartialGenerate(tablesPartialAdminToken());
    tablesPartialGenerate(tablesPartialAdminToken());

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(1);
});

it('exposes second pending cut to admin and cashier in same shift after my-tables sales', function () {
    $setup = tablesPartialSetupTable();
    $girlId = tablesPartialGirlAId();
    $admin = tablesPartialAdminToken();

    tablesPartialOpenChargeGirl($setup['table_id'], $girlId);
    tablesPartialGenerate($admin);
    tablesPartialMarkPaid((int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id'), $admin);

    tablesPartialOpenChargeGirl($setup['table_id'], $girlId);
    tablesPartialGenerate($admin);

    expect(StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('status', 'PENDING')
        ->count())->toBe(1);

    $adminGirls = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.girls');

    $cashier = tablesPartialCashierToken();
    nightposOpenCashSession($cashier, 0);

    $cashierGirls = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data.girls');

    $adminCuts = collect($adminGirls)->filter(fn ($g) => (int) ($g['staff_user_id'] ?? 0) === $girlId)->sortBy('cut_number')->values();
    $cashierCuts = collect($cashierGirls)->filter(fn ($g) => (int) ($g['staff_user_id'] ?? 0) === $girlId)->sortBy('cut_number')->values();

    expect($adminCuts)->toHaveCount(2)
        ->and($adminCuts[0]['status'])->toBe('PAID')
        ->and($adminCuts[1]['status'])->toBe('PENDING')
        ->and($adminCuts[1]['cut_number'])->toBe(2)
        ->and($cashierCuts)->toHaveCount(2)
        ->and($cashierCuts[1]['status'])->toBe('PENDING');
});

it('aligns sale official_shift_id with open shift after my-tables charge', function () {
    $setup = tablesPartialSetupTable();
    $shiftId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
        ->where('status', 'OPEN')
        ->value('id');

    $result = tablesPartialOpenChargeGirl($setup['table_id'], tablesPartialGirlAId());

    $sale = SaleModel::query()->find($result['sale_id']);
    $order = \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()->find($result['order_id']);

    expect($sale->official_shift_id)->toBe($shiftId)
        ->and($order->official_shift_id)->toBe($shiftId);
});
