<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function partialGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function partialAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function partialCashierToken(): string
{
    return nightposLoginPin('1234');
}

function partialWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function partialGenerate(string $token): void
{
    nightposResetApiAuth();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($token))
        ->assertCreated();
}

function partialChargeGirlOrder(string $cashierToken, string $waiterToken, int $girlUserId, string $table = 'Partial Girl'): void
{
    nightposOpenCashSession($cashierToken, 500);

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
        'table_label' => $table,
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

function partialFinishRoom(string $adminToken, int $girlId, string $label = 'Pieza Partial'): int
{
    $roomId = test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => $label,
        'total_amount' => 100,
        'girl_percent' => 100,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders($adminToken))->assertCreated()->json('data.room_service.id');

    test()->postJson("/api/v1/room-services/{$roomId}/finish", [], nightposOperationalHeaders($adminToken))->assertOk();

    return (int) $roomId;
}

function partialMarkPaid(int $settlementId, string $token): void
{
    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($token))->assertOk();
}

it('creates second girl settlement after first cut is paid and new activity arrives', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Maria 1');
    partialFinishRoom($admin, $girlId);
    partialGenerate($admin);

    $firstId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->value('id');

    partialMarkPaid($firstId, $admin);

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Maria 2');
    partialGenerate($admin);

    $first = StaffSettlementModel::query()->find($firstId);
    $second = StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->first();

    expect($first->status)->toBe('PAID')
        ->and(StaffSettlementItemModel::query()->where('staff_settlement_id', $firstId)->count())->toBe(2)
        ->and($second)->not->toBeNull()
        ->and($second->id)->not->toBe($firstId)
        ->and(StaffSettlementItemModel::query()->where('staff_settlement_id', $second->id)->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(2);
});

it('creates second waiter settlement after first cut is paid and new sales arrive', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    nightposOpenCashSession($cashier, 500);

    $firstOrderId = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Waiter Partial 1',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$firstOrderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    partialGenerate($admin);

    $firstId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $waiterId)
        ->where('settlement_type', 'WAITER')
        ->value('id');

    partialMarkPaid($firstId, $admin);

    $secondOrderId = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Waiter Partial 2',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$secondOrderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    partialGenerate($admin);

    expect(StaffSettlementModel::query()->find($firstId)->status)->toBe('PAID')
        ->and(StaffSettlementModel::query()->where('staff_user_id', $waiterId)->where('settlement_type', 'WAITER')->count())->toBe(2)
        ->and(StaffSettlementModel::query()->where('staff_user_id', $waiterId)->where('settlement_type', 'WAITER')->where('status', 'PENDING')->count())->toBe(1);
});

function partialCleaningToken(): string
{
    return nightposLoginPin('3333');
}

it('creates second cleaning settlement after first cut is paid and another room is cleaned', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $cleaningUserId = (int) UserModel::query()->where('username', 'limpieza.demo')->value('id');

    nightposOpenCashSession($cashier, 500);

    $createCleanedService = function (string $roomCode) use ($admin, $cashier): void {
        $roomId = (int) test()->postJson('/api/v1/rooms', [
            'code' => $roomCode,
            'name' => "Room {$roomCode}",
            'room_type' => 'STANDARD',
        ], nightposOperationalHeaders($admin))->assertCreated()->json('data.room.id');

        $serviceId = (int) test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
            'girl_user_id' => partialGirlId(),
            'room_id' => $roomId,
            'room_label' => "Pieza {$roomCode}",
            'total_amount' => 100,
            'duration_minutes' => 30,
        ]), nightposOperationalHeaders($cashier))->assertCreated()->json('data.room_service.id');

        $cleaningToken = partialCleaningToken();

        test()->postJson("/api/v1/cleaning/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($cleaningToken))
            ->assertOk();

        test()->postJson("/api/v1/cleaning/rooms/{$roomId}/mark-clean", [], nightposOperationalHeaders($cleaningToken))
            ->assertOk();

        nightposResetApiAuth();
    };

    $createCleanedService('PART1');
    partialGenerate(partialAdminToken());

    $firstId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $cleaningUserId)
        ->where('settlement_type', 'CLEANING')
        ->value('id');

    partialMarkPaid($firstId, $cashier);

    $createCleanedService('PART2');
    partialGenerate(partialAdminToken());

    expect(StaffSettlementModel::query()->find($firstId)->status)->toBe('PAID')
        ->and(StaffSettlementModel::query()->where('staff_user_id', $cleaningUserId)->where('settlement_type', 'CLEANING')->count())->toBe(2)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'CLEANING_BASE')->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('source_type', 'CLEANING_ROOM')->count())->toBe(2);
});

