<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
    $cashier = nightposLoginPin('1234');
    nightposOpenCashSession($cashier, 100, false);
});

function cleaningSettlementsAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function cleaningSettlementsCashierToken(): string
{
    return nightposLoginPin('1234');
}

function cleaningSettlementsCleaningToken(): string
{
    return nightposLoginPin('3333');
}

function cleaningSettlementsGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function cleaningSettlementsCreateRoom(string $adminToken, string $code = 'CLN1'): int
{
    return (int) test()->postJson('/api/v1/rooms', [
        'code' => $code,
        'name' => "Room {$code}",
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($adminToken))
        ->assertCreated()
        ->json('data.room.id');
}

function cleaningSettlementsCreateFinishedService(
    string $adminToken,
    int $roomId,
    ?string $startedAt = null,
): int {
    $opsToken = cleaningSettlementsCashierToken();
    nightposOpenCashSession($opsToken, 100, false);
    $payload = nightposRoomServicePayload([
        'girl_user_id' => cleaningSettlementsGirlId(),
        'room_id' => $roomId,
        'room_label' => 'Pieza Test',
        'total_amount' => 100,
        'duration_minutes' => 30,
    ]);

    if ($startedAt !== null) {
        $payload['started_at'] = $startedAt;
    }

    $serviceId = (int) test()->postJson('/api/v1/room-services', $payload, nightposOperationalHeaders($opsToken))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(cleaningSettlementsCleaningToken()))
        ->assertOk();

    return $serviceId;
}

function cleaningSettlementsMarkRoomClean(int $roomId): void
{
    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(cleaningSettlementsCleaningToken()))
        ->assertOk();
}

function cleaningSettlementsGenerate(): void
{
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(cleaningSettlementsAdminToken()))
        ->assertCreated();
}

function cleaningSettlementsCreateCleaningUser(string $base = '30', string $room = '10', string $username = 'rosa.limpieza'): int
{
    $admin = cleaningSettlementsAdminToken();

    $response = test()->postJson('/api/v1/admin/users', [
        'name' => 'Rosa Limpieza',
        'username' => $username,
        'pin' => '4455',
        'staff_role' => 'CLEANING',
        'cleaning_base_amount' => (float) $base,
        'cleaning_room_amount' => (float) $room,
        'accessible_branch_ids' => [
            (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        ],
    ], nightposOperationalHeaders($admin));

    $response->assertCreated();

    return (int) $response->json('data.user.id');
}

it('admin creates cleaning user with base 30 and room pay 10', function () {
    $userId = cleaningSettlementsCreateCleaningUser('30', '10');

    $profile = StaffProfileModel::query()->where('user_id', $userId)->first();

    expect($profile?->staff_role)->toBe('CLEANING')
        ->and((string) $profile?->cleaning_base_amount)->toBe('30.00')
        ->and((string) $profile?->cleaning_room_amount)->toBe('10.00');
});

it('admin edits cleaning user base and room pay', function () {
    $userId = cleaningSettlementsCreateCleaningUser('30', '10', 'rosa.edit');
    $admin = cleaningSettlementsAdminToken();

    test()->putJson("/api/v1/admin/users/{$userId}", [
        'name' => 'Rosa Limpieza',
        'username' => 'rosa.edit',
        'status' => 'active',
        'staff_role' => 'CLEANING',
        'cleaning_base_amount' => 35,
        'cleaning_room_amount' => 12,
        'accessible_branch_ids' => [
            (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        ],
    ], nightposOperationalHeaders($admin))->assertOk();

    $profile = StaffProfileModel::query()->where('user_id', $userId)->first();

    expect((string) $profile?->cleaning_base_amount)->toBe('35.00')
        ->and((string) $profile?->cleaning_room_amount)->toBe('12.00');
});

it('cleaning pay fields do not apply to waiter', function () {
    $admin = cleaningSettlementsAdminToken();
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    test()->putJson("/api/v1/admin/users/{$waiterId}", [
        'name' => 'Garzón Demo',
        'username' => 'garzon.demo',
        'status' => 'active',
        'staff_role' => 'WAITER',
        'waiter_commission_percent' => 5,
        'cleaning_base_amount' => 99,
        'cleaning_room_amount' => 99,
        'accessible_branch_ids' => [
            (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        ],
    ], nightposOperationalHeaders($admin))->assertOk();

    $profile = StaffProfileModel::query()->where('user_id', $waiterId)->first();

    expect($profile?->cleaning_base_amount)->toBeNull()
        ->and($profile?->cleaning_room_amount)->toBeNull();
});

it('mark room clean creates cleaning task with profile amount', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin);
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    $task = CleaningTaskModel::query()->where('room_id', $roomId)->first();

    expect($task)->not->toBeNull()
        ->and((string) $task->amount)->toBe('10.00')
        ->and($task->status)->toBe('DONE');
});

it('same room with different room services creates two cleaning tasks', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN2');

    $service1 = cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    $service2 = cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    expect(CleaningTaskModel::query()->where('room_id', $roomId)->count())->toBe(2)
        ->and(CleaningTaskModel::query()->pluck('room_service_id')->all())->toBe([$service1, $service2]);
});

