<?php

declare(strict_types=1);

use App\Application\Cash\Services\CashMovementTaxonomyBackfillService;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function cmtCashierToken(): string
{
    return nightposLoginPin('1234');
}

function cmtAdminToken(): string
{
    return nightposLoginPin('2468');
}

function cmtOpenCash(string $token): int
{
    nightposOpenCashSession($token, 100);

    return (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');
}

function cmtLatestMovement(): CashMovementModel
{
    return CashMovementModel::query()->latest('id')->firstOrFail();
}

function cmtReasonId(string $name): int
{
    $id = (int) CashMovementReasonModel::query()->where('name', $name)->value('id');
    expect($id)->toBeGreaterThan(0);

    return $id;
}

function cmtDirectSaleSeedProduct(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'Producto taxonomy',
        'product_type' => 'food',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 10,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

function cmtDirectSalePayload(int $productId): array
{
    return [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 10],
        ],
    ];
}

function cmtPendingSettlementId(string $type): int
{
    $id = (int) StaffSettlementModel::query()
        ->where('settlement_type', $type)
        ->where('status', 'PENDING')
        ->latest('id')
        ->value('id');

    expect($id)->toBeGreaterThan(0);

    return $id;
}

it('1. cobro de comanda se clasifica como SALE / SALE_COLLECTION', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $orderId = nightposCreateOrderWithItem($token, [
        'table_label' => 'Taxonomy table',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('SALE')
        ->and($movement->movement_category)->toBe('SALE_COLLECTION')
        ->and($movement->source_type)->toBe('SALE')
        ->and($movement->source_id)->not->toBeNull();
});

it('2. venta directa se clasifica como SALE / DIRECT_SALE_COLLECTION', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    $productId = cmtDirectSaleSeedProduct();

    test()->postJson('/api/v1/direct-sales', cmtDirectSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('SALE')
        ->and($movement->movement_category)->toBe('DIRECT_SALE_COLLECTION')
        ->and($movement->source_type)->toBe('SALE');
});

it('3. pago chica se clasifica como SETTLEMENT / SETTLEMENT_GIRL_PAYMENT', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 30,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(cmtAdminToken()))
        ->assertCreated();

    $settlementId = cmtPendingSettlementId('GIRL');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertOk();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('SETTLEMENT')
        ->and($movement->movement_category)->toBe('SETTLEMENT_GIRL_PAYMENT')
        ->and($movement->source_type)->toBe('STAFF_SETTLEMENT');
});

it('4. pago garzón se clasifica como SETTLEMENT / SETTLEMENT_WAITER_PAYMENT', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $orderId = nightposCreateOrderWithItem($token, [
        'table_label' => 'Waiter settlement',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(cmtAdminToken()))
        ->assertCreated();

    $settlementId = cmtPendingSettlementId('WAITER');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertOk();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('SETTLEMENT')
        ->and($movement->movement_category)->toBe('SETTLEMENT_WAITER_PAYMENT');
});

it('5. pago limpieza se clasifica como SETTLEMENT / SETTLEMENT_CLEANING_PAYMENT', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    $roomId = (int) test()->postJson('/api/v1/rooms', [
        'code' => 'TX1',
        'name' => 'Room TX1',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders(cmtAdminToken()))->assertCreated()->json('data.room.id');

    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 80,
    ]), nightposOperationalHeaders($token))->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(nightposLoginPin('3333')))->assertOk();
    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(nightposLoginPin('3333')))->assertOk();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(cmtAdminToken()))
        ->assertCreated();

    $settlementId = cmtPendingSettlementId('CLEANING');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertOk();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('SETTLEMENT')
        ->and($movement->movement_category)->toBe('SETTLEMENT_CLEANING_PAYMENT');
});

it('6. ingreso manual se clasifica como MANUAL / MANUAL_INCOME', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 100,
        'cash_movement_reason_id' => cmtReasonId('Otro ingreso'),
        'payment_method' => 'CASH',
        'notes' => 'Ingreso libre',
    ], nightposOperationalHeaders($token))->assertCreated();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('MANUAL')
        ->and($movement->movement_category)->toBe('MANUAL_INCOME');
});

it('7. gasto operativo se clasifica como EXPENSE / OPERATING_EXPENSE', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 50,
        'cash_movement_reason_id' => cmtReasonId('Pago cajera'),
        'payment_method' => 'CASH',
        'notes' => 'Arreglo urgente',
    ], nightposOperationalHeaders($token))->assertCreated();

    $movement = cmtLatestMovement();

    expect($movement->movement_family)->toBe('EXPENSE')
        ->and($movement->movement_category)->toBe('OPERATING_EXPENSE');
});

