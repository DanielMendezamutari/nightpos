<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function productChangeCashierToken(): string
{
    return nightposLoginPin('1234');
}

function productChangeWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function productChangeGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function productChangeSeedNamedProduct(string $name, float $soloPrice, bool $withCompanion = false, ?float $companionPrice = null): int
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
        'price' => $soloPrice,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    if ($withCompanion) {
        ProductPriceModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'sale_mode' => 'CON_ACOMPANANTE',
            'price' => $companionPrice ?? 55,
            'girl_amount' => 25,
            'house_amount' => ($companionPrice ?? 55) - 25,
            'currency' => 'BOB',
            'status' => 'active',
        ]);
    }

    return (int) $product->id;
}

function productChangeCreateOrderWithProduct(int $productId, string $saleMode = 'SOLO_CLIENTE', int $quantity = 1): array
{
    nightposEnsureShiftOpen();
    $waiter = productChangeWaiterToken();

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Producto',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($waiter))->assertCreated();

    $orderId = (int) $orderResponse->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => $saleMode,
        'quantity' => $quantity,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    $itemId = (int) OrderItemModel::query()->where('order_id', $orderId)->value('id');

    return compact('orderId', 'itemId');
}

it('allows cashier to change product Corona to Ice 51 on OPEN order', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $cashier = productChangeCashierToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
    ], nightposOperationalHeaders($cashier))->assertOk()
        ->assertJsonPath('data.order.items.0.product_name', 'Ice 51');

    $item = OrderItemModel::query()->find($itemId);

    expect((int) $item->product_id)->toBe($iceId)
        ->and($item->product_name)->toBe('Ice 51')
        ->and((float) $item->unit_price)->toBe(35.0)
        ->and((float) OrderModel::query()->find($orderId)->total)->toBe(35.0);
});

it('recalculates girl and house amounts when changing to CON_ACOMPANANTE product', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35, true, 60);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $cashier = productChangeCashierToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'girl_user_id' => productChangeGirlId(),
    ], nightposOperationalHeaders($cashier))->assertOk();

    $item = OrderItemModel::query()->find($itemId);

    expect($item->sale_mode)->toBe('CON_ACOMPANANTE')
        ->and((float) $item->unit_price)->toBe(60.0)
        ->and((float) $item->girl_amount)->toBe(25.0)
        ->and((float) $item->house_amount)->toBe(35.0);
});

it('returns clear error when new product has no price for sale mode', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $noPriceId = productChangeSeedNamedProduct('Sin Precio Comp', 15, false);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $cashier = productChangeCashierToken();

    $response = test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $noPriceId,
        'sale_mode' => 'CON_ACOMPANANTE',
    ], nightposOperationalHeaders($cashier));

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Este producto no tiene precio configurado para la modalidad CON_ACOMPANANTE.');
});

it('denies waiter from changing product', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $waiter = productChangeWaiterToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
    ], nightposOperationalHeaders($waiter))->assertForbidden();
});

it('does not allow product change on BILLED order', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $cashier = productChangeCashierToken();
    nightposOpenCashSession($cashier);

    $total = (float) OrderModel::query()->find($orderId)->total;

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $total]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
    ], nightposOperationalHeaders($cashier))->assertStatus(422);
});

it('requires reason to change product on SENT_TO_BAR line', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $waiter = productChangeWaiterToken();
    $cashier = productChangeCashierToken();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiter))
        ->assertOk();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
    ], nightposOperationalHeaders($cashier))->assertStatus(422);

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
        'reason' => 'Cliente pidió Ice 51 en lugar de Corona',
    ], nightposOperationalHeaders($cashier))->assertOk()
        ->assertJsonPath('data.order.items.0.product_name', 'Ice 51');
});

it('records audit log when product is changed', function () {
    $coronaId = productChangeSeedNamedProduct('Corona', 20);
    $iceId = productChangeSeedNamedProduct('Ice 51', 35);
    ['orderId' => $orderId, 'itemId' => $itemId] = productChangeCreateOrderWithProduct($coronaId);
    $cashier = productChangeCashierToken();

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'product_id' => $iceId,
    ], nightposOperationalHeaders($cashier))->assertOk();

    $log = AuditLogModel::query()
        ->where('action', 'order.item_product_changed')
        ->where('subject_id', $orderId)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata['previous_product_name'])->toBe('Corona')
        ->and($log->metadata['new_product_name'])->toBe('Ice 51');
});
