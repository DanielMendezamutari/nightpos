<?php

declare(strict_types=1);

use App\Application\Tenant\Support\TenantDefaultRolePermissions;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function opFixCashierToken(): string
{
    return nightposLoginPin('1234');
}

function opFixWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function opFixGirlToken(): string
{
    return nightposLoginPin('9012');
}

function opFixSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

function opFixCreateOpenOrder(string $waiterToken, bool $withItem = true): int
{
    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Cobro Cajera',
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $orderId = (int) $orderResponse->json('data.order.id');

    if ($withItem) {
        $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiterToken))
            ->json('data.products.0.id');

        test()->postJson("/api/v1/orders/{$orderId}/items", [
            'product_id' => $productId,
            'sale_mode' => 'SOLO_CLIENTE',
            'quantity' => 1,
        ], nightposOperationalHeaders($waiterToken))->assertCreated();
    }

    return $orderId;
}

it('lists cashier chargeable orders across branch statuses', function () {
    nightposEnsureShiftOpen();
    $waiterToken = opFixWaiterToken();
    $openId = opFixCreateOpenOrder($waiterToken);
    $cashierToken = opFixCashierToken();

    test()->postJson("/api/v1/orders/{$openId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $response = test()->getJson('/api/v1/orders?scope=cashier_chargeable', nightposOperationalHeaders($cashierToken))
        ->assertOk();

    $ids = collect($response->json('data.orders'))->pluck('id')->all();

    expect($ids)->toContain($openId);

    $row = collect($response->json('data.orders'))->firstWhere('id', $openId);

    expect($row)->toHaveKeys(['waiter_name', 'opened_at', 'items_count', 'status'])
        ->and($row['status'])->toBe('SENT_TO_BAR');
});

it('allows cashier to charge from chargeable order when cash session is open', function () {
    nightposEnsureShiftOpen();
    $waiterToken = opFixWaiterToken();
    $orderId = opFixCreateOpenOrder($waiterToken);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $cashierToken = opFixCashierToken();
    nightposOpenCashSession($cashierToken);

    $total = (float) OrderModel::query()->find($orderId)?->total;

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $total]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    expect(OrderModel::query()->find($orderId)?->status)->toBe('BILLED');
});

it('denies cashier charge when cash session is closed', function () {
    nightposEnsureShiftOpen();
    $waiterToken = opFixWaiterToken();
    $orderId = opFixCreateOpenOrder($waiterToken);
    $cashierToken = opFixCashierToken();

    $total = (float) OrderModel::query()->find($orderId)?->total;

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $total]],
    ], nightposOperationalHeaders($cashierToken))->assertStatus(422);
});

it('lists open orders for waiter with open scope', function () {
    nightposEnsureShiftOpen();
    $waiter = opFixWaiterToken();

    opFixCreateOpenOrder($waiter);

    $response = test()->getJson('/api/v1/waiter/orders?scope=open', nightposOperationalHeaders($waiter))
        ->assertOk();

    expect(collect($response->json('data.orders'))->pluck('status')->unique()->all())
        ->toBe(['OPEN']);
});

it('allows waiter to add items when order is sent to bar', function () {
    nightposEnsureShiftOpen();
    $waiter = opFixWaiterToken();

    $productId = (int) test()->getJson('/api/v1/products', nightposOperationalHeaders($waiter))
        ->json('data.products.0.id');

    $orderId = opFixCreateOpenOrder($waiter);

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiter))->assertCreated();
});

it('denies girl access to waiter dashboard', function () {
    nightposEnsureShiftOpen();

    test()->getJson('/api/v1/waiter/dashboard', nightposOperationalHeaders(opFixGirlToken()))
        ->assertForbidden();
});

it('allows girl to access girl shift earnings', function () {
    nightposEnsureShiftOpen();

    test()->getJson('/api/v1/girl/shift-earnings', nightposOperationalHeaders(opFixGirlToken()))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'earnings' => [
                    'consumption_total',
                    'bracelets_total',
                    'rooms_total',
                    'shows_total',
                    'total_pending',
                    'total_paid',
                ],
            ],
        ]);
});

