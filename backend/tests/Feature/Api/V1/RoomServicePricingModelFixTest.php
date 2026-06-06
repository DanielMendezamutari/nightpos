<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposOpenCashSession(pricingFixAdminToken());
});

function pricingFixAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function pricingFixGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function pricingFixRoomServicePayload(array $overrides = []): array
{
    return array_merge([
        'girl_user_id' => pricingFixGirlId(),
        'room_label' => 'Pieza modelo',
        'room_number' => 'PM1',
        'total_amount' => 200,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], $overrides);
}

it('creates room without price or duration', function () {
    $token = pricingFixAdminToken();

    $response = test()->postJson('/api/v1/rooms', [
        'code' => 'VIP-NO-PRICE',
        'name' => 'Pieza VIP sin defaults',
        'room_type' => 'VIP',
        'notes' => 'Solo recurso físico',
    ], nightposOperationalHeaders($token))->assertCreated();

    expect($response->json('data.room.suggested_price'))->toBeNull()
        ->and($response->json('data.room.default_duration_minutes'))->toBeNull()
        ->and($response->json('data.room.code'))->toBe('VIP-NO-PRICE');
});

it('registers room service with total 200 and percent 50', function () {
    $token = pricingFixAdminToken();

    $response = test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload(),
        nightposOperationalHeaders($token),
    )->assertCreated();

    expect($response->json('data.room_service.total_amount'))->toBe('200.00')
        ->and($response->json('data.room_service.girl_percent'))->toBe('50.00')
        ->and($response->json('data.room_service.girl_amount'))->toBe('100.00')
        ->and($response->json('data.room_service.house_amount'))->toBe('100.00')
        ->and($response->json('data.room_service.duration_minutes'))->toBe(30);
});

it('calculates girl 120 and house 80 when percent is 60', function () {
    $token = pricingFixAdminToken();

    $response = test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload(['girl_percent' => 60]),
        nightposOperationalHeaders($token),
    )->assertCreated();

    expect($response->json('data.room_service.girl_amount'))->toBe('120.00')
        ->and($response->json('data.room_service.house_amount'))->toBe('80.00');
});

it('rejects girl percent below zero', function () {
    $token = pricingFixAdminToken();

    test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload(['girl_percent' => -1]),
        nightposOperationalHeaders($token),
    )->assertUnprocessable();
});

it('rejects girl percent above 100', function () {
    $token = pricingFixAdminToken();

    test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload(['girl_percent' => 100.01]),
        nightposOperationalHeaders($token),
    )->assertUnprocessable();
});

it('ignores manual girl_amount and house_amount from request', function () {
    $token = pricingFixAdminToken();

    $response = test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload([
            'girl_percent' => 50,
            'girl_amount' => 10,
            'house_amount' => 190,
        ]),
        nightposOperationalHeaders($token),
    )->assertCreated();

    expect($response->json('data.room_service.girl_amount'))->toBe('100.00')
        ->and($response->json('data.room_service.house_amount'))->toBe('100.00');
});

it('settles girl room using calculated girl_amount not total_amount', function () {
    $token = pricingFixAdminToken();

    $roomServiceId = (int) test()->postJson(
        '/api/v1/room-services',
        pricingFixRoomServicePayload([
            'total_amount' => 200,
            'girl_percent' => 37.5,
        ]),
        nightposOperationalHeaders($token),
    )->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomServiceId}/finish", [], nightposOperationalHeaders($token))
        ->assertOk();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated();

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $roomServiceId)
        ->first();

    expect($item)->not->toBeNull()
        ->and($item->amount)->toBe('75.00');

    $service = RoomServiceModel::query()->find($roomServiceId);

    expect($service->girl_percent)->toBe('37.50')
        ->and($service->girl_amount)->toBe('75.00')
        ->and($service->house_amount)->toBe('125.00')
        ->and($service->total_amount)->toBe('200.00');
});

it('room does not define how much the girl earns on service create', function () {
    $token = pricingFixAdminToken();
    $girlId = pricingFixGirlId();

    $roomId = (int) test()->postJson('/api/v1/rooms', [
        'code' => 'PHYS-1',
        'name' => 'Solo física',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($token))->assertCreated()->json('data.room.id');

    test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 150,
        'girl_percent' => 40,
        'payment_method' => 'CASH',
        'duration_minutes' => 45,
    ], nightposOperationalHeaders($token))->assertCreated()
        ->assertJsonPath('data.room_service.girl_amount', '60.00')
        ->assertJsonPath('data.room_service.house_amount', '90.00')
        ->assertJsonPath('data.room_service.girl_percent', '40.00');

    $room = RoomModel::query()->find($roomId);

    expect($room->suggested_price)->toBeNull()
        ->and($room->default_duration_minutes)->toBeNull();
});
