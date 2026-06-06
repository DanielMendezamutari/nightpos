<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function nightposSeedChargeableOrder(string $token): int
{
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($token))->assertCreated();

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $result = nightposCreateOrderWithItem($token, [
        'table_label' => 'Mesa Cobro',
        'waiter_user_id' => $waiterId,
    ]);

    return $result['order_id'];
}

it('charges order with cash and creates sale snapshot', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    $response = test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [
            ['method' => 'CASH', 'amount' => 50],
        ],
    ], nightposOperationalHeaders($token));

    $response->assertCreated()
        ->assertJsonPath('data.sale.payment_mode', 'CASH')
        ->assertJsonPath('data.sale.total', '50.00')
        ->assertJsonPath('data.sale.items.0.unit_price_snapshot', '25.00')
        ->assertJsonPath('data.sale.items.0.waiter_commission_percent_snapshot', '5.00')
        ->assertJsonPath('data.order_status', 'BILLED');

    expect(SaleModel::query()->where('order_id', $orderId)->exists())->toBeTrue();
    expect(OrderModel::query()->find($orderId)->status)->toBe('BILLED');
    expect(CashMovementModel::query()->where('description', 'like', '%Cobro comanda%')->exists())->toBeTrue();
});

it('charges with mixed payments', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [
            ['method' => 'CASH', 'amount' => 30],
            ['method' => 'QR', 'amount' => 20],
        ],
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.sale.payment_mode', 'MIXED')
        ->assertJsonPath('data.sale.payments', fn ($p) => count($p) === 2);
});

it('rejects charge without open cash session', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    CashSessionModel::query()->update(['status' => 'CLOSED']);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe tener una caja abierta para cobrar.');
});

it('rejects charge on empty order', function () {
    $token = nightposLoginPin('1234');
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($token));

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => 'Vacía',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 0]],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422);
});

it('rejects charge on cancelled order', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    test()->postJson("/api/v1/orders/{$orderId}/cancel", [], nightposOperationalHeaders($token));

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422);
});

it('rejects charging twice', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'La comanda ya fue cobrada.');
});

it('denies waiter from charging', function () {
    $cashierToken = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($cashierToken);

    $waiterToken = nightposLoginPin('5678');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($waiterToken))
        ->assertForbidden();
});

it('lists sales for current session', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'QR', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->getJson('/api/v1/sales?current_session=1', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.sales.0.payment_mode', 'QR');
});
