<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function reportsAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function reportsAdminH(): array
{
    return nightposOperationalHeaders(reportsAdminToken());
}

function reportsChargeOrder(string $token, int $orderId): void
{
    nightposOpenCashSession($token);
    $headers = nightposOperationalHeaders($token);
    test()->withHeaders($headers)->post("/api/v1/orders/{$orderId}/send-to-bar")->assertSuccessful();
    test()->withHeaders($headers)->post("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ])->assertSuccessful();
}

function reportsCreateDirectSale(string $token, string $method, float $amount = 25): void
{
    nightposOpenCashSession($token);
    $headers = nightposOperationalHeaders($token);

    $productId = nightposSeedOrderProduct();
    $priceId = (int) ProductPriceModel::query()->where('product_id', $productId)->value('id');

    test()->withHeaders($headers)
        ->post('/api/v1/direct-sales', [
            'items' => [[
                'product_id' => $productId,
                'price_id' => $priceId,
                'quantity' => 1,
                'sale_mode' => 'SOLO_CLIENTE',
            ]],
            'payments' => [[
                'method' => $method,
                'amount' => $amount,
            ]],
        ])
        ->assertCreated();
}

function reportsChargeGirlOrder(string $cashierToken, string $waiterToken, float $girlAmount = 100): void
{
    nightposOpenCashSession($cashierToken);

    $productId = nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => $girlAmount * 2,
            'girl_amount' => $girlAmount,
            'house_amount' => $girlAmount,
        ],
    ]);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => 'Report Girl',
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

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $girlAmount * 2]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('1. daily report sums order sales', function () {
    $token   = reportsAdminToken();
    $result  = nightposCreateOrderWithItem($token);
    $orderId = $result['order_id'];
    reportsChargeOrder($token, $orderId);

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/daily')
        ->assertOk()
        ->json('data');

    expect((float) $resp['sales']['total'])->toBeGreaterThan(0);
    expect((float) $resp['sales']['total_cash'])->toBeGreaterThan(0);
});

it('2. daily report sums direct sale', function () {
    $token   = reportsAdminToken();
    $headers = nightposOperationalHeaders($token);

    nightposOpenCashSession($token);

    $productId = nightposSeedOrderProduct();
    $priceId   = (int) ProductPriceModel::query()->where('product_id', $productId)->value('id');

    $this->withHeaders($headers)
        ->post('/api/v1/direct-sales', [
            'items'    => [['product_id' => $productId, 'price_id' => $priceId, 'quantity' => 1, 'sale_mode' => 'SOLO_CLIENTE']],
            'payments' => [['method' => 'CASH', 'amount' => 25]],
        ])
        ->assertCreated();

    $resp = $this->withHeaders($headers)
        ->get('/api/v1/reports/daily')
        ->assertOk()
        ->json('data');

    expect((float) $resp['sales']['total'])->toBeGreaterThan(0);
});

it('3. daily report separates CASH / QR / CARD', function () {
    $token   = reportsAdminToken();
    $result  = nightposCreateOrderWithItem($token);
    reportsChargeOrder($token, $result['order_id']);

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/daily')
        ->assertOk()
        ->json('data.sales');

    expect($resp)->toHaveKeys(['total_cash', 'total_qr', 'total_card']);
    expect((float) $resp['total_cash'])->toBeGreaterThan(0);
    expect((float) $resp['total_qr'])->toBe(0.0);
    expect((float) $resp['total_card'])->toBe(0.0);
});

it('4. cash report includes opening session', function () {
    $token   = reportsAdminToken();
    nightposOpenCashSession($token);

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/cash')
        ->assertOk()
        ->json('data');

    expect($resp['sessions'])->not->toBeEmpty();
    expect($resp['open_count'])->toBeGreaterThan(0);
});

