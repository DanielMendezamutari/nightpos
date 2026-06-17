<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phaseC1CashierToken(): string
{
    return nightposLoginPin('1234');
}

function phaseC1WaiterToken(): string
{
    return nightposLoginPin('5678');
}

function phaseC1AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

it('requires cashier to select waiter when creating order', function () {
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Cajera',
    ], nightposOperationalHeaders(phaseC1CashierToken()))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe seleccionar un garzón para abrir la comanda.');
});

it('assigns waiter session user when waiter creates order', function () {
    nightposEnsureShiftOpen();

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $response = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Garzón',
    ], nightposOperationalHeaders(phaseC1WaiterToken()))
        ->assertCreated();

    expect($response->json('data.order.waiter_user_id'))->toBe($waiterId);
});

it('creates quick waiter and uses it on new order', function () {
    nightposEnsureShiftOpen();

    $created = test()->postJson('/api/v1/staff/quick-waiters', [
        'name' => 'Garzón Rápido C1',
        'pin' => '7777',
        'waiter_commission_percent' => 7,
    ], nightposOperationalHeaders(phaseC1CashierToken()))
        ->assertCreated()
        ->json('data.waiter');

    $waiterId = (int) $created['id'];

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Quick Waiter',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders(phaseC1CashierToken()))
        ->assertCreated()
        ->assertJsonPath('data.order.waiter_user_id', $waiterId);
});

it('creates quick combo product with bracelet allocation in one step', function () {
    $category = ProductCategoryModel::query()->create([
        'tenant_id' => 1,
        'name' => 'Combos C1',
        'status' => 'active',
    ]);

    $response = test()->postJson('/api/v1/products/quick', [
        'name' => 'Combo 6 Cervezas',
        'category_id' => $category->id,
        'solo_price' => 120,
        'companion_price' => 180,
        'girl_amount' => 90,
        'house_amount' => 90,
        'settlement_behavior' => 'GIRL_BRACELET_ALLOCATION',
        'bracelet_units_per_line' => 6,
        'unit' => 'combo',
    ], nightposOperationalHeaders(phaseC1CashierToken()))
        ->assertCreated();

    expect($response->json('data.product.settlement_behavior'))->toBe('GIRL_BRACELET_ALLOCATION')
        ->and($response->json('data.product.bracelet_units_per_line'))->toBe(6)
        ->and($response->json('data.product.requires_allocation'))->toBeTrue()
        ->and($response->json('data.prices'))->toHaveCount(2);
});

it('creates quick product with solo and companion prices', function () {
    $category = ProductCategoryModel::query()->create([
        'tenant_id' => 1,
        'name' => 'Bebidas C1',
        'status' => 'active',
    ]);

    $response = test()->postJson('/api/v1/products/quick', [
        'name' => 'Trago C1 Quick',
        'category_id' => $category->id,
        'solo_price' => 30,
        'companion_price' => 90,
        'girl_amount' => 45,
        'house_amount' => 45,
    ], nightposOperationalHeaders(phaseC1CashierToken()))
        ->assertCreated();

    $productId = (int) $response->json('data.product.id');

    expect(ProductPriceModel::query()
        ->where('product_id', $productId)
        ->where('sale_mode', 'SOLO_CLIENTE')
        ->where('status', 'active')
        ->exists())->toBeTrue()
        ->and(ProductPriceModel::query()
            ->where('product_id', $productId)
            ->where('sale_mode', 'CON_ACOMPANANTE')
            ->where('status', 'active')
            ->exists())->toBeTrue();
});

it('lists cleaning rooms when none are available', function () {
    $tenantId = 1;
    $branchId = 1;

    RoomModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'code' => 'C1-LIMP',
        'name' => 'Suite Limpieza',
        'room_type' => 'SUITE',
        'status' => 'CLEANING',
        'suggested_price' => 150,
        'default_duration_minutes' => 60,
    ]);

    $available = test()->getJson('/api/v1/rooms/available', nightposOperationalHeaders(phaseC1AdminToken()))
        ->assertOk()
        ->json('data.items');

    $cleaning = test()->getJson('/api/v1/rooms/cleaning', nightposOperationalHeaders(phaseC1AdminToken()))
        ->assertOk()
        ->json('data.items');

    expect($available)->toBeArray()
        ->and(collect($cleaning)->pluck('code'))->toContain('C1-LIMP');
});

it('reports waiters without commission in pending sources when they had activity', function () {
    $waiterId = nightposDemoWaiterUserId();
    $cashier = phaseC1CashierToken();
    nightposOpenCashSession($cashier, 100);

    // El garzón participa en una venta del turno actual.
    $created = nightposCreateOrderWithItem($cashier, ['table_label' => 'Mesa C1 garzón']);
    test()->postJson("/api/v1/orders/{$created['order_id']}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    StaffProfileModel::query()
        ->where('user_id', $waiterId)
        ->update(['waiter_commission_percent' => null]);

    $response = test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders(phaseC1AdminToken()))
        ->assertOk();

    expect(collect($response->json('data.waiters_without_commission'))->pluck('id'))
        ->toContain($waiterId);
});

it('does not warn about waiters without commission when they had no activity in the shift', function () {
    $waiterId = nightposDemoWaiterUserId();
    nightposEnsureShiftOpen();

    StaffProfileModel::query()
        ->where('user_id', $waiterId)
        ->update(['waiter_commission_percent' => null]);

    $response = test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders(phaseC1AdminToken()))
        ->assertOk();

    expect(collect($response->json('data.waiters_without_commission'))->pluck('id'))
        ->not->toContain($waiterId);
});

it('reports girls without commission flag in pending sources when they had activity', function () {
    $girlId = (int) UserModel::query()
        ->whereHas('staffProfile', fn ($q) => $q->where('staff_role', 'GIRL'))
        ->value('id');

    $cashier = phaseC1CashierToken();
    nightposOpenCashSession($cashier, 100);

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
    ]), nightposOperationalHeaders($cashier))->assertCreated();

    StaffProfileModel::query()
        ->where('user_id', $girlId)
        ->update(['can_receive_girl_commissions' => false]);

    $response = test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders(phaseC1AdminToken()))
        ->assertOk();

    expect(collect($response->json('data.girls_without_commission_flag'))->pluck('id'))
        ->toContain($girlId);
});
