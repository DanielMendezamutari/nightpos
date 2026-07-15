<?php

declare(strict_types=1);

use App\Application\StaffSettlement\UseCases\SyncSettlementsFromSaleUseCase;
use App\Application\StaffSettlement\UseCases\SyncSettlementsFromRoomServiceUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function autoSyncCancelSentToBarOrders(): void
{
    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    if ($shiftId <= 0) {
        return;
    }

    \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()
        ->where('official_shift_id', $shiftId)
        ->where('status', 'SENT_TO_BAR')
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);
}

function autoSyncCashierToken(): string
{
    return nightposLoginPin('1234');
}

function autoSyncAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function autoSyncChargeOrderWithMode(string $cashierToken, string $saleMode = 'CON_ACOMPANANTE'): int
{
    $extraPrices = $saleMode === 'CON_ACOMPANANTE'
        ? [['sale_mode' => 'CON_ACOMPANANTE', 'price' => 40]]
        : [];

    $productId = nightposSeedOrderProduct($extraPrices);

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa autosync',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($cashierToken));

    $order->assertCreated();
    $orderId = (int) $order->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => $saleMode,
        'quantity' => 1,
        'girl_user_id' => $saleMode === 'CON_ACOMPANANTE' ? (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()->where('username', 'chica.centro')->value('id') : null,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($cashierToken))->assertOk();

    $orderDetail = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($cashierToken))
        ->assertOk();

    $amount = (float) ($orderDetail->json('data.order.total') ?? 0);

    $charge = test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $amount]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    return (int) $charge->json('data.sale.id');
}

/**
 * @param  list<array{method:string, amount:float|int}>  $payments
 * @return array{sale_id:int, sale_total:string}
 */
function autoSyncChargeOrderWithPayments(string $cashierToken, array $payments, string $saleMode = 'SOLO_CLIENTE'): array
{
    $extraPrices = $saleMode === 'CON_ACOMPANANTE'
        ? [['sale_mode' => 'CON_ACOMPANANTE', 'price' => 40]]
        : [];

    $productId = nightposSeedOrderProduct($extraPrices);

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa autosync pagos',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($cashierToken));

    $order->assertCreated();
    $orderId = (int) $order->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => $saleMode,
        'quantity' => 1,
        'girl_user_id' => $saleMode === 'CON_ACOMPANANTE'
            ? (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()->where('username', 'chica.centro')->value('id')
            : null,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($cashierToken))->assertOk();

    $orderDetail = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($cashierToken))
        ->assertOk();

    $expectedTotal = (float) ($orderDetail->json('data.order.total') ?? 0);

    if ($payments === []) {
        $payments = [['method' => 'CASH', 'amount' => $expectedTotal]];
    }

    $plannedTotal = collect($payments)->sum(static fn (array $payment) => (float) ($payment['amount'] ?? 0));
    $difference = $expectedTotal - $plannedTotal;

    if (abs($difference) > 0.0001) {
        $last = count($payments) - 1;
        $payments[$last]['amount'] = (float) ($payments[$last]['amount'] ?? 0) + $difference;
    }

    $charge = test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => $payments,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    return [
        'sale_id' => (int) $charge->json('data.sale.id'),
        'sale_total' => (string) $charge->json('data.sale.total'),
    ];
}

/**
 * @return array<string, mixed>|null
 */
function autoSyncCurrentWaiterOverview(string $token, int $waiterUserId): ?array
{
    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($token))
        ->assertOk();

    $rows = collect($response->json('data.waiters') ?? []);

    $row = $rows->first(fn ($item) => (int) ($item['staff_user_id'] ?? 0) === $waiterUserId);

    return is_array($row) ? $row : null;
}

