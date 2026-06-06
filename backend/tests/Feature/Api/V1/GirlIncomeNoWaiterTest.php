<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function noWaiterGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function noWaiterGarzonId(): int
{
    return (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
}

it('registers room service without waiter', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => noWaiterGirlId(),
        'room_label' => 'Suite 10',
        'total_amount' => 100,
        'duration_minutes' => 45,
    ]), nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.room_service'))->not->toHaveKey('waiter_user_id')
        ->and($response->json('data.room_service'))->not->toHaveKey('waiter_name');
});

it('ignores waiter_user_id when sent on room service create', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => noWaiterGirlId(),
        'room_label' => 'Suite 11',
        'total_amount' => 80,
        'duration_minutes' => 30,
        'waiter_user_id' => noWaiterGarzonId(),
    ]), nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.room_service.registered_by_name'))->not->toBeNull()
        ->and($response->json('data.room_service'))->not->toHaveKey('waiter_user_id');
});

it('registers show without waiter', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/shows', [
        'girl_user_id' => noWaiterGirlId(),
        'show_type' => 'STAGE',
        'unit_price' => 200,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.show'))->not->toHaveKey('waiter_user_id')
        ->and($response->json('data.show'))->not->toHaveKey('waiter_name');
});

it('ignores waiter_user_id when sent on show create', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/shows', [
        'girl_user_id' => noWaiterGirlId(),
        'show_type' => 'PRIVATE',
        'unit_price' => 150,
        'payment_method' => 'CASH',
        'waiter_user_id' => noWaiterGarzonId(),
    ], nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.show'))->not->toHaveKey('waiter_user_id');
});

it('girl room and show settlements have no waiter commission items', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = noWaiterGirlId();
    nightposOpenCashSession($token);

    $roomId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Liq Room',
        'total_amount' => 90,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($token))->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomId}/finish", [], nightposOperationalHeaders($token))->assertOk();

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => $girlId,
        'show_type' => 'SPECIAL',
        'unit_price' => 70,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))->assertCreated();

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_ROOM')->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'GIRL_SHOW')->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'WAITER_COMMISSION')->count())->toBe(0);
});