it('denies girl from creating orders', function () {
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Hack Chica',
    ], nightposOperationalHeaders(opFixGirlToken()))->assertForbidden();
});

it('provisions complete waiter permissions for new tenant via platform setup', function () {
    test()->postJson('/api/v1/admin/platform/setup', [
        'tenant' => [
            'name' => 'Club Operativo',
            'slug' => 'club-operativo',
            'status' => 'active',
        ],
        'branch' => [
            'name' => 'Sede',
            'code' => 'SEDE1',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin Oper',
            'username' => 'admin.oper',
            'password' => 'OperAdmin123!',
            'pin' => '8888',
        ],
    ], [
        'Authorization' => 'Bearer '.opFixSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    $tenantId = (int) TenantModel::query()->where('slug', 'club-operativo')->value('id');
    $role = RoleModel::query()->where('tenant_id', $tenantId)->where('slug', 'waiter')->first();

    expect($role)->not->toBeNull();

    $slugs = PermissionModel::query()
        ->whereIn('id', $role->permissions()->pluck('permissions.id'))
        ->pluck('slug')
        ->all();

    foreach (TenantDefaultRolePermissions::waiter() as $expected) {
        expect($slugs)->toContain($expected);
    }
});

it('provisions complete cleaning permissions for new tenant via platform setup', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'club-operativo')->value('id');

    if ($tenantId === 0) {
        test()->postJson('/api/v1/admin/platform/setup', [
            'tenant' => ['name' => 'Club Operativo', 'slug' => 'club-operativo', 'status' => 'active'],
            'branch' => ['name' => 'Sede', 'code' => 'SEDE1', 'status' => 'active'],
            'admin' => ['name' => 'Admin Oper', 'username' => 'admin.oper', 'password' => 'OperAdmin123!', 'pin' => '8888'],
        ], [
            'Authorization' => 'Bearer '.opFixSuperToken(),
            'Accept' => 'application/json',
        ])->assertCreated();

        $tenantId = (int) TenantModel::query()->where('slug', 'club-operativo')->value('id');
    }

    $role = RoleModel::query()->where('tenant_id', $tenantId)->where('slug', 'cleaning')->first();

    $slugs = PermissionModel::query()
        ->whereIn('id', $role->permissions()->pluck('permissions.id'))
        ->pluck('slug')
        ->all();

    foreach (TenantDefaultRolePermissions::cleaning() as $expected) {
        expect($slugs)->toContain($expected);
    }
});

it('provisions girl role with dashboard permissions for new tenant', function () {
    test()->postJson('/api/v1/admin/platform/setup', [
        'tenant' => ['name' => 'Club Chica', 'slug' => 'club-chica', 'status' => 'active'],
        'branch' => ['name' => 'Sede Chica', 'code' => 'CHICA1', 'status' => 'active'],
        'admin' => ['name' => 'Admin Chica', 'username' => 'admin.chica', 'password' => 'ChicaAdmin123!', 'pin' => '7777'],
    ], [
        'Authorization' => 'Bearer '.opFixSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    $tenantId = (int) TenantModel::query()->where('slug', 'club-chica')->value('id');

    expect($tenantId)->toBeGreaterThan(0);

    $role = RoleModel::query()->where('tenant_id', $tenantId)->where('slug', 'girl')->first();

    expect($role)->not->toBeNull();

    $slugs = PermissionModel::query()
        ->whereIn('id', $role->permissions()->pluck('permissions.id'))
        ->pluck('slug')
        ->all();

    expect($slugs)->toContain('girl.dashboard')
        ->and($slugs)->toContain('girl.earnings.view');
});

it('assigns girl staff to girl role not waiter role', function () {
    $girl = UserModel::query()->where('username', 'chica.centro')->with('role')->first();

    expect($girl)->not->toBeNull()
        ->and($girl->role?->slug)->toBe('girl');
});