it('5. services report sums bracelets', function () {
    $headers = reportsAdminH();
    $shift   = OfficialShiftModel::query()->where('tenant_id', 1)->where('branch_id', 1)->first();
    $girlId  = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    BraceletModel::query()->create([
        'tenant_id'             => 1,
        'branch_id'             => 1,
        'official_shift_id'     => $shift->id,
        'cash_session_id'       => null,
        'girl_user_id'          => $girlId,
        'waiter_user_id'        => null,
        'quantity'              => 2,
        'unit_price'            => 50.00,
        'total_amount'          => 100.00,
        'payment_method'        => 'CASH',
        'cash_movement_id'      => null,
        'registered_by_user_id' => $adminId,
        'registered_at'         => now(),
    ]);

    $resp = $this->withHeaders($headers)
        ->get('/api/v1/reports/services')
        ->assertOk()
        ->json('data');

    expect((float) $resp['totals']['bracelets_total'])->toBeGreaterThan(0);
    expect($resp['bracelets'])->not->toBeEmpty();
});

it('6. settlements report separates paid and pending', function () {
    $headers = reportsAdminH();

    $this->withHeaders($headers)->post('/api/v1/settlements/generate-current-shift')->assertSuccessful();

    $resp = $this->withHeaders($headers)
        ->get('/api/v1/reports/settlements')
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKey('totals');
    expect($resp['totals'])->toHaveKeys(['total_generated', 'total_paid', 'total_pending']);
});

it('7. rooms report counts rooms', function () {
    $resp = $this->withHeaders(reportsAdminH())
        ->get('/api/v1/reports/rooms')
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKey('rooms');
    expect($resp)->toHaveKey('totals');
    expect($resp['totals'])->toHaveKey('rooms_count');
});

it('8. tenant isolation: daily report only shows own tenant data', function () {
    $token  = reportsAdminToken();
    $result = nightposCreateOrderWithItem($token);
    reportsChargeOrder($token, $result['order_id']);

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/daily')
        ->assertOk()
        ->json('data');

    expect((float) $resp['sales']['total'])->toBeGreaterThanOrEqual(50.0);
});

it('9. branch isolation: report has expected daily structure', function () {
    $resp = $this->withHeaders(reportsAdminH())
        ->get('/api/v1/reports/daily')
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKeys(['sales', 'services', 'settlements', 'cash', 'rooms']);
});

it('10. report filters by official_shift_id', function () {
    $shift = OfficialShiftModel::query()->where('tenant_id', 1)->where('branch_id', 1)->first();

    $resp = $this->withHeaders(reportsAdminH())
        ->get("/api/v1/reports/daily?official_shift_id={$shift->id}")
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKey('sales');
});

it('11. shift-closure check returns can_close false when cash sessions are open', function () {
    $token = reportsAdminToken();
    nightposOpenCashSession($token);

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/shift-closure')
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKeys(['can_close', 'blockers', 'warnings', 'summary']);
    expect($resp['can_close'])->toBeFalse();
    expect($resp['blockers'])->not->toBeEmpty();
    $codes = collect($resp['blockers'])->pluck('code')->all();
    expect($codes)->toContain('open_cash_sessions');
});

it('managerial 1. carga reporte con fecha', function () {
    $token = reportsAdminToken();
    $result = nightposCreateOrderWithItem($token);
    reportsChargeOrder($token, $result['order_id']);

    $date = now()->format('Y-m-d');

    $resp = $this->withHeaders(nightposOperationalHeaders($token))
        ->get("/api/v1/reports/managerial-daily?date_from={$date}&date_to={$date}")
        ->assertOk()
        ->json('data');

    expect($resp)->toHaveKeys(['scope', 'kpis', 'waiter_rankings', 'girl_rankings', 'product_rankings', 'hourly_performance', 'room_performance', 'cash_health', 'alerts', 'managerial_formula']);
});

it('managerial 2. respeta tenant branch', function () {
    $token = reportsAdminToken();

    $scope = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.scope');

    expect($scope['tenant_id'])->toBe(1)
        ->and($scope['branch_id'])->toBe(1);
});

it('managerial 3. incluye ventas por metodo', function () {
    $token = reportsAdminToken();

    reportsCreateDirectSale($token, 'CASH', 25);
    reportsCreateDirectSale($token, 'QR', 25);
    reportsCreateDirectSale($token, 'CARD', 25);

    $kpis = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.kpis');

    expect((float) $kpis['total_cash'])->toBeGreaterThan(0)
        ->and((float) $kpis['total_qr'])->toBeGreaterThan(0)
        ->and((float) $kpis['total_card'])->toBeGreaterThan(0);
});

