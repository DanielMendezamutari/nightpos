<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    Carbon::setTestNow('2026-06-02 14:00:00');
    nightposEnsureShiftOpen();
});

function phase15AdminToken(): string
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenCashSession($token);

    return $token;
}

afterEach(function () {
    Carbon::setTestNow();
});

function phase15GirlUserId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function phase15CreateExtraGirl(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $roleId = (int) RoleModel::query()->where('slug', 'waiter')->value('id');

    $girl = UserModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'role_id' => $roleId,
        'name' => 'Chica Extra',
        'username' => 'chica.extra',
        'status' => 'active',
    ]);

    StaffProfileModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'user_id' => $girl->id,
        'staff_role' => 'GIRL',
        'can_receive_girl_commissions' => true,
        'status' => 'active',
    ]);

    return (int) $girl->id;
}

it('registers bracelet linked to current shift', function () {
    $admin = phase15AdminToken();
    $girlId = phase15GirlUserId();

    $response = test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 3,
        'unit_price' => 25,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertCreated();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    expect($response->json('data.bracelet.official_shift_id'))->toBe($shiftId)
        ->and($response->json('data.bracelet.total_amount'))->toBe('75.00')
        ->and($response->json('data.bracelet.settlement_source_type'))->toBe('GIRL_BRACELET');

    expect(BraceletModel::query()->count())->toBe(1);
});

it('registers room service and show', function () {
    $admin = phase15AdminToken();
    $girlId = phase15GirlUserId();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza 12',
        'total_amount' => 150,
        'duration_minutes' => 90,
    ]), nightposOperationalHeaders($admin))->assertCreated();

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => $girlId,
        'show_type' => 'STAGE',
        'unit_price' => 200,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertCreated();

    expect(RoomServiceModel::query()->value('room_label'))->toBe('Pieza 12');
    expect(ShowModel::query()->value('show_type'))->toBe('STAGE');
});

it('lists current shift bracelets with summary', function () {
    $admin = phase15AdminToken();
    $girlId = phase15GirlUserId();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 2,
        'unit_price' => 10,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));

    $response = test()->getJson('/api/v1/bracelets', nightposOperationalHeaders($admin))->assertOk();

    expect($response->json('data.summary.total_amount'))->toBe('20.00')
        ->and($response->json('data.summary.quantity'))->toBe(2)
        ->and($response->json('data.items'))->toHaveCount(1)
        ->and($response->json('data.shift.id'))->not->toBeNull();
});

it('requires girl user for bracelet', function () {
    $admin = phase15AdminToken();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => (int) UserModel::query()->where('username', 'garzon.demo')->value('id'),
        'quantity' => 1,
        'unit_price' => 10,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertStatus(422);
});

it('denies waiter without create permission from registering bracelet', function () {
    $waiter = nightposLoginPin('5678');
    $girlId = phase15GirlUserId();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 10,
    ], nightposOperationalHeaders($waiter))->assertForbidden();
});

it('allows waiter with bracelets.create permission', function () {
    $role = RoleModel::query()->where('slug', 'waiter')->first();
    $permIds = PermissionModel::query()
        ->whereIn('slug', ['bracelets.create', 'cash.access'])
        ->pluck('id');
    $role->permissions()->syncWithoutDetaching($permIds);

    $waiter = nightposLoginPin('5678');
    nightposOpenCashSession($waiter);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => phase15GirlUserId(),
        'quantity' => 1,
        'unit_price' => 15,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($waiter))->assertCreated();
});

it('does not expose bracelet from another tenant', function () {
    $admin = phase15AdminToken();

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra',
        'slug' => 'otra-girl-income',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $foreignShift = OfficialShiftModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => 1,
        'name' => 'Turno Ajeno',
        'shift_type' => 'DAY',
        'business_date' => '2026-06-02',
        'starts_at' => now(),
        'ends_at' => now()->addHours(12),
        'status' => 'OPEN',
        'opened_by_user_id' => 1,
        'opened_at' => now(),
    ]);

    $foreign = BraceletModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => 1,
        'official_shift_id' => $foreignShift->id,
        'girl_user_id' => phase15GirlUserId(),
        'quantity' => 1,
        'unit_price' => 10,
        'total_amount' => 10,
        'registered_by_user_id' => 1,
        'registered_at' => now(),
    ]);

    test()->getJson("/api/v1/bracelets/{$foreign->id}", nightposOperationalHeaders($admin))
        ->assertNotFound();
});

it('isolates branch on bracelet detail', function () {
    $admin = phase15AdminToken();
    $girlId = phase15CreateExtraGirl();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 20,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertCreated();

    $id = (int) BraceletModel::query()->latest('id')->value('id');

    test()->getJson("/api/v1/bracelets/{$id}", nightposOperationalHeaders($admin, 'CENTRO'))
        ->assertOk();
});

it('cashier can access and create all girl income services', function () {
    $cashier = nightposLoginPin('1234');
    nightposOpenCashSession($cashier);
    $girlId = phase15GirlUserId();

    test()->getJson('/api/v1/bracelets', nightposOperationalHeaders($cashier))->assertOk();
    test()->getJson('/api/v1/room-services', nightposOperationalHeaders($cashier))->assertOk();
    test()->getJson('/api/v1/shows', nightposOperationalHeaders($cashier))->assertOk();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 5,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashier))->assertCreated();
});
