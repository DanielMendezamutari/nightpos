<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposOpenCashSession(nightposLoginPassword('admin.demo', 'AdminDemo123!'));
});

function phase17GirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function phase17CreateActiveRoom(string $adminToken, int $girlId, int $durationMinutes = 60, ?string $startedAt = null): int
{
    $payload = nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'duration_minutes' => $durationMinutes,
    ]);

    if ($startedAt !== null) {
        $payload['started_at'] = $startedAt;
    }

    return (int) test()->postJson('/api/v1/room-services', $payload, nightposOperationalHeaders($adminToken))
        ->assertCreated()
        ->json('data.room_service.id');
}

it('registers bracelet without creating room service', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase17GirlId();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 50,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($adminToken))->assertCreated();

    expect(RoomServiceModel::query()->count())->toBe(0);
});

it('calculates expected_ends_at on room service create', function () {
    Carbon::setTestNow('2026-06-02 15:00:00');
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase17GirlId();

    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Suite 1',
        'total_amount' => 80,
        'girl_percent' => 100,
        'duration_minutes' => 45,
        'started_at' => '2026-06-02 15:00:00',
    ]), nightposOperationalHeaders($adminToken))->assertCreated();

    expect($response->json('data.room_service.status'))->toBe('ACTIVE')
        ->and($response->json('data.room_service.expected_ends_at'))->toBe('2026-06-02 15:45:00')
        ->and($response->json('data.room_service.settlement_source_type'))->toBe('GIRL_ROOM');

    Carbon::setTestNow();
});

it('lists active and due room services', function () {
    Carbon::setTestNow('2026-06-02 18:00:00');
    $girlId = phase17GirlId();

    phase17CreateActiveRoom(nightposLoginPassword('admin.demo', 'AdminDemo123!'), $girlId, 60, '2026-06-02 17:30:00');
    phase17CreateActiveRoom(nightposLoginPassword('admin.demo', 'AdminDemo123!'), $girlId, 30, '2026-06-02 17:20:00');
    $cleaningToken = nightposLoginPin('3333');

    test()->getJson('/api/v1/cleaning/room-services/active', nightposOperationalHeaders($cleaningToken))
        ->assertOk()
        ->assertJsonPath('data.items.0.status', 'ACTIVE');

    $due = test()->getJson('/api/v1/cleaning/room-services/due', nightposOperationalHeaders($cleaningToken))
        ->assertOk();

    expect($due->json('data.due_count'))->toBeGreaterThanOrEqual(1);

    Carbon::setTestNow();
});

it('command creates cleaning notification for due room', function () {
    Carbon::setTestNow('2026-06-02 18:00:00');
    $girlId = phase17GirlId();

    phase17CreateActiveRoom(nightposLoginPassword('admin.demo', 'AdminDemo123!'), $girlId, 30, '2026-06-02 17:00:00');

    $this->artisan('room-services:check-due')->assertSuccessful();

    $notification = NotificationModel::query()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('ROOM_SERVICE_DUE')
        ->and($notification->source_type)->toBe('ROOM_SERVICE');

    expect(NotificationModel::query()->where('type', 'ROOM_SERVICE_DUE')->count())->toBe(2)
        ->and(NotificationModel::query()->where('role_target', 'CLEANING')->exists())->toBeTrue()
        ->and(NotificationModel::query()->where('role_target', 'CASHIER')->exists())->toBeTrue();

    expect(RoomServiceModel::query()->value('status'))->toBe('DUE')
        ->and(RoomServiceModel::query()->value('alert_sent_at'))->not->toBeNull();

    $this->artisan('room-services:check-due')->assertSuccessful();

    expect(NotificationModel::query()->where('type', 'ROOM_SERVICE_DUE')->count())->toBe(2);

    Carbon::setTestNow();
});

it('cleaning sees cleaning notifications for due room service', function () {
    Carbon::setTestNow('2026-06-02 18:00:00');

    phase17CreateActiveRoom(nightposLoginPassword('admin.demo', 'AdminDemo123!'), phase17GirlId(), 15, '2026-06-02 17:40:00');
    $this->artisan('room-services:check-due')->assertSuccessful();

    test()->getJson('/api/v1/notifications', nightposOperationalHeaders(nightposLoginPin('3333')))
        ->assertOk()
        ->assertJsonCount(1, 'data.notifications')
        ->assertJsonPath('data.notifications.0.type', 'ROOM_SERVICE_DUE');

    $cashierNotifications = test()->getJson('/api/v1/notifications', nightposOperationalHeaders(nightposLoginPin('1234')))
        ->assertOk()
        ->json('data.notifications');

    expect(collect($cashierNotifications)->where('type', 'ROOM_SERVICE_DUE')->count())->toBeGreaterThanOrEqual(1)
        ->and(collect($cashierNotifications)->pluck('role_target'))->toContain('CASHIER');

    Carbon::setTestNow();
});

it('marks notification read and checks room', function () {
    Carbon::setTestNow('2026-06-02 18:00:00');
    $roomId = phase17CreateActiveRoom(
        nightposLoginPassword('admin.demo', 'AdminDemo123!'),
        phase17GirlId(),
        10,
        '2026-06-02 17:50:00',
    );
    $cleaningToken = nightposLoginPin('3333');

    $this->artisan('room-services:check-due')->assertSuccessful();

    $notificationId = (int) NotificationModel::query()->value('id');

    test()->postJson("/api/v1/cleaning/room-services/{$roomId}/check", [], nightposOperationalHeaders($cleaningToken))
        ->assertOk()
        ->assertJsonPath('data.room_service.checked_at', fn ($v) => $v !== null);

    expect(NotificationModel::query()->find($notificationId)->status)->toBe('READ');

    Carbon::setTestNow();
});

it('finishes room and unread count endpoint works', function () {
    $roomId = phase17CreateActiveRoom(
        nightposLoginPassword('admin.demo', 'AdminDemo123!'),
        phase17GirlId(),
        60,
    );
    $cleaningToken = nightposLoginPin('3333');

    test()->postJson("/api/v1/cleaning/room-services/{$roomId}/finish", [], nightposOperationalHeaders($cleaningToken))
        ->assertOk()
        ->assertJsonPath('data.room_service.status', 'FINISHED');

    test()->getJson('/api/v1/notifications/unread-count', nightposOperationalHeaders($cleaningToken))
        ->assertOk()
        ->assertJsonStructure(['data' => ['unread_count']]);
});

it('does not settle active or cancelled room only finished', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase17GirlId();

    $activeId = phase17CreateActiveRoom($adminToken, $girlId, 120);

    $cancelled = RoomServiceModel::query()->find($activeId);
    $cancelled->update(['status' => 'CANCELLED', 'ended_at' => now()]);

    $finishedId = phase17CreateActiveRoom($adminToken, $girlId, 30);
    test()->postJson("/api/v1/room-services/{$finishedId}/finish", [], nightposOperationalHeaders($adminToken))->assertOk();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($adminToken))->assertCreated();

    expect(\App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->count())->toBe(1);
});
