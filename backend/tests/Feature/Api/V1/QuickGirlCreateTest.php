<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function quickGirlAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function quickGirlCashierToken(): string
{
    return nightposLoginPin('1234');
}

it('allows admin to quick create girl', function () {
    $response = test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Rápida Admin',
        'pin' => '4321',
        'notes' => 'Alta desde caja',
    ], nightposOperationalHeaders(quickGirlAdminToken()))->assertCreated();

    expect($response->json('data.girl.staff_role'))->toBe('GIRL')
        ->and($response->json('data.girl.can_receive_girl_commissions'))->toBeTrue()
        ->and($response->json('data.girl.status'))->toBe('active');
});

it('allows cashier to quick create girl', function () {
    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Rápida Cajera',
        'pin' => '4322',
    ], nightposOperationalHeaders(quickGirlCashierToken()))->assertCreated();
});

it('allows waiter with permission to quick create girl', function () {
    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Garzón Rápida',
    ], nightposOperationalHeaders(nightposLoginPin('5678')))->assertCreated();
});

it('denies cleaning from quick create girl', function () {
    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Hack Limpieza',
    ], nightposOperationalHeaders(nightposLoginPin('3333')))->assertForbidden();
});

it('sets girl staff profile and branch access', function () {
    $response = test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Perfil Test',
    ], nightposOperationalHeaders(quickGirlAdminToken()))->assertCreated();

    $girlId = (int) $response->json('data.girl.id');
    $profile = StaffProfileModel::query()->where('user_id', $girlId)->first();

    expect($profile?->staff_role)->toBe('GIRL')
        ->and($profile?->can_receive_girl_commissions)->toBeTrue()
        ->and($profile?->status)->toBe('active')
        ->and($response->json('data.girl.accessible_branch_ids'))->not->toBeEmpty();
});

it('stores pin hashed not plain', function () {
    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica PIN Test',
        'pin' => '7890',
    ], nightposOperationalHeaders(quickGirlAdminToken()))->assertCreated();

    $user = UserModel::query()->where('name', 'Chica PIN Test')->first();

    expect($user?->pin_hash)->not->toBeNull()
        ->and($user?->pin_hash)->not->toBe('7890')
        ->and(Hash::check('7890', (string) $user?->pin_hash))->toBeTrue();
});

it('rejects duplicate active girl name in branch', function () {
    $token = quickGirlAdminToken();

    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Duplicada',
    ], nightposOperationalHeaders($token))->assertCreated();

    test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'chica duplicada',
    ], nightposOperationalHeaders($token))->assertStatus(422);
});

it('lists operational girls for cashier', function () {
    $response = test()->getJson('/api/v1/staff/girls', nightposOperationalHeaders(quickGirlCashierToken()))
        ->assertOk();

    expect($response->json('data.items'))->not->toBeEmpty();
});

it('quick created girl can receive room service', function () {
    $create = test()->postJson('/api/v1/staff/quick-girls', [
        'name' => 'Chica Pieza Nueva',
    ], nightposOperationalHeaders(quickGirlAdminToken()))->assertCreated();

    $girlId = (int) $create->json('data.girl.id');
    $roomId = (int) \App\Infrastructure\Persistence\Eloquent\Models\RoomModel::query()->where('code', 'P1')->value('id');

    nightposOpenCashSession(quickGirlAdminToken());

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_id' => $roomId,
        'total_amount' => 100,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders(quickGirlAdminToken()))->assertCreated();
});
