<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phase16GirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function phase16Generate(string $token): void
{
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated();
}

function phase16CreateGirlUser(): int
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $roleId = (int) RoleModel::query()->where('slug', 'waiter')->value('id');

    $girl = UserModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'role_id' => $roleId,
        'name' => 'Chica Test F16',
        'username' => 'chica.f16',
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'user_id' => $girl->id,
        'staff_role' => 'GIRL',
        'can_receive_girl_commissions' => true,
        'status' => 'active',
    ]);

    return (int) $girl->id;
}

function phase16ChargeGirlOrder(string $cashierToken, string $waiterToken, int $girlUserId): void
{
    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashierToken))->assertCreated();

    $productId = nightposSeedOrderProduct([
        [
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => 80,
            'girl_amount' => 40,
            'house_amount' => 40,
        ],
    ]);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => 'Liq F16',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlUserId,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 80]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

function phase16ChargeSoloOrder(string $cashierToken, string $waiterToken): void
{
    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashierToken))->assertCreated();

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $orderId = nightposCreateOrderWithItem($waiterToken, [
        'table_label' => 'Liq Solo F16',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

it('generates girl settlement from manual bracelet', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase16GirlId();
    nightposOpenCashSession($admin);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 2,
        'unit_price' => 30,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertCreated();

    phase16Generate($admin);

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_BRACELET')->count())->toBe(1);
    expect(StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('total_amount'))->toBe('60.00');
});

it('generates girl settlement from room service and show', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase16GirlId();
    nightposOpenCashSession($admin);

    $roomId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza 5',
        'total_amount' => 120,
        'girl_percent' => 100,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($admin))->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomId}/finish", [], nightposOperationalHeaders($admin))->assertOk();

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => $girlId,
        'show_type' => 'PRIVATE',
        'unit_price' => 80,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin))->assertCreated();

    phase16Generate($admin);

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_ROOM')->count())->toBe(1);
    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_SHOW')->count())->toBe(1);

    $girl = StaffSettlementModel::query()->where('staff_user_id', $girlId)->first();

    expect($girl->total_amount)->toBe('200.00');
});

it('generates full girl settlement with consumption bracelet room and show', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');
    $girlId = phase16CreateGirlUser();

    phase16ChargeGirlOrder($cashier, $waiter, $girlId);
    nightposOpenCashSession($admin);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 10,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));

    $roomId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza F16',
        'total_amount' => 50,
        'duration_minutes' => 30,
    ]), nightposOperationalHeaders($admin))->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomId}/finish", [], nightposOperationalHeaders($admin))->assertOk();

    phase16Generate($admin);

    $items = StaffSettlementItemModel::query()
        ->whereHas('settlement', fn ($q) => $q->where('staff_user_id', $girlId))
        ->pluck('source_type')
        ->all();

    expect($items)->toContain('GIRL_CONSUMPTION', 'GIRL_BRACELET', 'GIRL_ROOM');
});

it('does not modify paid settlement on regenerate', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    phase16ChargeSoloOrder($cashier, $waiter);
    nightposOpenCashSession($admin);
    phase16Generate($admin);

    $id = (int) StaffSettlementModel::query()->value('id');

    test()->postJson("/api/v1/settlements/{$id}/mark-paid", [], nightposOperationalHeaders($admin))->assertOk();

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => phase16GirlId(),
        'quantity' => 1,
        'unit_price' => 99,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));
    phase16Generate($admin);

    expect(StaffSettlementModel::query()->find($id)->status)->toBe('PAID')
        ->and(StaffSettlementModel::query()->find($id)->total_amount)->toBe('2.50')
        ->and(StaffSettlementItemModel::query()->where('staff_settlement_id', $id)->count())->toBe(1);
});

it('waiter only sees own settlement on current shift', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');
    $girlId = phase16GirlId();

    phase16ChargeSoloOrder($cashier, $waiter);
    nightposOpenCashSession($admin);
    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 20,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));
    phase16Generate($admin);

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $waiterToken = nightposLoginPin('5678');

    expect($waiterToken)->not->toBe($admin);

    $me = test()->getJson('/api/v1/auth/me', nightposOperationalHeaders($waiterToken))->assertOk()->json('data.user');

    expect($me['username'])->toBe('garzon.demo')
        ->and($me['permissions'])->toContain('settlements.access')
        ->and($me['permissions'])->not->toContain('settlements.generate');

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($waiterToken))->assertOk();

    $visible = array_merge($response->json('data.waiters') ?? [], $response->json('data.girls') ?? []);

    expect($visible)->toHaveCount(1)
        ->and($visible[0]['staff_user_id'])->toBe($waiterId)
        ->and($response->json('data.girls'))->toHaveCount(0);
});

it('girl only sees own settlement on current shift', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $girlId = phase16GirlId();
    nightposOpenCashSession($admin);

    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 25,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));
    phase16Generate($admin);

    $girlToken = nightposLoginPin('9012');

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($girlToken))->assertOk();

    expect($response->json('data.girls'))->toHaveCount(1)
        ->and($response->json('data.waiters'))->toHaveCount(0)
        ->and($response->json('data.girls.0.staff_user_id'))->toBe($girlId);
});

it('shows settlements after shift is closed', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    phase16ChargeSoloOrder($cashier, $waiter);
    nightposOpenCashSession($admin);
    phase16Generate($admin);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    test()->postJson('/api/v1/cash/session/close', ['declared_closing_amount' => 100], nightposOperationalHeaders($cashier));
    test()->postJson("/api/v1/shifts/{$shiftId}/close", ['counted_cash' => 100], nightposOperationalHeaders($admin));

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.shift.id', $shiftId)
        ->assertJsonPath('data.summary.total_waiters', '2.50');
});

it('history filters by settlement type', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');
    $girlId = phase16GirlId();

    phase16ChargeSoloOrder($cashier, $waiter);
    nightposOpenCashSession($admin);
    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 15,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));
    phase16Generate($admin);

    $response = test()->getJson('/api/v1/settlements/history?settlement_type=GIRL', nightposOperationalHeaders($admin))
        ->assertOk();

    expect($response->json('data.settlements'))->toHaveCount(1)
        ->and($response->json('data.settlements.0.settlement_type'))->toBe('GIRL');
});

it('summary includes consumption and bracelet totals separately', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');
    $girlId = phase16CreateGirlUser();

    phase16ChargeGirlOrder($cashier, $waiter, $girlId);
    nightposOpenCashSession($admin);
    test()->postJson('/api/v1/bracelets', [
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => 10,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($admin));
    phase16Generate($admin);

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.summary.total_consumption', '40.00')
        ->assertJsonPath('data.summary.total_bracelets', '10.00');
});
