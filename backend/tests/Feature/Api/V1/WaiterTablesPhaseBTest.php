<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceTableModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function phaseBAdminHeaders(): array
{
    return nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!'));
}

function phaseBWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function phaseBWaiter2Token(): string
{
    return nightposLoginPin('5688');
}

function phaseBWaiterHeaders(?string $token = null): array
{
    return nightposOperationalHeaders($token ?? phaseBWaiterToken());
}

function phaseBWaiterUserId(): int
{
    return nightposDemoWaiterUserId();
}

function phaseBWaiter2UserId(): int
{
    return (int) UserModel::query()->where('username', 'garzon2.demo')->value('id');
}

function phaseBCreateAreaAndTables(): array
{
    $area = test()->postJson('/api/v1/service-areas', [
        'code' => 'SALON-B',
        'name' => 'Salón B',
        'area_type' => 'VIP',
    ], phaseBAdminHeaders())->assertCreated();

    $areaId = (int) $area->json('data.service_area.id');

    $table1 = test()->postJson('/api/v1/service-tables', [
        'service_area_id' => $areaId,
        'code' => 'B-01',
        'label' => 'Mesa 1',
        'sort_order' => 1,
    ], phaseBAdminHeaders())->assertCreated();

    $table2 = test()->postJson('/api/v1/service-tables', [
        'service_area_id' => $areaId,
        'code' => 'B-02',
        'label' => 'Mesa 2',
        'sort_order' => 2,
    ], phaseBAdminHeaders())->assertCreated();

    return [
        'area_id' => $areaId,
        'table1_id' => (int) $table1->json('data.service_table.id'),
        'table2_id' => (int) $table2->json('data.service_table.id'),
    ];
}

function phaseBAssignTablesToWaiter(int $waiterId, array $tableIds): void
{
    test()->putJson('/api/v1/waiter-table-assignments/sync', [
        'waiter_user_id' => $waiterId,
        'service_table_ids' => $tableIds,
    ], phaseBAdminHeaders())->assertOk();
}

// ─── 1. admin crea mesa ───────────────────────────────────────────────────────

it('admin creates service table in a salon', function () {
    $created = phaseBCreateAreaAndTables();

    $list = test()->getJson('/api/v1/service-tables?service_area_id='.$created['area_id'], phaseBAdminHeaders())
        ->assertOk()
        ->json('data.service_tables');

    expect($list)->toHaveCount(2)
        ->and(collect($list)->pluck('label')->all())->toContain('Mesa 1', 'Mesa 2');
});

// ─── 2. admin asigna mesas a garzón ──────────────────────────────────────────

it('admin assigns tables to waiter', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id'], $created['table2_id']]);

    $assignments = test()->getJson(
        '/api/v1/waiter-table-assignments?waiter_user_id='.phaseBWaiterUserId(),
        phaseBAdminHeaders(),
    )->assertOk()->json('data.waiter_table_assignments');

    expect($assignments)->toHaveCount(2);
});

// ─── 3. garzón ve solo sus mesas ─────────────────────────────────────────────

it('waiter sees only assigned tables in my-tables', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    $tables = test()->getJson('/api/v1/waiter/my-tables', phaseBWaiterHeaders())
        ->assertOk()
        ->json('data.tables');

    expect($tables)->toHaveCount(1)
        ->and($tables[0]['id'])->toBe($created['table1_id'])
        ->and($tables[0]['status'])->toBe('FREE');
});

// ─── 4. mesa libre crea comanda ──────────────────────────────────────────────

it('opening a free table creates an order', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    $response = test()->postJson(
        '/api/v1/waiter/my-tables/'.$created['table1_id'].'/open',
        [],
        phaseBWaiterHeaders(),
    )->assertCreated();

    expect($response->json('data.created'))->toBeTrue()
        ->and($response->json('data.order.service_table_id'))->toBe($created['table1_id'])
        ->and($response->json('data.order.table_label'))->toBe('Mesa 1')
        ->and($response->json('data.order.waiter_user_id'))->toBe(phaseBWaiterUserId());
});

// ─── 5. mesa ocupada devuelve comanda existente ───────────────────────────────

