<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function scopeFixAdminToken(): string
{
    return nightposLoginPin('2468');
}

function scopeFixTenantBranchShift(): array
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    return compact('tenantId', 'branchId', 'shiftId');
}

function scopeFixCreateOrder(string $status, string $label): int
{
    ['tenantId' => $tenantId, 'branchId' => $branchId, 'shiftId' => $shiftId] = scopeFixTenantBranchShift();

    return (int) OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'order_number' => 'SCOPE-'.uniqid(),
        'status' => $status,
        'table_label' => $label,
        'waiter_user_id' => nightposDemoWaiterUserId(),
        'opened_by_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
    ])->id;
}

function scopeFixOrderIds(string $scope): array
{
    $response = test()->getJson('/api/v1/orders?scope='.$scope, nightposOperationalHeaders(scopeFixAdminToken()))
        ->assertOk();

    return collect($response->json('data.orders'))->pluck('id')->map(fn ($id) => (int) $id)->all();
}

it('scope operational_active returns OPEN and SENT_TO_BAR', function () {
    $openId = scopeFixCreateOrder('OPEN', 'Mesa Scope Open');
    $barId = scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Scope Bar');
    scopeFixCreateOrder('BILLED', 'Mesa Scope Billed');
    scopeFixCreateOrder('CANCELLED', 'Mesa Scope Cancelled');

    $ids = scopeFixOrderIds('operational_active');

    expect($ids)->toContain($openId, $barId)
        ->and(count($ids))->toBeGreaterThanOrEqual(2);
});

it('scope open returns only OPEN', function () {
    $openId = scopeFixCreateOrder('OPEN', 'Mesa Solo Open');
    scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Solo Bar');

    $ids = scopeFixOrderIds('open');

    expect($ids)->toContain($openId);

    $statuses = collect(test()->getJson('/api/v1/orders?scope=open', nightposOperationalHeaders(scopeFixAdminToken()))
        ->json('data.orders'))->pluck('status')->unique()->all();

    expect($statuses)->toBe(['OPEN']);
});

it('scope sent_to_bar returns only SENT_TO_BAR', function () {
    scopeFixCreateOrder('OPEN', 'Mesa Bar Open');
    $barId = scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Bar Only');

    $ids = scopeFixOrderIds('sent_to_bar');

    expect($ids)->toContain($barId);

    $statuses = collect(test()->getJson('/api/v1/orders?scope=sent_to_bar', nightposOperationalHeaders(scopeFixAdminToken()))
        ->json('data.orders'))->pluck('status')->unique()->all();

    expect($statuses)->toBe(['SENT_TO_BAR']);
});

it('scope pending_charge includes SENT_TO_BAR without duplicating open scope', function () {
    scopeFixCreateOrder('OPEN', 'Mesa Pending Open');
    $barId = scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Pending Bar');

    $pendingIds = scopeFixOrderIds('pending_charge');
    $openIds = scopeFixOrderIds('open');

    expect($pendingIds)->toContain($barId)
        ->and($openIds)->not->toContain($barId);
});

it('scope cancelled returns only CANCELLED', function () {
    scopeFixCreateOrder('OPEN', 'Mesa Cancel Open');
    $cancelId = scopeFixCreateOrder('CANCELLED', 'Mesa Cancel Only');

    $ids = scopeFixOrderIds('cancelled');

    expect($ids)->toContain($cancelId);
});

it('status OPEN remains compatible without scope', function () {
    $openId = scopeFixCreateOrder('OPEN', 'Mesa Legacy Open');
    scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Legacy Bar');

    $response = test()->getJson('/api/v1/orders?status=OPEN', nightposOperationalHeaders(scopeFixAdminToken()))
        ->assertOk();

    $ids = collect($response->json('data.orders'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($ids)->toContain($openId);
});

it('scope cashier_chargeable remains compatible', function () {
    $openId = scopeFixCreateOrder('OPEN', 'Mesa Cashier Open');
    $barId = scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Cashier Bar');
    scopeFixCreateOrder('BILLED', 'Mesa Cashier Billed');

    $ids = scopeFixOrderIds('cashier_chargeable');

    expect($ids)->toContain($openId, $barId);
});

it('isolates orders by tenant', function () {
    scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Tenant A');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro Tenant Test',
        'slug' => 'otro-tenant-test',
        'status' => 'active',
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'code' => 'OTRO',
        'name' => 'Sucursal Otro',
        'status' => 'active',
    ]);

    OrderModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => $otherBranch->id,
        'official_shift_id' => null,
        'order_number' => 'OTHER-TENANT',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Mesa Otro Tenant',
        'waiter_user_id' => nightposDemoWaiterUserId(),
        'opened_by_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => 5,
        'total' => 5,
        'currency' => 'BOB',
    ]);

    $labels = collect(test()->getJson('/api/v1/orders?scope=operational_active', nightposOperationalHeaders(scopeFixAdminToken()))
        ->json('data.orders'))->pluck('table_label')->all();

    expect($labels)->not->toContain('Mesa Otro Tenant');
});

it('isolates orders by branch', function () {
    scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Branch Centro');

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $otherBranch = BranchModel::query()
        ->where('tenant_id', $tenantId)
        ->where('code', '!=', 'CENTRO')
        ->first();

    if ($otherBranch === null) {
        $otherBranch = BranchModel::query()->create([
            'tenant_id' => $tenantId,
            'code' => 'NORTE',
            'name' => 'Sucursal Norte Test',
            'status' => 'active',
        ]);
    }

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranch->id,
        'official_shift_id' => null,
        'order_number' => 'OTHER-BRANCH',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Mesa Otra Sucursal',
        'waiter_user_id' => nightposDemoWaiterUserId(),
        'opened_by_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => 5,
        'total' => 5,
        'currency' => 'BOB',
    ]);

    $labels = collect(test()->getJson('/api/v1/orders?scope=sent_to_bar', nightposOperationalHeaders(scopeFixAdminToken()))
        ->json('data.orders'))->pluck('table_label')->all();

    expect($labels)->not->toContain('Mesa Otra Sucursal');
});

it('waiter pending_charge KPI does not double count SENT_TO_BAR', function () {
    $baseline = test()->getJson('/api/v1/waiter/dashboard', nightposOperationalHeaders(nightposLoginPin('5678')))
        ->assertOk()
        ->json('data.dashboard.cards');

    scopeFixCreateOrder('SENT_TO_BAR', 'Mesa Waiter KPI');

    $cards = test()->getJson('/api/v1/waiter/dashboard', nightposOperationalHeaders(nightposLoginPin('5678')))
        ->assertOk()
        ->json('data.dashboard.cards');

    expect($cards['sent_to_bar'])->toBe($baseline['sent_to_bar'] + 1)
        ->and($cards['pending_charge'])->toBe($baseline['pending_charge']);
});
