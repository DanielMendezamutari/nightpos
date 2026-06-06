<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});


it('allows waiter to create an order and add item with resolved price', function () {
    $token = nightposLoginPin('5678');

    $result = nightposCreateOrderWithItem($token);

    test()->getJson('/api/v1/orders/'.$result['order_id'], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.order.order_number', 'C-0001')
        ->assertJsonPath('data.order.status', 'OPEN')
        ->assertJsonPath('data.order.table_label', 'Mesa 5')
        ->assertJsonPath('data.order.items.0.unit_price', '25.00')
        ->assertJsonPath('data.order.items.0.line_total', '50.00')
        ->assertJsonPath('data.order.total', '50.00');
});

it('sends order to bar and updates status', function () {
    $token = nightposLoginPin('5678');

    $orderId = nightposCreateOrderWithItem($token)['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.order.status', 'SENT_TO_BAR')
        ->assertJsonPath('data.order.items.0.item_status', 'SENT');
});

it('requires girl_user_id for CON_ACOMPANANTE when sending to bar', function () {
    $productId = nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => 80,
            'girl_amount' => 40,
            'house_amount' => 40,
        ],
    ]);

    nightposEnsureShiftOpen();
    $waiterToken = nightposLoginPin('5678');

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => 'VIP 1',
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertStatus(422)
        ->assertJsonPath('message', 'CON_ACOMPANANTE requiere asignar una chica antes de continuar.');
});

it('cancels an open order', function () {
    $token = nightposLoginPin('1234');

    $orderId = nightposCreateOrderWithItem(nightposLoginPin('5678'))['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/cancel", [], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.order.status', 'CANCELLED');
});

it('does not expose orders from another tenant', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $orderId = nightposCreateOrderWithItem($token)['order_id'];

    $otherTenant = \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa-orders',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $foreignOrder = \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => 1,
        'order_number' => 'C-9999',
        'status' => 'OPEN',
        'opened_by_user_id' => 1,
        'subtotal' => 0,
        'total' => 0,
        'currency' => 'BOB',
    ]);

    test()->getJson('/api/v1/orders/'.$foreignOrder->id, nightposOperationalHeaders($token))
        ->assertNotFound();
});

it('denies orders for a branch the user cannot access', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Sur',
        'code' => 'SUR',
        'status' => 'active',
    ]);

    $token = nightposLoginPin('5678');

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa 1',
    ], nightposOperationalHeaders($token, 'SUR'))
        ->assertForbidden();
});
