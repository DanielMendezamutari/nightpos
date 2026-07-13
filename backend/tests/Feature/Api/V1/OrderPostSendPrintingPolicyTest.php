<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function postSendAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function postSendWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function postSendCashierToken(): string
{
    return nightposLoginPin('1234');
}

function postSendRegisterDevice(): void
{
    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'PostSend Device '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders(postSendAdminToken()))->assertCreated();
}

function postSendCreateSimpleProduct(string $name = 'PostSend Producto'): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => $name,
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 30,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 50,
        'girl_amount' => 20,
        'house_amount' => 30,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}

function postSendCreateAllocationProduct(int $braceletUnits = 6): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'PostSend Combo '.uniqid(),
        'product_type' => 'beverage',
        'unit' => 'combo',
        'status' => 'active',
        'settlement_behavior' => 'GIRL_BRACELET_ALLOCATION',
        'bracelet_units_per_line' => $braceletUnits,
        'requires_allocation' => true,
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    ProductPriceModel::query()->create([
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

/** @return array{orderId:int,itemId:int,productId:int} */
function postSendCreateOrderAndSend(): array
{
    postSendRegisterDevice();
    $waiter = postSendWaiterToken();
    $productId = postSendCreateSimpleProduct();

    $created = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Mesa PostSend',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ]);

    $orderId = (int) $created['order_id'];
    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    return compact('orderId', 'itemId', 'productId');
}

function postSendOrderPrintJobs(int $orderId): \Illuminate\Support\Collection
{
    return PrintJobModel::query()
        ->where('source_type', 'order')
        ->where('source_id', $orderId)
        ->orderBy('id')
        ->get();
}

it('primer send-to-bar crea un unico print job inicial', function () {
    ['orderId' => $orderId] = postSendCreateOrderAndSend();

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1)
        ->and(postSendOrderPrintJobs($orderId)->first()->type)->toBe('ORDER_COMMAND');
});

it('repetir send-to-bar no duplica impresion inicial', function () {
    ['orderId' => $orderId] = postSendCreateOrderAndSend();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders(postSendWaiterToken()))
        ->assertStatus(422);

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('agregar producto despues de SENT_TO_BAR no crea print job', function () {
    ['orderId' => $orderId] = postSendCreateOrderAndSend();
    $productId = postSendCreateSimpleProduct('Producto agregado');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders(postSendCashierToken()))->assertCreated();

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('modificar cantidad despues de SENT_TO_BAR no crea print job', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = postSendCreateOrderAndSend();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 2,
    ], nightposOperationalHeaders(postSendCashierToken()))->assertStatus(422);

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('cancelar producto despues de SENT_TO_BAR no crea print job', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = postSendCreateOrderAndSend();

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Error de pedido',
    ], nightposOperationalHeaders(postSendCashierToken()))->assertOk();

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('cambiar chica despues de SENT_TO_BAR no crea print job', function () {
    postSendRegisterDevice();
    $waiter = postSendWaiterToken();
    $cashier = postSendCashierToken();
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    $productId = postSendCreateSimpleProduct('Producto chica');

    $created = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Mesa Chica',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ]);

    $orderId = (int) $created['order_id'];
    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    $girl2Id = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');
    $before = OperationalEventModel::query()->where('type', 'order.item_girl_changed')->count();

    test()->patchJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'girl_user_id' => $girl2Id,
        'reason' => 'Cambio operativo de sala',
    ], nightposOperationalHeaders($cashier))->assertOk();

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
    expect(OperationalEventModel::query()->where('type', 'order.item_girl_changed')->count())->toBe($before + 1);
});

it('requiere motivo al cambiar chica despues de SENT_TO_BAR', function () {
    postSendRegisterDevice();
    $waiter = postSendWaiterToken();
    $cashier = postSendCashierToken();
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    $productId = postSendCreateSimpleProduct('Producto chica sin motivo');

    $created = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Mesa Chica sin motivo',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ]);

    $orderId = (int) $created['order_id'];
    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    $girl2Id = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');
    test()->patchJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'girl_user_id' => $girl2Id,
    ], nightposOperationalHeaders($cashier))->assertStatus(422);

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('sincronizar allocations despues de SENT_TO_BAR no crea print job', function () {
    postSendRegisterDevice();
    $waiter = postSendWaiterToken();
    $girl1 = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $girl2 = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');

    $productId = postSendCreateAllocationProduct();

    $created = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Mesa Combo',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ]);

    $orderId = (int) $created['order_id'];
    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [
            ['girl_user_id' => $girl1, 'units' => 3],
            ['girl_user_id' => $girl2, 'units' => 3],
        ],
    ], nightposOperationalHeaders($waiter))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))->assertOk();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}/allocations", [
        'allocations' => [
            ['girl_user_id' => $girl1, 'units' => 2],
            ['girl_user_id' => $girl2, 'units' => 4],
        ],
    ], nightposOperationalHeaders($waiter))->assertOk();

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(1);
});

it('precuenta manual refleja estado actualizado', function () {
    ['orderId' => $orderId] = postSendCreateOrderAndSend();
    $cashier = postSendCashierToken();
    $productId = postSendCreateSimpleProduct('Producto precheck actualizado');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 2,
    ], nightposOperationalHeaders($cashier))->assertCreated();

    $precheck = test()->getJson("/api/v1/orders/{$orderId}/precheck", nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data.precheck');

    $names = collect($precheck['order']['items'] ?? [])->pluck('product_name')->filter()->all();
    expect($names)->toContain('Producto precheck actualizado')
        ->and((float) ($precheck['order']['total'] ?? 0))->toBeGreaterThan(30.0);
});

it('reimpresion manual sigue funcionando bajo solicitud explicita', function () {
    ['orderId' => $orderId] = postSendCreateOrderAndSend();

    test()->postJson("/api/v1/orders/{$orderId}/reprint", [], nightposOperationalHeaders(postSendWaiterToken()))
        ->assertCreated()
        ->assertJsonPath('data.job.type', 'ORDER_COMMAND');

    expect(postSendOrderPrintJobs($orderId))->toHaveCount(2);
});

it('fallo de impresion inicial no bloquea enviar a barra', function () {
    $waiter = postSendWaiterToken();
    $productId = postSendCreateSimpleProduct('Sin impresora');

    $created = nightposCreateOrderWithItem($waiter, [
        'table_label' => 'Mesa sin impresora',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ]);

    $orderId = (int) $created['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    expect((string) OrderModel::query()->find($orderId)?->status)->toBe('SENT_TO_BAR')
        ->and(postSendOrderPrintJobs($orderId))->toHaveCount(0);
});

it('no elimina print_jobs historicos tras correcciones posteriores', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = postSendCreateOrderAndSend();

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Historico intacto',
    ], nightposOperationalHeaders(postSendCashierToken()))->assertOk();

    $jobs = postSendOrderPrintJobs($orderId);
    expect($jobs)->toHaveCount(1)
        ->and($jobs->first()->idempotency_key)->toContain("order_command:{$orderId}");
});
