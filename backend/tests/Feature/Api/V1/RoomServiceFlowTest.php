<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposOpenCashSession(roomFlowAdminToken());
});

function roomFlowAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function roomFlowGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

it('follows full room service lifecycle without auto cleaning on due', function () {
    Carbon::setTestNow('2026-06-02 20:00:00');
    $adminToken = roomFlowAdminToken();
    $girlId = roomFlowGirlId();

    $room = test()->postJson('/api/v1/rooms', [
        'code' => 'FLOW1',
        'name' => 'Flujo Test',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($adminToken))->assertCreated();

    $roomId = (int) $room->json('data.room.id');
    expect($room->json('data.room.status'))->toBe('AVAILABLE');

    $service = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 70,
        'duration_minutes' => 5,
        'started_at' => '2026-06-02 20:00:00',
    ]), nightposOperationalHeaders($adminToken))->assertCreated();

    $serviceId = (int) $service->json('data.room_service.id');

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED')
        ->and($service->json('data.room_service.status'))->toBe('ACTIVE');

    Carbon::setTestNow('2026-06-02 20:06:00');

    $this->artisan('room-services:check-due')->assertSuccessful();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('OCCUPIED')
        ->and(RoomServiceModel::query()->find($serviceId)?->status)->toBe('DUE');

    expect(NotificationModel::query()->where('type', 'ROOM_SERVICE_DUE')->count())->toBe(2)
        ->and(NotificationModel::query()->where('role_target', 'CLEANING')->exists())->toBeTrue()
        ->and(NotificationModel::query()->where('role_target', 'CASHIER')->exists())->toBeTrue();

    $control = test()->getJson('/api/v1/cleaning/dashboard', nightposOperationalHeaders(nightposLoginPin('3333')))
        ->assertOk();

    expect($control->json('data.due_count'))->toBe(1)
        ->and(collect($control->json('data.due'))->pluck('id'))->toContain($serviceId)
        ->and(collect($control->json('data.active'))->pluck('id'))->not->toContain($serviceId);

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(nightposLoginPin('3333')))
        ->assertOk()
        ->assertJsonPath('data.room_service.status', 'FINISHED');

    expect(RoomModel::query()->find($roomId)?->status)->toBe('CLEANING')
        ->and(RoomServiceModel::query()->find($serviceId)?->status)->toBe('FINISHED');

    $controlAfterFinish = test()->getJson('/api/v1/cleaning/dashboard', nightposOperationalHeaders(nightposLoginPin('3333')))
        ->assertOk();

    expect(collect($controlAfterFinish->json('data.cleaning'))->pluck('id'))->toContain($roomId)
        ->and(collect($controlAfterFinish->json('data.cleaning'))->pluck('code'))->toContain('FLOW1');

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(nightposLoginPin('3333')))
        ->assertOk();

    expect(RoomModel::query()->find($roomId)?->status)->toBe('AVAILABLE');

    Carbon::setTestNow();
});