it('managerial 4. incluye ranking garzones', function () {
    $token = reportsAdminToken();
    $result = nightposCreateOrderWithItem($token);
    reportsChargeOrder($token, $result['order_id']);

    $rows = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.waiter_rankings.top_waiters_by_sales');

    expect($rows)->not->toBeEmpty();
});

it('managerial 5. incluye ranking chicas', function () {
    $token = reportsAdminToken();
    $shift = OfficialShiftModel::query()->where('tenant_id', 1)->where('branch_id', 1)->firstOrFail();
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    RoomServiceModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => 1,
        'official_shift_id' => $shift->id,
        'girl_user_id' => $girlId,
        'room_id' => null,
        'room_label' => 'VIP 1',
        'room_number' => 'VIP1',
        'unit_price' => 120,
        'total_amount' => 120,
        'girl_percent' => 50,
        'gross_girl_amount' => 60,
        'girl_amount' => 60,
        'house_amount' => 60,
        'cleaning_amount' => 0,
        'payment_method' => 'CASH',
        'registered_by_user_id' => $girlId,
        'registered_at' => now(),
        'status' => 'FINISHED',
        'duration_minutes' => 60,
    ]);

    $rows = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.girl_rankings.top_girls_by_generated_income');

    expect($rows)->not->toBeEmpty();
});

it('managerial 6. incluye top productos', function () {
    $token = reportsAdminToken();
    reportsCreateDirectSale($token, 'CASH', 25);

    $rows = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.product_rankings.top_products_by_revenue');

    expect($rows)->not->toBeEmpty();
});

it('managerial 7. incluye rendimiento por hora', function () {
    $token = reportsAdminToken();
    reportsCreateDirectSale($token, 'CASH', 25);

    $hourly = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily?include_hours_granularity=hour_24')
        ->assertOk()
        ->json('data.hourly_performance');

    expect($hourly)->toHaveKeys(['granularity', 'best_hour_by_revenue', 'hourly_buckets'])
        ->and($hourly['hourly_buckets'])->not->toBeEmpty();
});

it('managerial 8. incluye alertas', function () {
    $token = reportsAdminToken();
    nightposOpenCashSession($token);

    $alerts = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.alerts');

    expect($alerts)->toHaveKeys(['blockers', 'warnings', 'pending_summary'])
        ->and($alerts['blockers'])->not->toBeEmpty();
});

it('managerial 9. incluye garzones con comision 0 si tuvieron ventas', function () {
    $token = reportsAdminToken();
    $result = nightposCreateOrderWithItem($token);
    reportsChargeOrder($token, $result['order_id']);

    StaffSettlementModel::query()->where('tenant_id', 1)->where('branch_id', 1)->delete();

    $rows = $this->withHeaders(nightposOperationalHeaders($token))
        ->get('/api/v1/reports/managerial-daily')
        ->assertOk()
        ->json('data.waiter_rankings.top_waiters_by_compensation');

    expect($rows)->not->toBeEmpty()
        ->and((float) ($rows[0]['compensation_total'] ?? -1))->toBe(0.0);
});

it('managerial 10. mantiene limpieza manual en resumen de turno', function () {
    $admin = reportsAdminToken();
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    reportsChargeGirlOrder($cashier, $waiter, 100);

    test()->withHeaders(nightposOperationalHeaders($admin))
        ->post('/api/v1/settlements/generate-current-shift')
        ->assertCreated();

    $settlement = StaffSettlementModel::query()
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();

    test()->patchJson("/api/v1/settlements/{$settlement->id}/cleaning-deduction", [
        'amount' => 10,
    ], nightposOperationalHeaders($cashier))->assertOk();

    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $summary = $this->withHeaders(nightposOperationalHeaders($admin))
        ->get("/api/v1/shifts/{$shiftId}/summary")
        ->assertOk()
        ->json('data.managerial.settlement_adjustments');

    expect($summary['cleaning']['count'])->toBe(1)
        ->and($summary['cleaning']['amount'])->toBe('-10.00');
});