it('autosincroniza liquidaciones al cobrar una comanda con chica sin boton manual', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    autoSyncChargeOrderWithMode($token, 'CON_ACOMPANANTE');

    $overview = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($token))
        ->assertOk();

    expect((bool) $overview->json('data.auto_sync_enabled'))->toBeTrue()
        ->and($overview->json('data.sync_status'))->toBeString()
        ->and((int) ($overview->json('data.pending_sources_count') ?? 0))->toBeGreaterThanOrEqual(0)
        ->and((int) StaffSettlementModel::query()->where('status', 'PENDING')->count())->toBeGreaterThan(0)
        ->and($overview->json('data.settlements'))->not->toBeEmpty();
});

it('autosincroniza liquidaciones en venta directa', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $productId = nightposSeedOrderProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 35],
    ]);

    $girlId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

    $direct = test()->postJson('/api/v1/direct-sales', [
        'items' => [[
            'product_id' => $productId,
            'sale_mode' => 'CON_ACOMPANANTE',
            'quantity' => 1,
            'girl_user_id' => $girlId,
        ]],
        'payments' => [[
            'method' => 'CASH',
            'amount' => 35,
        ]],
    ], nightposOperationalHeaders($token))->assertCreated();

    $overview = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($token))
        ->assertOk();

    expect((int) $direct->json('data.sale.id'))->toBeGreaterThan(0)
        ->and((bool) $overview->json('data.auto_sync_enabled'))->toBeTrue()
        ->and($overview->json('data.sync_status'))->toBeString();
});

it('garzon con porcentaje 0 queda en compensacion manual pendiente', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $waiterId = nightposDemoWaiterUserId();

    StaffProfileModel::query()
        ->where('user_id', $waiterId)
        ->update(['waiter_commission_percent' => 0]);

    autoSyncChargeOrderWithMode($token, 'SOLO_CLIENTE');

    $waiterSettlement = StaffSettlementModel::query()
        ->where('staff_user_id', $waiterId)
        ->where('settlement_type', 'WAITER')
        ->latest('id')
        ->first();

    expect($waiterSettlement)->not->toBeNull()
        ->and((string) $waiterSettlement->compensation_mode)->toBe('MANUAL')
        ->and((string) $waiterSettlement->compensation_source)->toBe('REQUIRES_MANUAL_INPUT');
});

it('garzon con cuatro ventas reporta sales_count y total vendido correcto', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $totals = [];

    for ($i = 0; $i < 4; $i++) {
        $charge = autoSyncChargeOrderWithPayments($token, [['method' => 'CASH', 'amount' => 50]], 'SOLO_CLIENTE');
        $totals[] = (float) $charge['sale_total'];
    }

    $waiterId = nightposDemoWaiterUserId();
    $waiter = autoSyncCurrentWaiterOverview($token, $waiterId);

    expect($waiter)->not->toBeNull()
        ->and((int) ($waiter['sales_count'] ?? 0))->toBe(4)
        ->and((string) ($waiter['sales_total_amount'] ?? '0.00'))->toBe(number_format(array_sum($totals), 2, '.', ''));
});

it('pago mixto no duplica total vendido del garzon', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $productId = nightposSeedOrderProduct();

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa autosync mixto',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))->assertCreated();

    $orderId = (int) $order->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($token))->assertOk();

    $orderDetail = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($token))
        ->assertOk();

    $total = (float) ($orderDetail->json('data.order.total') ?? 0);
    $cash = round($total / 2, 2);
    $qr = round($total - $cash, 2);

    $chargeResponse = test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [
            ['method' => 'CASH', 'amount' => $cash],
            ['method' => 'QR', 'amount' => $qr],
        ],
    ], nightposOperationalHeaders($token))->assertCreated();

    $charge = [
        'sale_id' => (int) $chargeResponse->json('data.sale.id'),
        'sale_total' => (string) $chargeResponse->json('data.sale.total'),
    ];

    $waiter = autoSyncCurrentWaiterOverview($token, nightposDemoWaiterUserId());

    expect($waiter)->not->toBeNull()
        ->and((int) ($waiter['sales_count'] ?? 0))->toBe(1)
        ->and((string) ($waiter['sales_total_amount'] ?? '0.00'))->toBe((string) $charge['sale_total']);
});

