<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposOpenCashSession(phase18AdminToken());
});

function phase18AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function phase18GirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function phase18RoomId(string $code): int
{
    return (int) RoomModel::query()->where('code', $code)->value('id');
}

it('creates and updates room', function () {
    $token = phase18AdminToken();

    $create = test()->postJson('/api/v1/rooms', [
        'code' => 'S99',
        'name' => 'Suite Demo',
        'room_type' => 'SUITE',
        'default_duration_minutes' => 120,
        'suggested_price' => 250,
    ], nightposOperationalHeaders($token))->assertCreated();

    expect($create->json('data.room.status'))->toBe('AVAILABLE')
        ->and($create->json('data.room.code'))->toBe('S99');

    $roomId = (int) $create->json('data.room.id');

    test()->putJson("/api/v1/rooms/{$roomId}", [
        'code' => 'S99',
        'name' => 'Suite Demo Actualizada',
        'room_type' => 'SUITE',
        'default_duration_minutes' => 90,
        'suggested_price' => 300,
    ], nightposOperationalHeaders($token))->assertOk()
        ->assertJsonPath('data.room.name', 'Suite Demo Actualizada');
});

it('lists available rooms endpoint', function () {
    $token = phase18AdminToken();

    $response = test()->getJson('/api/v1/rooms/available', nightposOperationalHeaders($token))->assertOk();

    expect($response->json('data.items'))->not->toBeEmpty()
        ->and(collect($response->json('data.items'))->every(fn ($r) => $r['status'] === 'AVAILABLE'))->toBeTrue();
});

it('registers room service with room and occupies room', function () {
    $token = phase18AdminToken();
    $girlId = phase18GirlId();
    $roomId = phase18RoomId('P1');

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 100,
        'duration_minutes' => 45,
    ]), nightposOperationalHeaders($token))->assertCreated();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED')
        ->and(RoomServiceModel::query()->where('room_id', $roomId)->value('status'))->toBe('ACTIVE');
});

it('rejects assigning occupied room', function () {
    $token = phase18AdminToken();
    $girlId = phase18GirlId();
    $roomId = phase18RoomId('P2');

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 90,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 90,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($token))->assertStatus(422);
});

it('finishes room service and sets room to cleaning', function () {
    $token = phase18AdminToken();
    $girlId = phase18GirlId();
    $roomId = phase18RoomId('P3');

    $serviceId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 80,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($token))->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token))->assertOk();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('CLEANING');
});

it('cleaning user marks room available', function () {
    $token = phase18AdminToken();
    $girlId = phase18GirlId();
    $roomId = phase18RoomId('P4');

    $serviceId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 70,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($token))->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token));

    $cleaningToken = nightposLoginPin('3333');

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders($cleaningToken))->assertOk();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('AVAILABLE');
});

it('maintenance blocks room assignment', function () {
    $token = phase18AdminToken();
    $roomId = phase18RoomId('VIP1');

    test()->postJson("/api/v1/rooms/{$roomId}/mark-maintenance", [], nightposOperationalHeaders($token))->assertOk();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => phase18GirlId(),
        'room_id' => $roomId,
        'total_amount' => 150,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($token))->assertStatus(422);
});

it('admin returns room from maintenance to available', function () {
    $token = phase18AdminToken();
    $roomId = phase18RoomId('VIP2');

    test()->postJson("/api/v1/rooms/{$roomId}/mark-maintenance", [], nightposOperationalHeaders($token));
    test()->postJson("/api/v1/rooms/{$roomId}/mark-available", [], nightposOperationalHeaders($token))->assertOk();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('AVAILABLE');
});

it('rooms dashboard summary counts statuses', function () {
    $token = phase18AdminToken();

    $response = test()->getJson('/api/v1/rooms', nightposOperationalHeaders($token))->assertOk();

    expect($response->json('data.summary.total'))->toBeGreaterThan(0)
        ->and($response->json('data.summary.available'))->toBeGreaterThan(0);
});

it('isolates rooms by tenant and branch', function () {
    $token = phase18AdminToken();

    $room = test()->getJson('/api/v1/rooms/'.phase18RoomId('P1'), nightposOperationalHeaders($token))->assertOk();

    expect($room->json('data.room.branch_code'))->toBe('CENTRO');
});
