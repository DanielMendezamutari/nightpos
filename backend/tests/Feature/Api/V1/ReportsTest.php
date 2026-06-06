<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
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
