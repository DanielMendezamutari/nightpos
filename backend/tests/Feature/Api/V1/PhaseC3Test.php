<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomTypeCatalogModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phaseC3AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function phaseC3Headers(?string $token = null, string $branchCode = 'CENTRO'): array
{
    return nightposOperationalHeaders($token ?? phaseC3AdminToken(), $branchCode);
}

it('creates income cash movement reason', function () {
    $response = test()->postJson('/api/v1/cash-movement-reasons', [
        'type' => 'INCOME',
        'name' => 'Propina extra',
    ], phaseC3Headers())->assertCreated();

    expect($response->json('data.cash_movement_reason.type'))->toBe('INCOME')
        ->and($response->json('data.cash_movement_reason.name'))->toBe('Propina extra');
});

it('creates expense cash movement reason', function () {
    $response = test()->postJson('/api/v1/cash-movement-reasons', [
        'type' => 'EXPENSE',
        'name' => 'Gasto imprevisto',
    ], phaseC3Headers())->assertCreated();

    expect($response->json('data.cash_movement_reason.type'))->toBe('EXPENSE');
});

it('uses catalog reason on manual cash movement', function () {
    nightposEnsureShiftOpen();
    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], phaseC3Headers($token))->assertCreated();

    $reason = CashMovementReasonModel::query()
        ->where('type', 'EXPENSE')
        ->where('name', 'Pago taxi')
        ->first();

    $response = test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 25,
        'cash_movement_reason_id' => $reason->id,
        'payment_method' => 'CASH',
        'notes' => 'Ida al banco',
    ], phaseC3Headers($token))->assertCreated();

    $movement = collect($response->json('data.session.movements'))->first();

    expect($movement['cash_movement_reason_id'])->toBe($reason->id)
        ->and($movement['notes'])->toBe('Ida al banco');
});

it('lists active payment methods', function () {
    $response = test()->getJson('/api/v1/payment-methods?active_only=1', phaseC3Headers())->assertOk();

    $codes = collect($response->json('data.payment_methods'))->pluck('code')->all();

    expect($codes)->toContain('CASH', 'QR', 'CARD');
});

it('creates custom QR payment method', function () {
    $response = test()->postJson('/api/v1/payment-methods', [
        'code' => 'QR_YAPE',
        'name' => 'Yape QR',
        'type' => 'QR',
        'enabled' => true,
    ], phaseC3Headers())->assertCreated();

    expect($response->json('data.payment_method.code'))->toBe('QR_YAPE')
        ->and($response->json('data.payment_method.legacy_method'))->toBe('QR');
});

it('creates service area and order can use it', function () {
    nightposEnsureShiftOpen();
    $waiterId = nightposDemoWaiterUserId();

    $area = test()->postJson('/api/v1/service-areas', [
        'code' => 'VIP-A',
        'name' => 'VIP A',
        'area_type' => 'VIP',
    ], phaseC3Headers())->assertCreated();

    $areaId = (int) $area->json('data.service_area.id');

    $order = test()->postJson('/api/v1/orders', [
        'service_area_id' => $areaId,
        'waiter_user_id' => $waiterId,
    ], phaseC3Headers(nightposLoginPin('1234')))->assertCreated();

    expect($order->json('data.order.service_area_id'))->toBe($areaId)
        ->and($order->json('data.order.table_label'))->toBe('VIP A');
});

it('order keeps table_label fallback without service area', function () {
    nightposEnsureShiftOpen();
    $waiterId = nightposDemoWaiterUserId();

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Cliente walk-in',
        'waiter_user_id' => $waiterId,
    ], phaseC3Headers(nightposLoginPin('1234')))->assertCreated();

    expect($order->json('data.order.table_label'))->toBe('Cliente walk-in')
        ->and($order->json('data.order.service_area_id'))->toBeNull();
});

it('creates room type catalog entry and room using it', function () {
    $type = test()->postJson('/api/v1/room-types', [
        'code' => 'PREMIUM',
        'name' => 'Premium',
        'default_duration_minutes' => 75,
        'suggested_price' => 120,
    ], phaseC3Headers())->assertCreated();

    $typeId = (int) $type->json('data.room_type.id');

    $room = test()->postJson('/api/v1/rooms', [
        'code' => 'PR1',
        'name' => 'Premium 1',
        'room_type_id' => $typeId,
        'default_duration_minutes' => 75,
        'suggested_price' => 120,
    ], phaseC3Headers())->assertCreated();

    expect($room->json('data.room.room_type'))->toBe('PREMIUM');
});

it('first night checklist detects missing and complete states', function () {
    $response = test()->getJson('/api/v1/settings/first-night-checklist', phaseC3Headers())->assertOk();

    $items = collect($response->json('data.checklist.items'));
    $keys = $items->pluck('key')->all();

    expect($keys)->toContain('active_products', 'payment_methods', 'cash_reasons', 'show_types')
        ->and($response->json('data.checklist.complete'))->toBeBool();
});

it('isolates master data by tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro Local',
        'slug' => 'otro-local',
        'status' => 'active',
    ]);

    CashMovementReasonModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'type' => 'EXPENSE',
        'name' => 'Solo otro tenant',
        'status' => 'active',
    ]);

    $response = test()->getJson('/api/v1/cash-movement-reasons', phaseC3Headers())->assertOk();

    $names = collect($response->json('data.cash_movement_reasons'))->pluck('name')->all();

    expect($names)->not->toContain('Solo otro tenant');
});