it('opening an occupied table returns the existing order', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    $first = test()->postJson(
        '/api/v1/waiter/my-tables/'.$created['table1_id'].'/open',
        [],
        phaseBWaiterHeaders(),
    )->assertCreated();

    $orderId = (int) $first->json('data.order.id');

    $second = test()->postJson(
        '/api/v1/waiter/my-tables/'.$created['table1_id'].'/open',
        [],
        phaseBWaiterHeaders(),
    )->assertOk();

    expect($second->json('data.created'))->toBeFalse()
        ->and($second->json('data.order.id'))->toBe($orderId);
});

// ─── 6. no permite dos comandas activas en la misma mesa ─────────────────────

it('does not create two active orders for the same table', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    test()->postJson('/api/v1/waiter/my-tables/'.$created['table1_id'].'/open', [], phaseBWaiterHeaders())
        ->assertCreated();

    test()->postJson('/api/v1/waiter/my-tables/'.$created['table1_id'].'/open', [], phaseBWaiterHeaders())
        ->assertOk()
        ->assertJsonPath('data.created', false);

    $myTables = test()->getJson('/api/v1/waiter/my-tables', phaseBWaiterHeaders())
        ->assertOk()
        ->json('data.tables');

    expect($myTables[0]['status'])->toBe('OCCUPIED')
        ->and($myTables[0]['order_id'])->not->toBeNull();
});

// ─── 7. cobrar comanda libera mesa ───────────────────────────────────────────

it('charging an order frees the table', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    $open = test()->postJson(
        '/api/v1/waiter/my-tables/'.$created['table1_id'].'/open',
        [],
        phaseBWaiterHeaders(),
    )->assertCreated();

    $orderId = (int) $open->json('data.order.id');

    $productId = nightposSeedOrderProduct();
    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], phaseBWaiterHeaders())->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], phaseBWaiterHeaders())->assertOk();

    $cashier = nightposLoginPin('1234');
    nightposOpenCashSession($cashier);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 25]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    $tables = test()->getJson('/api/v1/waiter/my-tables', phaseBWaiterHeaders())
        ->assertOk()
        ->json('data.tables');

    expect($tables[0]['status'])->toBe('FREE')
        ->and($tables[0]['order_id'])->toBeNull();
});

// ─── 8. otra sucursal no ve mesas ────────────────────────────────────────────

it('tables from another branch are not visible to waiter', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'code' => 'NORTE',
        'name' => 'Sucursal Norte',
        'status' => 'active',
    ]);

    $otherArea = ServiceAreaModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranch->id,
        'code' => 'N-VIP',
        'name' => 'VIP Norte',
        'area_type' => 'VIP',
        'status' => 'active',
    ]);

    $otherTable = ServiceTableModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranch->id,
        'service_area_id' => $otherArea->id,
        'code' => 'N-01',
        'label' => 'Mesa Norte',
        'sort_order' => 1,
        'status' => 'active',
    ]);

    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id']]);

    $tables = test()->getJson('/api/v1/waiter/my-tables', phaseBWaiterHeaders())
        ->assertOk()
        ->json('data.tables');

    $ids = collect($tables)->pluck('id')->all();

    expect($ids)->toContain($created['table1_id'])
        ->and($ids)->not->toContain((int) $otherTable->id);
});

// ─── 9. otro garzón no ve mesas no asignadas ─────────────────────────────────

it('another waiter does not see tables assigned to someone else', function () {
    $created = phaseBCreateAreaAndTables();
    phaseBAssignTablesToWaiter(phaseBWaiterUserId(), [$created['table1_id'], $created['table2_id']]);

    $tables = test()->getJson('/api/v1/waiter/my-tables', phaseBWaiterHeaders(phaseBWaiter2Token()))
        ->assertOk()
        ->json('data.tables');

    expect($tables)->toBeEmpty();
});

// ─── 10. table_label histórico sigue funcionando ─────────────────────────────

it('legacy table_label order creation still works', function () {
    $waiterId = phaseBWaiterUserId();

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Cliente walk-in histórico',
        'waiter_user_id' => $waiterId,
    ], phaseBAdminHeaders())->assertCreated();

    expect($order->json('data.order.table_label'))->toBe('Cliente walk-in histórico')
        ->and($order->json('data.order.service_table_id'))->toBeNull();
});