it('accumulates new sources in the same pending settlement when not paid yet', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Accum 1');
    partialGenerate($admin);

    $settlementId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('settlement_type', 'GIRL')
        ->value('id');

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Accum 2');
    partialGenerate($admin);

    expect(StaffSettlementModel::query()->where('staff_user_id', $girlId)->where('settlement_type', 'GIRL')->count())->toBe(1)
        ->and(StaffSettlementItemModel::query()->where('staff_settlement_id', $settlementId)->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(2);
});

it('does not duplicate settlement items for already settled sources', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId);
    partialGenerate($admin);
    partialGenerate($admin);

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(1);
});

it('blocks cash close when new sources exist without settlement items after a paid cut', function () {
    $token = partialCashierToken();
    $admin = partialAdminToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    nightposOpenCashSession($token, 500);
    partialChargeGirlOrder($token, $waiter, $girlId);
    partialGenerate($token);
    partialMarkPaid((int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id'), $admin);

    partialChargeGirlOrder($token, $waiter, $girlId, 'Orphan');

    $response = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    expect(collect($response->json('data.blockers'))->pluck('code')->all())
        ->toContain('unsettled_settlement_sources');
});

it('reports sum paid and pending amounts across multiple cuts', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Report 1');
    partialGenerate($admin);

    $firstId = (int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id');
    partialMarkPaid($firstId, $admin);

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Report 2');
    partialGenerate($admin);

    $response = test()->getJson('/api/v1/reports/settlements', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.totals');

    expect((float) $response['total_paid'])->toBeGreaterThan(0)
        ->and((float) $response['total_pending'])->toBeGreaterThan(0)
        ->and((float) $response['total_generated'])->toBe((float) $response['total_paid'] + (float) $response['total_pending']);
});

it('exposes cut numbers in current shift overview for multiple girl settlements', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Cut 1');
    partialGenerate($admin);
    partialMarkPaid((int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id'), $admin);

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Cut 2');
    partialGenerate($admin);

    $girls = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->json('data.girls');

    $cuts = collect($girls)->where('staff_user_id', $girlId)->sortBy('cut_number')->values();

    expect($cuts)->toHaveCount(2)
        ->and($cuts[0]['cut_number'])->toBe(1)
        ->and($cuts[0]['status'])->toBe('PAID')
        ->and($cuts[1]['cut_number'])->toBe(2)
        ->and($cuts[1]['status'])->toBe('PENDING');
});

it('does not expose partial settlements across tenants', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    partialChargeGirlOrder($cashier, $waiter, $girlId);
    partialGenerate($admin);

    $id = (int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Partial',
        'slug' => 'otra-partial',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Otra',
        'code' => 'OTRA-P',
        'status' => 'active',
    ]);

    $otherAdmin = UserModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'role_id' => RoleModel::query()->where('slug', 'tenant_owner')->value('id'),
        'name' => 'Admin Otra Partial',
        'username' => 'admin.otra.partial',
        'email' => 'otra.partial@test.com',
        'password' => bcrypt('AdminOtra123!'),
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel::query()->create([
        'user_id' => $otherAdmin->id,
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
    ]);

    $otherToken = nightposLoginPassword('admin.otra.partial', 'AdminOtra123!', 'otra-partial');

    test()->getJson("/api/v1/settlements/{$id}", nightposOperationalHeaders($otherToken, 'OTRA-P'))
        ->assertNotFound();
});

it('creating two paid cuts creates two separate cash expenses', function () {
    $admin = partialAdminToken();
    $cashier = partialCashierToken();
    $waiter = partialWaiterToken();
    $girlId = partialGirlId();

    nightposOpenCashSession($cashier, 1000);
    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Expense 1');
    partialGenerate($admin);

    $firstId = (int) StaffSettlementModel::query()->where('staff_user_id', $girlId)->value('id');
    partialMarkPaid($firstId, $cashier);

    partialChargeGirlOrder($cashier, $waiter, $girlId, 'Expense 2');
    partialGenerate($admin);

    $secondId = (int) StaffSettlementModel::query()
        ->where('staff_user_id', $girlId)
        ->where('status', 'PENDING')
        ->value('id');

    partialMarkPaid($secondId, $cashier);

    $expenses = CashMovementModel::query()
        ->where('movement_type', 'EXPENSE')
        ->where('source_type', 'STAFF_SETTLEMENT')
        ->whereIn('source_id', [$firstId, $secondId])
        ->count();

    expect($expenses)->toBe(2);
});
