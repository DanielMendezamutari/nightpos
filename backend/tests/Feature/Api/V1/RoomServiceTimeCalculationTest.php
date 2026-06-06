<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function tcAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function tcGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function tcCreateRoom(string $adminToken, string $code = 'TC1'): int
{
    return (int) test()->postJson('/api/v1/rooms', [
        'code' => $code,
        'name' => "Room {$code}",
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($adminToken))
        ->assertCreated()
        ->json('data.room.id');
}

function tcNow(): string
{
    return Carbon::now(config('app.timezone', 'America/La_Paz'))->format('Y-m-d\TH:i');
}

function tcPast(int $minutesAgo): string
{
    return Carbon::now(config('app.timezone', 'America/La_Paz'))
        ->subMinutes($minutesAgo)
        ->format('Y-m-d\TH:i');
}

function tcFuture(int $minutesAhead): string
{
    return Carbon::now(config('app.timezone', 'America/La_Paz'))
        ->addMinutes($minutesAhead)
        ->format('Y-m-d\TH:i');
}

// ─── Test 1: Pieza creada ahora → ACTIVE, no vencida ─────────────────────────

it('room service created now with 30 min duration is ACTIVE and not due', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin);

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
        'started_at' => tcNow(),
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    expect($rs['status'])->toBe('ACTIVE');
    expect($rs['is_due'])->toBeFalse();
    expect($rs['remaining_minutes'])->toBeGreaterThan(0);
    expect($rs['alert_sent_at'])->toBeNull();
});

// ─── Test 2: Pieza creada ahora NO aparece en la lista "due" ────────────────

it('room service created now does not appear in due list', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC2');

    test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
        'started_at' => tcNow(),
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $dueItems = test()->getJson('/api/v1/room-services/due', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.room_services');

    $dueIds = collect($dueItems)->pluck('room_id')->all();

    expect($dueIds)->not->toContain($roomId);
});

// ─── Test 3: Pieza con started_at hace 40 min y duración 30 → vencida ────────

it('room service started 40 minutes ago with 30 min duration appears as due', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC3');

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
        'started_at' => tcPast(40),
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    expect($rs['status'])->toBe('ACTIVE');
    expect($rs['is_due'])->toBeTrue();
    expect($rs['remaining_minutes'])->toBe(0);
});

// ─── Test 4: Pieza con started_at futuro → no vencida ─────────────────────────

it('room service with future started_at is not due', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC4');

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
        'started_at' => tcFuture(10),
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    expect($rs['is_due'])->toBeFalse();
    expect($rs['remaining_minutes'])->toBeGreaterThan(30); // 10 future + 30 duration
});

// ─── Test 5: duration_minutes = 0 → 422 ──────────────────────────────────────

it('room service with zero duration returns 422', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC5');

    test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 0,
    ], nightposOperationalHeaders($admin))
        ->assertStatus(422);
});

// ─── Test 6: alert_sent_at null al crear ────────────────────────────────────

it('newly created room service has null alert_sent_at', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC6');

    $serviceId = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 60,
        'started_at' => tcNow(),
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    $model = RoomServiceModel::query()->find($serviceId);

    expect($model?->alert_sent_at)->toBeNull();
    expect($model?->status)->toBe('ACTIVE');
});

// ─── Test 7: expected_ends_at correcto (started_at + duration) ───────────────

it('expected_ends_at equals started_at plus duration_minutes', function () {
    $admin = tcAdminToken();
    nightposOpenCashSession($admin);

    $roomId = tcCreateRoom($admin, 'TC7');
    $tz = config('app.timezone', 'America/La_Paz');
    $startedAt = Carbon::now($tz)->startOfMinute();

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => tcGirlId(),
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 45,
        'started_at' => $startedAt->format('Y-m-d\TH:i'),
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    $expectedEndsAt = Carbon::parse($rs['expected_ends_at'], $tz);
    $diffMinutes = (int) $startedAt->diffInMinutes($expectedEndsAt);

    expect($diffMinutes)->toBe(45);
});
