<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function adminCashSessionsAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function adminCashSessionsCashierToken(): string
{
    return nightposLoginPin('1234');
}

function adminCashSessionsWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function adminCashSessionsSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

function adminCashSessionsChargeSoloOrder(string $cashierToken, string $waiterToken): void
{
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $productId = (int) ProductModel::query()->value('id');

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Admin Cash Test',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $total = (string) test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($waiterToken))
        ->assertOk()
        ->json('data.order.total');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => (float) $total]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();
}

it('admin lists cash sessions for their branch', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    test()->getJson('/api/v1/admin/cash-sessions', nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonStructure(['data' => ['cash_sessions']])
        ->assertJsonPath('data.cash_sessions.0.status', 'OPEN');
});

it('admin does not see cash sessions from another tenant', function () {
    $demoTenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Sur',
        'code' => 'SUR',
        'status' => 'active',
    ]);

    CashSessionModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'opened_by_user_id' => UserModel::query()->where('tenant_id', $demoTenantId)->value('id'),
        'status' => 'OPEN',
        'opening_amount' => 50,
        'opened_at' => now(),
    ]);

    nightposOpenCashSession(adminCashSessionsCashierToken());

    $response = test()->getJson('/api/v1/admin/cash-sessions', nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk();

    $tenantIds = collect($response->json('data.cash_sessions'))->pluck('tenant.id')->unique();

    expect($tenantIds->contains($otherTenant->id))->toBeFalse();
});

it('superadmin can list cash sessions multi tenant with tenant filter', function () {
    $demoTenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra Casa',
        'slug' => 'otra-casa',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Sur',
        'code' => 'SUR',
        'status' => 'active',
    ]);

    CashSessionModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'opened_by_user_id' => UserModel::query()->where('username', 'superadmin')->value('id') ?? 1,
        'status' => 'OPEN',
        'opening_amount' => 75,
        'opened_at' => now(),
    ]);

    nightposOpenCashSession(adminCashSessionsCashierToken());

    test()->getJson('/api/v1/admin/cash-sessions?tenant_id='.$otherTenant->id.'&branch_id='.$otherBranch->id, [
        'Authorization' => 'Bearer '.adminCashSessionsSuperToken(),
        'Accept' => 'application/json',
        'X-Tenant-Slug' => 'otra-casa',
        'X-Branch-Code' => 'SUR',
    ])
        ->assertOk()
        ->assertJsonPath('data.cash_sessions.0.tenant.id', $otherTenant->id);
});

it('cashier cannot list all cash sessions', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    test()->getJson('/api/v1/admin/cash-sessions', nightposOperationalHeaders(adminCashSessionsCashierToken()))
        ->assertForbidden();
});

it('detail includes movements', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    $sessionId = (int) CashSessionModel::query()->where('status', 'OPEN')->value('id');

    test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}", nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonStructure(['data' => ['session', 'movements', 'summary', 'sales', 'settlements_paid']]);
});

it('detail includes sales when order charged', function () {
    $cashier = adminCashSessionsCashierToken();
    $waiter = adminCashSessionsWaiterToken();

    nightposOpenCashSession($cashier);
    adminCashSessionsChargeSoloOrder($cashier, $waiter);

    $sessionId = (int) CashSessionModel::query()->where('status', 'OPEN')->value('id');

    test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}", nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonCount(1, 'data.sales');
});

it('summary includes open sessions count', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    test()->getJson('/api/v1/admin/cash-sessions/summary', nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonPath('data.summary.total_open_sessions', 1);
});

it('filters by date work', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    $today = date('Y-m-d');

    test()->getJson("/api/v1/admin/cash-sessions?date_from={$today}&date_to={$today}", nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonPath('data.cash_sessions.0.status', 'OPEN');

    test()->getJson('/api/v1/admin/cash-sessions?date_from=2099-01-01', nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonCount(0, 'data.cash_sessions');
});

it('filters by cashier work', function () {
    $cashierA = adminCashSessionsCashierToken();
    $admin = adminCashSessionsAdminToken();

    nightposOpenCashSession($cashierA);

    $openSession = CashSessionModel::query()->where('status', 'OPEN')->first();

    expect($openSession)->not->toBeNull();

    $cashierAId = (int) $openSession->opened_by_user_id;

    test()->getJson('/api/v1/admin/cash-sessions', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonCount(1, 'data.cash_sessions');

    $response = test()->getJson("/api/v1/admin/cash-sessions?cashier_user_id={$cashierAId}", nightposOperationalHeaders($admin))
        ->assertOk();

    $sessions = $response->json('data.cash_sessions');

    expect(count($sessions))->toBeGreaterThanOrEqual(1);

    foreach ($sessions as $session) {
        expect($session['cashier']['id'])->toBe($cashierAId);
    }
});

it('filters by shift work', function () {
    nightposOpenCashSession(adminCashSessionsCashierToken());

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    test()->getJson("/api/v1/admin/cash-sessions?official_shift_id={$shiftId}", nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->assertJsonPath('data.cash_sessions.0.official_shift.id', $shiftId);
});

it('includes admin cash session permissions for tenant owner on auth me', function () {
    $permissions = test()->getJson('/api/v1/auth/me', nightposOperationalHeaders(adminCashSessionsAdminToken()))
        ->assertOk()
        ->json('data.user.permissions');

    expect($permissions)->toContain('admin.cash_sessions.list')
        ->and($permissions)->toContain('admin.cash_sessions.view')
        ->and($permissions)->toContain('admin.cash_sessions.summary')
        ->and($permissions)->toContain('admin.cash_sessions.force_close');
});