it('garzon con porcentaje 0 muestra total vendido en overview', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $waiterId = nightposDemoWaiterUserId();

    StaffProfileModel::query()
        ->where('user_id', $waiterId)
        ->update(['waiter_commission_percent' => 0]);

    autoSyncChargeOrderWithPayments($token, [['method' => 'CASH', 'amount' => 50]], 'SOLO_CLIENTE');

    $waiter = autoSyncCurrentWaiterOverview($token, $waiterId);

    expect($waiter)->not->toBeNull()
        ->and((string) ($waiter['compensation_mode'] ?? ''))->toBe('MANUAL')
        ->and((int) ($waiter['sales_count'] ?? 0))->toBe(1)
        ->and((float) ($waiter['sales_total_amount'] ?? 0))->toBeGreaterThan(0);
});

it('garzon automatico muestra total vendido y comision', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    autoSyncChargeOrderWithPayments($token, [['method' => 'CASH', 'amount' => 50]], 'SOLO_CLIENTE');

    $waiter = autoSyncCurrentWaiterOverview($token, nightposDemoWaiterUserId());

    expect($waiter)->not->toBeNull()
        ->and((string) ($waiter['compensation_mode'] ?? ''))->toBe('AUTO_PERCENT')
        ->and((float) ($waiter['sales_total_amount'] ?? 0))->toBeGreaterThan(0)
        ->and((float) ($waiter['total_amount'] ?? 0))->toBeGreaterThan(0);
});

it('venta cancelada no entra en sales_count ni total vendido', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $charge = autoSyncChargeOrderWithPayments($token, [['method' => 'CASH', 'amount' => 50]], 'SOLO_CLIENTE');

    SaleModel::query()->where('id', $charge['sale_id'])->update([
        'status' => 'CANCELLED',
    ]);

    $waiter = autoSyncCurrentWaiterOverview($token, nightposDemoWaiterUserId());

    expect($waiter)->not->toBeNull()
        ->and((int) ($waiter['sales_count'] ?? -1))->toBe(0)
        ->and((string) ($waiter['sales_total_amount'] ?? 'x'))->toBe('0.00');
});

it('venta de otra sucursal no entra en total vendido del garzon', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    autoSyncChargeOrderWithPayments($token, [['method' => 'CASH', 'amount' => 50]], 'SOLO_CLIENTE');
    $baselineWaiter = autoSyncCurrentWaiterOverview($token, nightposDemoWaiterUserId());
    $baselineTotal = (string) ($baselineWaiter['sales_total_amount'] ?? '0.00');

    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $currentBranchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Externa Autosync',
        'code' => 'EXT'.uniqid(),
        'address' => 'N/A',
        'status' => 'active',
    ]);

    $openShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $cashSessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->json('data.session.id');

    SaleModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => (int) $otherBranch->id,
        'official_shift_id' => $openShiftId,
        'cash_session_id' => $cashSessionId,
        'order_id' => null,
        'sale_number' => 'V-EXT-0001',
        'cashier_user_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()->where('username', 'cajero.demo')->value('id'),
        'waiter_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => '999.00',
        'total' => '999.00',
        'currency' => 'BOB',
        'payment_mode' => 'CASH',
        'status' => 'PAID',
        'paid_at' => now(),
    ]);

    $waiter = autoSyncCurrentWaiterOverview($token, nightposDemoWaiterUserId());

    expect($waiter)->not->toBeNull()
        ->and((int) ($waiter['branch_id'] ?? $currentBranchId))->toBe($currentBranchId)
        ->and((string) ($waiter['sales_total_amount'] ?? '0.00'))->toBe($baselineTotal);
});

