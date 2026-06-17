<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
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
});

function nightposCreateGirlUser(): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $roleId = (int) RoleModel::query()->where('slug', 'girl')->value('id');

    $girl = UserModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'role_id' => $roleId,
        'name' => 'Chica Test',
        'username' => 'chica.test',
        'email' => null,
        'password' => null,
        'status' => 'active',
    ]);

    StaffProfileModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'user_id' => $girl->id,
        'staff_role' => 'GIRL',
        'waiter_commission_percent' => null,
        'can_receive_girl_commissions' => true,
        'status' => 'active',
    ]);

    return (int) $girl->id;
}

function nightposChargeSoloOrder(string $cashierToken, string $waiterToken): int
{
    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 100], nightposOperationalHeaders($cashierToken))->assertCreated();

    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $orderId = nightposCreateOrderWithItem($waiterToken, [
        'table_label' => 'Liq Solo',
        'waiter_user_id' => $waiterId,
    ])['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    return $orderId;
}

function nightposChargeGirlOrder(string $cashierToken, string $waiterToken, int $girlUserId): int
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
        'table_label' => 'Liq Chica',
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

    return $orderId;
}

it('generates waiter settlement from charged sale', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);

    $response = test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin));
    $response->assertCreated()
        ->assertJsonPath('data.created_items', 1);

    $waiterSettlement = StaffSettlementModel::query()
        ->where('settlement_type', 'WAITER')
        ->first();

    expect($waiterSettlement)->not->toBeNull()
        ->and($waiterSettlement->total_amount)->toBe('2.50')
        ->and($waiterSettlement->status)->toBe('PENDING');

    expect(StaffSettlementItemModel::query()->where('source_type', 'WAITER_COMMISSION')->count())->toBe(1);
});

it('generates girl settlement from CON_ACOMPANANTE sale', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');
    $girlId = nightposCreateGirlUser();

    nightposChargeGirlOrder($cashier, $waiter, $girlId);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))
        ->assertCreated();

    $girlSettlement = StaffSettlementModel::query()
        ->where('settlement_type', 'GIRL')
        ->where('staff_user_id', $girlId)
        ->first();

    expect($girlSettlement)->not->toBeNull()
        ->and($girlSettlement->total_amount)->toBe('40.00');

    expect(StaffSettlementItemModel::query()->where('source_type', 'GIRL_CONSUMPTION')->count())->toBe(1);
});

it('does not duplicate settlement items on second generate', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated()
        ->assertJsonPath('data.created_items', 0);

    expect(StaffSettlementItemModel::query()->count())->toBe(1);
});

it('marks settlement as paid', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    $id = (int) StaffSettlementModel::query()->value('id');

    test()->postJson("/api/v1/settlements/{$id}/mark-paid", ['payment_method' => 'CASH', 'notes' => 'Efectivo'], nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.settlement.status', 'PAID');

    expect(StaffSettlementModel::query()->find($id)->paid_at)->not->toBeNull();
});

it('denies cashier without pay permission from marking paid', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    $id = (int) StaffSettlementModel::query()->value('id');

    RoleModel::query()->where('slug', 'cashier')->first()
        ->permissions()
        ->detach(
            \App\Infrastructure\Persistence\Eloquent\Models\PermissionModel::query()
                ->where('slug', 'settlements.pay')
                ->pluck('id')
        );

    $cashierFresh = nightposLoginPin('1234');

    test()->postJson("/api/v1/settlements/{$id}/mark-paid", ['payment_method' => 'CASH'], nightposOperationalHeaders($cashierFresh))
        ->assertForbidden();
});

it('does not expose settlements from another tenant', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    $id = (int) StaffSettlementModel::query()->value('id');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra',
        'slug' => 'otra-liq',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Otra',
        'code' => 'OTRA',
        'status' => 'active',
    ]);

    $otherAdmin = UserModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'role_id' => RoleModel::query()->where('slug', 'tenant_owner')->value('id'),
        'name' => 'Admin Otra',
        'username' => 'admin.otra',
        'email' => 'otra@test.com',
        'password' => bcrypt('AdminOtra123!'),
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel::query()->create([
        'user_id' => $otherAdmin->id,
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
    ]);

    $otherToken = nightposLoginPassword('admin.otra', 'AdminOtra123!', 'otra-liq');

    test()->getJson("/api/v1/settlements/{$id}", nightposOperationalHeaders($otherToken, 'OTRA'))
        ->assertNotFound();
});

it('keeps settlement snapshot after shift is closed', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    $settlementId = (int) StaffSettlementModel::query()->value('id');
    $totalBefore = StaffSettlementModel::query()->find($settlementId)->total_amount;

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    nightposPrepareCashSessionClose($cashier, $admin);
    test()->postJson('/api/v1/cash/session/close', ['declared_closing_amount' => 100], nightposOperationalHeaders($cashier))->assertOk();
    test()->postJson("/api/v1/shifts/{$shiftId}/close", ['counted_cash' => 100], nightposOperationalHeaders($admin))->assertOk();

    test()->getJson("/api/v1/settlements/{$settlementId}", nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.settlement.total_amount', $totalBefore);
});

it('does not generate girl settlement when sale has no girl', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    expect(StaffSettlementModel::query()->where('settlement_type', 'GIRL')->count())->toBe(0);
});

it('returns current shift overview with summary cards data', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    nightposChargeSoloOrder($cashier, $waiter);
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($admin))->assertCreated();

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.summary.total_waiters', '2.50')
        ->assertJsonPath('data.waiters.0.commission_percent', '5.00');
});