it('same room service does not duplicate cleaning task', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN3');
    $serviceId = cleaningSettlementsCreateFinishedService($admin, $roomId);

    CleaningTaskModel::query()->create([
        'tenant_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id'),
        'branch_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'official_shift_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()->where('status', 'OPEN')->value('id'),
        'room_id' => $roomId,
        'room_service_id' => $serviceId,
        'cleaning_user_id' => (int) UserModel::query()->where('username', 'limpieza.demo')->value('id'),
        'amount' => 10,
        'status' => 'DONE',
        'cleaned_at' => now(),
    ]);

    cleaningSettlementsMarkRoomClean($roomId);

    expect(CleaningTaskModel::query()->where('room_service_id', $serviceId)->count())->toBe(1);
});

it('adds cleaning base only once per shift when regenerating settlements', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN4');
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    cleaningSettlementsGenerate();
    cleaningSettlementsGenerate();

    $cleaningUserId = (int) UserModel::query()->where('username', 'limpieza.demo')->value('id');

    expect(StaffSettlementItemModel::query()
        ->where('source_type', 'CLEANING_BASE')
        ->whereHas('settlement', fn ($q) => $q->where('staff_user_id', $cleaningUserId))
        ->count())->toBe(1);
});

it('cleaning settlement total equals base plus cleaned rooms', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN5');
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    cleaningSettlementsGenerate();

    $cleaningUserId = (int) UserModel::query()->where('username', 'limpieza.demo')->value('id');
    $settlement = StaffSettlementModel::query()->where('staff_user_id', $cleaningUserId)->where('settlement_type', 'CLEANING')->first();

    expect($settlement)->not->toBeNull()
        ->and($settlement->total_amount)->toBe('40.00');
});

it('paying cleaning settlement creates cash expense', function () {
    $admin = cleaningSettlementsAdminToken();

    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN6');
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);
    cleaningSettlementsGenerate();

    $cashier = cleaningSettlementsCashierToken();
    $settlementId = (int) StaffSettlementModel::query()->where('settlement_type', 'CLEANING')->value('id');
    $before = CashMovementModel::query()->where('movement_type', 'EXPENSE')->count();

    nightposOpenCashSession($cashier);

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($cashier))
        ->assertOk();

    expect(CashMovementModel::query()->where('movement_type', 'EXPENSE')->count())->toBe($before + 1);
});

it('cannot pay cleaning settlement without open cash session', function () {
    $admin = cleaningSettlementsAdminToken();

    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN7');
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);
    cleaningSettlementsGenerate();

    \App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel::query()
        ->where('status', 'OPEN')
        ->update(['status' => 'CLOSED', 'closed_at' => now()]);

    $settlementId = (int) StaffSettlementModel::query()->where('settlement_type', 'CLEANING')->value('id');
    $cashier = cleaningSettlementsCashierToken();

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($cashier))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja para pagar esta liquidación.');
});

it('cleaning mobile shift earnings shows accumulated total', function () {
    $admin = cleaningSettlementsAdminToken();
    $roomId = cleaningSettlementsCreateRoom($admin, 'CLN8');
    cleaningSettlementsCreateFinishedService($admin, $roomId);
    cleaningSettlementsMarkRoomClean($roomId);

    $response = test()->getJson('/api/v1/cleaning/shift-earnings', nightposOperationalHeaders(cleaningSettlementsCleaningToken()))
        ->assertOk();

    expect($response->json('data.earnings.base_amount'))->toBe('30.00')
        ->and($response->json('data.earnings.room_amount'))->toBe('10.00')
        ->and($response->json('data.earnings.rooms_cleaned'))->toBe(1)
        ->and($response->json('data.earnings.rooms_total'))->toBe('10.00')
        ->and($response->json('data.earnings.total_accumulated'))->toBe('40.00');
});
