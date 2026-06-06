<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function rscAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function rscCleaningToken(): string
{
    return nightposLoginPin('3333');
}

function rscGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function rscCreateRoom(string $adminToken, string $code = 'RSC1'): int
{
    return (int) test()->postJson('/api/v1/rooms', [
        'code' => $code,
        'name' => "Room {$code}",
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($adminToken))
        ->assertCreated()
        ->json('data.room.id');
}

// ─── Test 1: Correct amount distribution ──────────────────────────────────────

it('room service with cleaning deduction calculates amounts correctly', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $roomId = rscCreateRoom($admin);

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => rscGirlId(),
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'cleaning_amount' => 10,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    expect((float) $rs['gross_girl_amount'])->toBe(100.0);
    expect((float) $rs['girl_amount'])->toBe(90.0);
    expect((float) $rs['cleaning_amount'])->toBe(10.0);
    expect((float) $rs['house_amount'])->toBe(100.0);
});

// ─── Test 2: cleaning_amount = 0 means no deduction ──────────────────────────

it('room service without cleaning amount has girl_amount equal to gross', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $roomId = rscCreateRoom($admin, 'RSC2');

    $response = test()->postJson('/api/v1/room-services', [
        'girl_user_id' => rscGirlId(),
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated();

    $rs = $response->json('data.room_service');

    expect((float) $rs['gross_girl_amount'])->toBe(100.0);
    expect((float) $rs['girl_amount'])->toBe(100.0);
    expect((float) $rs['cleaning_amount'])->toBe(0.0);
});

// ─── Test 3: cleaning_amount > gross_girl_amount → 422 ───────────────────────

it('cleaning amount exceeding gross girl amount returns 422', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $roomId = rscCreateRoom($admin, 'RSC3');

    test()->postJson('/api/v1/room-services', [
        'girl_user_id' => rscGirlId(),
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,   // gross_girl = 100
        'cleaning_amount' => 150, // 150 > 100 → invalid
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertStatus(422);
});

// ─── Test 4: Marking room clean uses room_service.cleaning_amount ─────────────

it('marking room clean creates cleaning task with room service cleaning amount', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $roomId = rscCreateRoom($admin, 'RSC4');

    $serviceId = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => rscGirlId(),
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'cleaning_amount' => 10,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    // Finish the service
    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    // Mark room clean
    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    $task = CleaningTaskModel::query()->where('room_service_id', $serviceId)->first();

    expect($task)->not->toBeNull();
    expect((float) $task->amount)->toBe(10.0);
});

// ─── Test 5: Girl settlement uses net girl_amount ─────────────────────────────

it('girl settlement item for room service uses net girl amount', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $girlId = rscGirlId();
    $roomId = rscCreateRoom($admin, 'RSC5');

    $serviceId = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'cleaning_amount' => 10,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    // Finish service as cleaning user
    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    // Fresh admin token for settlement generation
    $freshAdmin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($freshAdmin))
        ->assertCreated();

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $serviceId)
        ->first();

    expect($item)->not->toBeNull();
    expect((float) $item->amount)->toBe(90.0); // net girl amount
});

// ─── Test 6: Cleaning settlement uses cleaning_task.amount (= room_service.cleaning_amount) ──

it('cleaning settlement item for room service uses cleaning task amount from room service', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $girlId = rscGirlId();
    $roomId = rscCreateRoom($admin, 'RSC6');

    $serviceId = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'cleaning_amount' => 10,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    // Finish service and mark room clean
    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    // Fresh admin token for settlement generation
    $freshAdmin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($freshAdmin))
        ->assertCreated();

    $task = CleaningTaskModel::query()->where('room_service_id', $serviceId)->first();

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'CLEANING_ROOM')
        ->where('source_id', $task->id)
        ->first();

    expect($item)->not->toBeNull();
    expect((float) $item->amount)->toBe(10.0);
});

// ─── Test 7: Second use of the same room generates separate cleaning task ─────

it('using a room twice generates two independent cleaning tasks', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $girlId = rscGirlId();
    $roomId = rscCreateRoom($admin, 'RSC7');

    // First service
    $service1 = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 200,
        'girl_percent' => 50,
        'cleaning_amount' => 10,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$service1}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    // Re-login admin for second room service (room is now AVAILABLE again)
    $admin2 = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    // Second service on same room
    $service2 = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 150,
        'girl_percent' => 60,
        'cleaning_amount' => 15,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin2))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$service2}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    $tasks = CleaningTaskModel::query()->whereIn('room_service_id', [$service1, $service2])->get();

    expect($tasks->count())->toBe(2);
    expect((float) $tasks->where('room_service_id', $service1)->first()->amount)->toBe(10.0);
    expect((float) $tasks->where('room_service_id', $service2)->first()->amount)->toBe(15.0);
});

// ─── Test 8: Room service with cleaning_amount = 0 keeps original behavior ───

it('room service with zero cleaning amount uses profile fallback for cleaning task', function () {
    $admin = rscAdminToken();
    nightposOpenCashSession($admin);

    $girlId = rscGirlId();
    $roomId = rscCreateRoom($admin, 'RSC8');

    $serviceId = (int) test()->postJson('/api/v1/room-services', [
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 100,
        'girl_percent' => 50,
        'cleaning_amount' => 0,
        'payment_method' => 'CASH',
        'duration_minutes' => 30,
    ], nightposOperationalHeaders($admin))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(rscCleaningToken()))
        ->assertOk();

    $task = CleaningTaskModel::query()->where('room_service_id', $serviceId)->first();

    expect($task)->not->toBeNull();
    // Falls back to profile/config amount (not 0)
    expect((float) $task->amount)->toBeGreaterThan(0);
});