it('sync por venta es idempotente y no duplica items', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    $saleId = autoSyncChargeOrderWithMode($token, 'CON_ACOMPANANTE');

    $sync = app(SyncSettlementsFromSaleUseCase::class);

    $first = $sync->execute((object) [
        'tenantId' => (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id'),
        'branchId' => (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'saleId' => $saleId,
    ]);

    $second = $sync->execute((object) [
        'tenantId' => (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id'),
        'branchId' => (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'saleId' => $saleId,
    ]);

    expect($first->success)->toBeTrue()
        ->and($second->success)->toBeTrue()
        ->and((int) ($second->data['created_items'] ?? -1))->toBe(0);
});

it('autosincroniza pieza finalizada en liquidacion de chica sin generar manual', function () {
    $token = autoSyncAdminToken();
    nightposOpenCashSession($token, 100, false);

    $girlId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza autosync room-service',
        'total_amount' => 120,
        'girl_percent' => 75,
        'duration_minutes' => 45,
    ]), nightposOperationalHeaders($token))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token))
        ->assertOk();

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $serviceId)
        ->first();

    expect($item)->not->toBeNull();

    $settlement = StaffSettlementModel::query()->find((int) $item->staff_settlement_id);

    expect($settlement)->not->toBeNull()
        ->and((int) $settlement->staff_user_id)->toBe($girlId)
        ->and((float) $settlement->total_amount)->toBeGreaterThan(0);
});

it('autosincroniza pieza al registrar sin requerir finalizar ni generar manual', function () {
    $token = autoSyncAdminToken();
    nightposOpenCashSession($token, 100, false);

    $girlId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza autosync create',
        'total_amount' => 160,
        'girl_percent' => 75,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($token))
        ->assertCreated()
        ->json('data.room_service.id');

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $serviceId)
        ->first();

    expect($item)->not->toBeNull();

    $settlement = StaffSettlementModel::query()->find((int) $item->staff_settlement_id);

    expect($settlement)->not->toBeNull()
        ->and((int) $settlement->staff_user_id)->toBe($girlId)
        ->and((float) $settlement->total_amount)->toBeGreaterThan(0);
});

it('sync por pieza es idempotente y no duplica GIRL_ROOM', function () {
    $token = autoSyncAdminToken();
    nightposOpenCashSession($token, 100, false);

    $girlId = (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza idempotente',
        'total_amount' => 140,
        'girl_percent' => 70,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($token))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token))
        ->assertOk();

    $sync = app(SyncSettlementsFromRoomServiceUseCase::class);
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    $first = $sync->execute((object) [
        'tenantId' => $tenantId,
        'branchId' => $branchId,
        'roomServiceId' => $serviceId,
    ]);

    $second = $sync->execute((object) [
        'tenantId' => $tenantId,
        'branchId' => $branchId,
        'roomServiceId' => $serviceId,
    ]);

    $itemCount = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $serviceId)
        ->count();

    expect($first->success)->toBeTrue()
        ->and($second->success)->toBeTrue()
        ->and((int) ($second->data['created_items'] ?? -1))->toBe(0)
        ->and($itemCount)->toBe(1);
});

it('no usa order_items no cobrados para liquidaciones', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    nightposCreateOrderWithItem($token, ['table_label' => 'No cobrada']);

    $overview = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($token))
        ->assertOk();

    expect((int) StaffSettlementItemModel::query()->count())->toBe(0)
        ->and($overview->json('data.summary.total_pending'))->toBe('0.00');
});

it('pending settlements no bloquean cierre de caja y quedan en pending tras cierre', function () {
    $token = autoSyncCashierToken();
    nightposOpenCashSession($token, 100, false);

    autoSyncChargeOrderWithMode($token, 'CON_ACOMPANANTE');
    autoSyncCancelSentToBarOrders();

    test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', true);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 140,
    ], nightposOperationalHeaders($token))->assertOk();

    expect((int) StaffSettlementModel::query()
        ->where('status', 'PENDING')
        ->count())->toBeGreaterThan(0);
});