it('8. backfill usa OTHER_INCOME y OTHER_EXPENSE como fallback explícito', function () {
    $token = cmtCashierToken();
    $sessionId = cmtOpenCash($token);

    $cashierUserId = (int) CashSessionModel::query()->whereKey($sessionId)->value('opened_by_user_id');

    CashMovementModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => 1,
        'cash_session_id' => $sessionId,
        'movement_type' => 'INCOME',
        'amount' => '10.00',
        'description' => 'Ingreso desconocido legado',
        'payment_method' => 'CASH',
        'created_by_user_id' => $cashierUserId,
        'created_at' => now(),
        'movement_family' => null,
        'movement_category' => null,
    ]);

    CashMovementModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => 1,
        'cash_session_id' => $sessionId,
        'movement_type' => 'EXPENSE',
        'amount' => '15.00',
        'description' => 'Egreso desconocido legado',
        'payment_method' => 'CASH',
        'created_by_user_id' => $cashierUserId,
        'created_at' => now(),
        'movement_family' => null,
        'movement_category' => null,
    ]);

    $result = app(CashMovementTaxonomyBackfillService::class)->backfill(1, 1);

    expect($result['rows_updated'])->toBeGreaterThanOrEqual(2)
        ->and(CashMovementModel::query()->where('description', 'Ingreso desconocido legado')->value('movement_category'))->toBe('OTHER_INCOME')
        ->and(CashMovementModel::query()->where('description', 'Egreso desconocido legado')->value('movement_category'))->toBe('OTHER_EXPENSE');
});

it('9. backfill no pierde registros y puede aislar tenant/branch', function () {
    $token = cmtCashierToken();
    $sessionId = cmtOpenCash($token);

    $cashierUserId = (int) CashSessionModel::query()->whereKey($sessionId)->value('opened_by_user_id');

    $before = CashMovementModel::query()->count();

    CashMovementModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => 1,
        'cash_session_id' => $sessionId,
        'movement_type' => 'EXPENSE',
        'amount' => '20.00',
        'description' => 'Legacy current branch',
        'payment_method' => 'CASH',
        'created_by_user_id' => $cashierUserId,
        'created_at' => now(),
        'movement_family' => null,
        'movement_category' => null,
    ]);

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro tenant',
        'slug' => 'otro-tenant',
        'status' => 'active',
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Otra sucursal',
        'code' => 'OTRA',
        'address' => null,
        'status' => 'active',
    ]);

    CashMovementModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'cash_session_id' => $sessionId,
        'movement_type' => 'EXPENSE',
        'amount' => '30.00',
        'description' => 'Legacy other tenant',
        'payment_method' => 'CASH',
        'created_by_user_id' => $cashierUserId,
        'created_at' => now(),
        'movement_family' => null,
        'movement_category' => null,
    ]);

    app(CashMovementTaxonomyBackfillService::class)->backfill(1, 1);

    expect(CashMovementModel::query()->count())->toBe($before + 2)
        ->and(CashMovementModel::query()->where('description', 'Legacy current branch')->value('movement_category'))->not->toBeNull()
        ->and(CashMovementModel::query()->where('description', 'Legacy other tenant')->value('movement_category'))->toBeNull();
});

it('10. endpoints mantienen compatibilidad y exponen family/category', function () {
    $token = cmtCashierToken();
    $sessionId = cmtOpenCash($token);

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 100,
        'cash_movement_reason_id' => cmtReasonId('Otro ingreso'),
        'payment_method' => 'CASH',
        'notes' => 'Payload compatibility',
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.session.movements.0.movement_family', 'MANUAL')
        ->assertJsonPath('data.session.movements.0.movement_category', 'MANUAL_INCOME')
        ->assertJsonStructure(['data' => ['session' => ['movements' => [['description', 'payment_method', 'movement_family', 'movement_category']]]]]);

    test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}", nightposOperationalHeaders(cmtAdminToken()))
        ->assertOk()
        ->assertJsonPath('data.movements.0.movement_family', 'MANUAL')
        ->assertJsonPath('data.movements.0.movement_category', 'MANUAL_INCOME');
});

it('11. expected cash no cambia respecto al cálculo actual', function () {
    $token = cmtCashierToken();
    cmtOpenCash($token);

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 100,
        'cash_movement_reason_id' => cmtReasonId('Otro ingreso'),
        'payment_method' => 'CASH',
        'notes' => 'Ingreso prueba',
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 40,
        'cash_movement_reason_id' => cmtReasonId('Pago cajera'),
        'payment_method' => 'CASH',
        'notes' => 'Gasto prueba',
    ], nightposOperationalHeaders($token))->assertCreated();

    $financial = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.financial_summary');

    expect($financial['expected_cash'])->toBe('160.00');
});