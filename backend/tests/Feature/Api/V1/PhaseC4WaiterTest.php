<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phaseC4WaiterToken(): string
{
    return nightposLoginPin('5678');
}

function phaseC4CashierToken(): string
{
    return nightposLoginPin('1234');
}

function phaseC4WaiterHeaders(?string $token = null): array
{
    return nightposOperationalHeaders($token ?? phaseC4WaiterToken());
}

it('returns waiter dashboard for logged waiter', function () {
    nightposEnsureShiftOpen();

    $response = test()->getJson('/api/v1/waiter/dashboard', phaseC4WaiterHeaders())->assertOk();

    expect($response->json('data.dashboard.cards'))->toHaveKeys([
        'active_tables',
        'open_orders',
        'sent_to_bar',
        'pending_charge',
    ]);
});

it('waiter active scope excludes orders from previous closed shifts', function () {
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa turno viejo',
    ], phaseC4WaiterHeaders())->assertCreated();

    $openShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    OfficialShiftModel::query()->where('id', $openShiftId)->update(['status' => 'CLOSED']);

    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa turno nuevo',
    ], phaseC4WaiterHeaders())->assertCreated();

    $labels = collect(test()->getJson('/api/v1/waiter/orders?scope=active', phaseC4WaiterHeaders())
        ->json('data.orders'))
        ->pluck('table_label')
        ->all();

    expect($labels)->toContain('Mesa turno nuevo')
        ->and($labels)->not->toContain('Mesa turno viejo');
});

it('lists only waiter own orders', function () {
    nightposEnsureShiftOpen();
    $waiterId = nightposDemoWaiterUserId();

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Garzón C4',
    ], phaseC4WaiterHeaders())->assertCreated();

    $otherWaiterId = (int) UserModel::query()->where('username', 'garzon2.demo')->value('id');

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Cajera C4',
        'waiter_user_id' => $otherWaiterId,
    ], phaseC4WaiterHeaders(phaseC4CashierToken()))->assertCreated();

    $response = test()->getJson('/api/v1/waiter/orders?scope=active', phaseC4WaiterHeaders())->assertOk();

    $labels = collect($response->json('data.orders'))->pluck('table_label')->all();

    expect($labels)->toContain('Mesa Garzón C4')
        ->and($labels)->not->toContain('Mesa Cajera C4');
});

it('denies waiter access to another waiter order detail', function () {
    nightposEnsureShiftOpen();
    nightposEnsureShiftOpen();
    $otherWaiter = UserModel::query()->where('username', 'garzon2.demo')->first();
    expect($otherWaiter)->not->toBeNull();

    $tenant = TenantModel::query()->where('slug', 'casa-demo')->first();
    $branch = BranchModel::query()->where('code', 'CENTRO')->first();
    $shiftId = OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $order = OrderModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shiftId,
        'order_number' => 'T-OTHER-1',
        'status' => 'OPEN',
        'table_label' => 'Mesa ajena',
        'waiter_user_id' => $otherWaiter->id,
        'opened_by_user_id' => $otherWaiter->id,
        'subtotal' => 0,
        'total' => 0,
        'currency' => 'BOB',
    ]);

    test()->getJson("/api/v1/orders/{$order->id}", phaseC4WaiterHeaders())
        ->assertStatus(404);
});

it('waiter can list girls for order flow', function () {
    nightposEnsureShiftOpen();

    $response = test()->getJson('/api/v1/waiter/girls', phaseC4WaiterHeaders())->assertOk();

    expect($response->json('data.items'))->toBeArray();
});

it('waiter can list service areas for new order', function () {
    nightposEnsureShiftOpen();

    $response = test()->getJson('/api/v1/waiter/service-areas?active_only=1', phaseC4WaiterHeaders())
        ->assertOk();

    expect($response->json('data.service_areas'))->toBeArray()->not->toBeEmpty();
});

it('waiter creates order with service_area_id', function () {
    nightposEnsureShiftOpen();
    $areaId = (int) ServiceAreaModel::query()->where('code', 'VIP')->value('id');
    expect($areaId)->toBeGreaterThan(0);

    $order = test()->postJson('/api/v1/orders', [
        'service_area_id' => $areaId,
    ], phaseC4WaiterHeaders())->assertCreated();

    expect($order->json('data.order.service_area_id'))->toBe($areaId)
        ->and($order->json('data.order.table_label'))->toBe('VIP');
});

it('waiter cannot create order without table or area', function () {
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/orders', [], phaseC4WaiterHeaders())
        ->assertStatus(422);
});

it('waiter can create order add item and send to bar', function () {
    nightposEnsureShiftOpen();

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa C4 Flow',
    ], phaseC4WaiterHeaders())->assertCreated();

    $orderId = (int) $order->json('data.order.id');

    $productId = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()
        ->where('status', 'active')
        ->value('id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], phaseC4WaiterHeaders())->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], phaseC4WaiterHeaders())
        ->assertOk()
        ->assertJsonPath('data.order.status', 'SENT_TO_BAR');
});

it('cashier can still charge waiter order', function () {
    nightposEnsureShiftOpen();
    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Cobro C4',
    ], phaseC4WaiterHeaders())->assertCreated();

    $orderId = (int) $order->json('data.order.id');
    $productId = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()
        ->where('status', 'active')
        ->value('id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], phaseC4WaiterHeaders())->assertCreated();

    $cashier = phaseC4CashierToken();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashier))->assertCreated();

    $charged = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($cashier))->assertOk();
    $total = (float) $charged->json('data.order.total');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $total]],
    ], nightposOperationalHeaders($cashier))->assertCreated();
});
