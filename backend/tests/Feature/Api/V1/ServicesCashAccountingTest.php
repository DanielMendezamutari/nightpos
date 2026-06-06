<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function servicesCashAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function servicesCashGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

it('rejects bracelet without open cash session', function () {
    $token = servicesCashAdminToken();
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => servicesCashGirlId(),
        'quantity' => 1,
        'unit_price' => 50,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja antes de registrar este servicio.');
});

it('rejects room service without open cash session', function () {
    $token = servicesCashAdminToken();
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload(), nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja antes de registrar este servicio.');
});

it('rejects show without open cash session', function () {
    $token = servicesCashAdminToken();
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => servicesCashGirlId(),
        'show_type' => 'PRIVATE',
        'unit_price' => 120,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja antes de registrar este servicio.');
});

it('registers bracelet and creates cash movement', function () {
    $token = servicesCashAdminToken();
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => servicesCashGirlId(),
        'quantity' => 2,
        'unit_price' => 30,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();

    $braceletId = (int) $response->json('data.bracelet.id');
    $bracelet = BraceletModel::query()->find($braceletId);

    expect($bracelet->cash_session_id)->not->toBeNull()
        ->and($bracelet->payment_method)->toBe('CASH')
        ->and($bracelet->cash_movement_id)->not->toBeNull();

    $movement = CashMovementModel::query()->find($bracelet->cash_movement_id);

    expect($movement->movement_type)->toBe('INCOME')
        ->and($movement->amount)->toBe('60.00')
        ->and($movement->source_type)->toBe('BRACELET')
        ->and($movement->source_id)->toBe($braceletId);
});

it('registers room service with percent split and cash movement', function () {
    $token = servicesCashAdminToken();
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'total_amount' => 200,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
    ]), nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.room_service.girl_amount'))->toBe('100.00')
        ->and($response->json('data.room_service.house_amount'))->toBe('100.00');

    $serviceId = (int) $response->json('data.room_service.id');
    $service = RoomServiceModel::query()->find($serviceId);
    $movement = CashMovementModel::query()->find($service->cash_movement_id);

    expect($movement->amount)->toBe('200.00')
        ->and($movement->source_type)->toBe('ROOM_SERVICE')
        ->and($movement->source_id)->toBe($serviceId);
});

it('registers show and creates cash movement', function () {
    $token = servicesCashAdminToken();
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/shows', [
        'girl_user_id' => servicesCashGirlId(),
        'show_type' => 'PRIVATE',
        'unit_price' => 150,
        'payment_method' => 'QR',
    ], nightposOperationalHeaders($token))->assertCreated();

    $showId = (int) $response->json('data.show.id');
    $show = ShowModel::query()->find($showId);

    expect($show->payment_method)->toBe('QR')
        ->and(CashMovementModel::query()->find($show->cash_movement_id)->source_type)->toBe('SHOW');
});

it('due room service stays occupied until finish then cleaning to available', function () {
    Carbon::setTestNow('2026-06-02 21:00:00');
    $token = servicesCashAdminToken();
    nightposOpenCashSession($token);

    $roomId = (int) RoomModel::query()->where('code', 'P1')->value('id');

    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => servicesCashGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'duration_minutes' => 5,
        'started_at' => '2026-06-02 21:00:00',
    ]), nightposOperationalHeaders($token))->assertCreated()->json('data.room_service.id');

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED');

    Carbon::setTestNow('2026-06-02 21:06:00');
    test()->artisan('room-services:check-due')->assertSuccessful();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED')
        ->and(RoomServiceModel::query()->find($serviceId)?->status)->toBe('DUE');

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token))->assertOk();
    expect(RoomModel::query()->find($roomId)?->status)->toBe('CLEANING');

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(nightposLoginPin('3333')))->assertOk();
    expect(RoomModel::query()->find($roomId)?->status)->toBe('AVAILABLE');

    Carbon::setTestNow();
});

it('cleaning user accesses cleaning dashboard only with cleaning permissions', function () {
    Carbon::setTestNow('2026-06-02 21:00:00');
    $admin = servicesCashAdminToken();
    nightposOpenCashSession($admin);

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => servicesCashGirlId(),
        'duration_minutes' => 10,
        'started_at' => '2026-06-02 20:50:00',
    ]), nightposOperationalHeaders($admin))->assertCreated();

    $cleaningToken = nightposLoginPin('3333');

    test()->getJson('/api/v1/cleaning/dashboard', nightposOperationalHeaders($cleaningToken))
        ->assertOk()
        ->assertJsonStructure(['data' => ['active', 'due', 'cleaning', 'finished_today']]);

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cleaningToken))
        ->assertForbidden();

    Carbon::setTestNow();
});
