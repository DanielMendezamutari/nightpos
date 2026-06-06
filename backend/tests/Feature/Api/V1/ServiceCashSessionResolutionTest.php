<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function serviceCashCashierToken(): string
{
    return nightposLoginPin('1234');
}

function serviceCashGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

it('cashier opens cash and registers room service', function () {
    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => serviceCashGirlId(),
        'total_amount' => 200,
        'girl_percent' => 50,
        'payment_method' => 'CASH',
    ]), nightposOperationalHeaders($token))->assertCreated();
});

it('cashier opens cash and registers bracelet', function () {
    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => serviceCashGirlId(),
        'quantity' => 1,
        'unit_price' => 25,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();
});

it('cashier opens cash and registers show', function () {
    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => serviceCashGirlId(),
        'show_type' => 'PRIVATE',
        'unit_price' => 100,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();
});

it('current cash session and service registration resolve the same session', function () {
    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    $current = test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.session.status', 'OPEN');

    $sessionId = (int) $current->json('data.session.id');

    $response = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => serviceCashGirlId(),
        'payment_method' => 'CASH',
    ]), nightposOperationalHeaders($token))->assertCreated();

    $serviceId = (int) $response->json('data.room_service.id');
    $service = RoomServiceModel::query()->find($serviceId);

    expect($service->cash_session_id)->toBe($sessionId);
});

it('does not register service with another user open cash session', function () {
    nightposOpenCashSession(nightposLoginPassword('admin.demo', 'AdminDemo123!'));
    $cashier = serviceCashCashierToken();

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonPath('data.session', null);

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => serviceCashGirlId(),
        'payment_method' => 'CASH',
    ]), nightposOperationalHeaders($cashier))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe abrir caja antes de registrar este servicio.');
});

it('does not register service with cash session from another branch', function () {
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')
        ->value('id');

    $otherBranch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ]);

    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => serviceCashGirlId(),
        'payment_method' => 'CASH',
    ]), nightposOperationalHeaders($token, 'NORTE'))
        ->assertForbidden();

    expect(CashSessionModel::query()
        ->where('status', 'OPEN')
        ->where('branch_id', $otherBranch->id)
        ->count())->toBe(0);
});

it('rejects services when cashier has no open session', function () {
    $token = serviceCashCashierToken();
    nightposEnsureShiftOpen();

    test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.session', null);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => serviceCashGirlId(),
        'quantity' => 1,
        'unit_price' => 10,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))
        ->assertStatus(422);

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => serviceCashGirlId(),
        'show_type' => 'STAGE',
        'unit_price' => 50,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))
        ->assertStatus(422);
});

it('service cash movement links to cashier session', function () {
    $token = serviceCashCashierToken();
    nightposOpenCashSession($token);

    $bracelet = test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => serviceCashGirlId(),
        'quantity' => 1,
        'unit_price' => 40,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertCreated();

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->json('data.session.id');

    expect(BraceletModel::query()->find($bracelet->json('data.bracelet.id'))->cash_session_id)->toBe($sessionId)
        ->and(ShowModel::query()->count())->toBe(0);
});
