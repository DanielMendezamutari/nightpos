<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function settlementsCashUiAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function settlementsCashUiWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function settlementsCashUiCashierToken(): string
{
    return nightposLoginPin('1234');
}

function settlementsCashUiCleaningToken(): string
{
    return nightposLoginPin('3333');
}

function settlementsCashUiCreateCleaningSettlement(string $adminToken): int
{
    $roomId = (int) test()->postJson('/api/v1/rooms', [
        'code' => 'SCU1',
        'name' => 'Room SCU1',
        'room_type' => 'STANDARD',
    ], nightposOperationalHeaders($adminToken))
        ->assertCreated()
        ->json('data.room.id');

    $opsToken = settlementsCashUiCashierToken();
    nightposOpenCashSession($opsToken);

    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'room_label' => 'Pieza Test',
        'total_amount' => 100,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($opsToken))
        ->assertCreated()
        ->json('data.room_service.id');

    test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders(settlementsCashUiCleaningToken()))
        ->assertOk();

    test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders(settlementsCashUiCleaningToken()))
        ->assertOk();

    auth('api')->forgetUser();
    test()->flushHeaders();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(settlementsCashUiCashierToken()))
        ->assertCreated();

    $settlement = StaffSettlementModel::query()
        ->where('settlement_type', 'CLEANING')
        ->where('status', 'PENDING')
        ->first();

    expect($settlement)->not->toBeNull();

    return (int) $settlement->id;
}

it('branch admin can view current shift settlements', function () {
    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(settlementsCashUiAdminToken()))
        ->assertOk()
        ->assertJsonStructure(['data' => ['shift', 'summary', 'waiters', 'girls', 'cleaning']]);
});

it('branch admin can consult pending sources', function () {
    test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders(settlementsCashUiAdminToken()))
        ->assertOk();
});

it('branch admin can generate settlements', function () {
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(settlementsCashUiAdminToken()))
        ->assertCreated();
});

it('paying cleaning settlement requires open cash session for current user', function () {
    $admin = settlementsCashUiAdminToken();
    $settlementId = settlementsCashUiCreateCleaningSettlement($admin);

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja para pagar esta liquidación.');
});

it('paying cleaning settlement with open cash creates expense', function () {
    $admin = settlementsCashUiAdminToken();
    $settlementId = settlementsCashUiCreateCleaningSettlement($admin);

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);
    nightposOpenCashSession($admin);

    $before = CashMovementModel::query()->where('movement_type', 'EXPENSE')->count();

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertOk();

    expect(CashMovementModel::query()->where('movement_type', 'EXPENSE')->count())->toBe($before + 1);
});

it('user without settlement permission cannot access settlements', function () {
    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(settlementsCashUiCleaningToken()))
        ->assertForbidden();
});

it('admin cash session current reflects open session', function () {
    $admin = settlementsCashUiAdminToken();
    nightposOpenCashSession($admin);

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.session.status', 'OPEN');
});

it('expected_cash decreases after paying cleaning settlement', function () {
    $admin = settlementsCashUiAdminToken();
    $settlementId = settlementsCashUiCreateCleaningSettlement($admin);

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);
    nightposOpenCashSession($admin);

    $before = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.session.financial_summary.expected_cash');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertOk();

    $after = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.session.financial_summary.expected_cash');

    expect((float) $after)->toBeLessThan((float) $before);
});

it('cannot pay cleaning settlement twice', function () {
    $admin = settlementsCashUiAdminToken();
    $settlementId = settlementsCashUiCreateCleaningSettlement($admin);

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);
    nightposOpenCashSession($admin);

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertOk();

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertStatus(422);
});

it('current shift settlements includes pending cleaning total', function () {
    $admin = settlementsCashUiAdminToken();
    settlementsCashUiCreateCleaningSettlement($admin);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(settlementsCashUiAdminToken()))
        ->assertOk();

    $cleaning = $response->json('data.cleaning');

    expect($cleaning)->not->toBeNull();
    expect(count($cleaning))->toBeGreaterThan(0);

    $hasPending = collect($cleaning)->contains(fn ($s) => $s['status'] === 'PENDING');

    expect($hasPending)->toBeTrue();
});

it('cash session expenses include cleaning settlement payment', function () {
    $admin = settlementsCashUiAdminToken();
    $settlementId = settlementsCashUiCreateCleaningSettlement($admin);

    CashSessionModel::query()->where('status', 'OPEN')->update(['status' => 'CLOSED', 'closed_at' => now()]);
    nightposOpenCashSession($admin);

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($admin))
        ->assertOk();

    $movements = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.session.financial_summary.total_manual_expense');

    expect((float) $movements)->toBeGreaterThan(0);
});
