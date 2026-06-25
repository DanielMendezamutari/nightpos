<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function precheckPrintRegisterDevice(string $token = null): array
{
    $token ??= nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $response = test()->postJson('/api/v1/print-devices/register', [
        'name' => 'Precheck Device',
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token));

    $response->assertCreated();

    return [
        'device' => $response->json('data.device'),
        'device_key' => $response->json('data.device_key'),
    ];
}

function precheckPrintWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function precheckPrintCreateOrder(string $token, array $itemPayload = []): array
{
    return nightposCreateOrderWithItem($token, ['table_label' => 'Mesa Precheck'], $itemPayload);
}

it('waiter can create PRECHECK print job', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $created = precheckPrintCreateOrder($waiter);

    test()->postJson("/api/v1/orders/{$created['order_id']}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated()
        ->assertJsonPath('data.job.type', 'PRECHECK')
        ->assertJsonPath('data.job.source_id', $created['order_id'])
        ->assertJsonPath('data.job.status', 'PENDING');
});

it('PRECHECK content includes total', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $created = precheckPrintCreateOrder($waiter);

    test()->postJson("/api/v1/orders/{$created['order_id']}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated();

    $content = test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!')))
        ->json('data.jobs.0.content_text');

    expect($content)->toContain('TOTAL');
});

it('PRECHECK content includes manilla for CON_ACOMPANANTE item', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $productId = nightposSeedOrderProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 80, 'girl_amount' => 40, 'house_amount' => 40],
    ]);

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Acomp',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($waiter))->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated();

    $girlName = (string) UserModel::query()->where('id', $girlId)->value('name');
    $content = test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!')))
        ->json('data.jobs.0.content_text');

    expect($content)->toContain('Manilla: '.$girlName);
});

it('PRECHECK content includes combo allocation distribution', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $productId = precheckSeedCombo();
    $girlId = precheckGirlId();
    $girlName = (string) UserModel::query()->where('id', $girlId)->value('name');

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Combo Precheck',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($waiter))->json('data.order.id');

    $itemId = (int) test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiter))->json('data.order.items.0.id');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [
            ['girl_user_id' => $girlId, 'units' => 6],
        ],
    ], nightposOperationalHeaders($waiter))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated();

    $content = test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!')))
        ->json('data.jobs.0.content_text');

    expect($content)->toContain($girlName)
        ->and($content)->toContain('x6');
});

it('does not allow precheck print for order from another branch', function () {
    precheckPrintRegisterDevice();
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')->value('id');

    $centroId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')->value('id');

    $otherBranch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('tenant_id', $tenantId)
        ->where('code', 'NORTE')
        ->first();

    if ($otherBranch === null) {
        $otherBranch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->create([
            'tenant_id' => $tenantId,
            'code' => 'NORTE',
            'name' => 'Sucursal Norte Test',
            'status' => 'active',
        ]);
    }

    $orderId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranch->id,
        'official_shift_id' => null,
        'order_number' => 'NORTE-PRECHECK',
        'status' => 'OPEN',
        'table_label' => 'Mesa Norte',
        'waiter_user_id' => nightposDemoWaiterUserId(),
        'opened_by_user_id' => nightposDemoWaiterUserId(),
        'subtotal' => 25,
        'total' => 50,
        'currency' => 'BOB',
    ])->id;

    test()->postJson("/api/v1/orders/{$orderId}/precheck/print", [], nightposOperationalHeaders($admin, 'CENTRO'))
        ->assertNotFound();

    expect($centroId)->not->toBe($otherBranch->id);
});

it('does not allow precheck print for cancelled order', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $created = precheckPrintCreateOrder($waiter);

    test()->postJson("/api/v1/orders/{$created['order_id']}/cancel", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    test()->postJson("/api/v1/orders/{$created['order_id']}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertStatus(422)
        ->assertJsonPath('message', 'La comanda ya fue cobrada o cancelada.');
});

it('PRECHECK print job stays PENDING until agent confirms', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $created = precheckPrintCreateOrder($waiter);

    $jobId = test()->postJson("/api/v1/orders/{$created['order_id']}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated()
        ->json('data.job.id');

    test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!')))
        ->assertJsonPath('data.jobs.0.id', $jobId)
        ->assertJsonPath('data.jobs.0.status', 'PENDING');
});

it('PRECHECK print job stores requested_by_user_id of current user', function () {
    precheckPrintRegisterDevice();
    $waiter = precheckPrintWaiterToken();
    $created = precheckPrintCreateOrder($waiter);

    $response = test()->postJson("/api/v1/orders/{$created['order_id']}/precheck/print", [], nightposOperationalHeaders($waiter))
        ->assertCreated();

    $requestedBy = $response->json('data.job.requested_by_user_id');

    expect($requestedBy)->toBeInt()->toBeGreaterThan(0);

    $meId = (int) test()->getJson('/api/v1/auth/me', nightposOperationalHeaders($waiter))
        ->json('data.user.id');

    expect($requestedBy)->toBe($meId);
});

function precheckSeedCombo(int $braceletUnits = 6): int
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')->value('id');

    $product = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'Combo Precheck Test',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'requires_allocation' => true,
        'bracelet_units_per_line' => $braceletUnits,
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 120,
        'girl_amount' => 60,
        'house_amount' => 60,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

function precheckGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}
