<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function directSaleSeedProduct(array $extraPrices = [], float $soloPrice = 10): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'Galleta',
        'product_type' => 'food',
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

    foreach ($extraPrices as $priceRow) {
        ProductPriceModel::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'currency' => 'BOB',
            'status' => 'active',
        ], $priceRow));
    }

    return (int) $product->id;
}

function directSalePayload(int $productId, array $overrides = []): array
{
    return array_merge([
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 10],
        ],
    ], $overrides);
}

// -----------------------------------------------------------------------
// 1. Cajera puede venta directa con caja abierta (SOLO_CLIENTE)
// -----------------------------------------------------------------------
it('cajera can create direct sale with open cash session', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token));

    $response->assertCreated()
        ->assertJsonPath('data.sale.order_id', null)
        ->assertJsonPath('data.sale.total', '10.00')
        ->assertJsonPath('data.sale.payment_mode', 'CASH')
        ->assertJsonPath('data.sale.items.0.product_name_snapshot', 'Galleta')
        ->assertJsonPath('data.sale.items.0.order_item_id', null);

    expect(SaleModel::query()->whereNull('order_id')->exists())->toBeTrue();
    expect(SaleItemModel::query()->whereNull('order_item_id')->exists())->toBeTrue();
});

// -----------------------------------------------------------------------
// 2. Venta directa crea sale con order_id null
// -----------------------------------------------------------------------
it('direct sale creates sale record with order_id null', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    $sale = SaleModel::query()->whereNull('order_id')->first();
    expect($sale)->not->toBeNull();
    expect($sale->order_id)->toBeNull();
    expect($sale->status)->toBe('PAID');
});

// -----------------------------------------------------------------------
// 3. Venta directa crea sale_items con order_item_id null
// -----------------------------------------------------------------------
it('direct sale creates sale_items with order_item_id null', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    $item = SaleItemModel::query()->whereNull('order_item_id')->first();
    expect($item)->not->toBeNull();
    expect($item->quantity)->toBe(1);
    expect($item->unit_price_snapshot)->toBe('10.00');
    expect($item->line_total)->toBe('10.00');
});

// -----------------------------------------------------------------------
// 4. Venta directa crea payment
// -----------------------------------------------------------------------
it('direct sale creates sale payment record', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    $saleId = $response->json('data.sale.id');
    expect($response->json('data.sale.payments.0.payment_method'))->toBe('CASH');
    expect($response->json('data.sale.payments.0.amount'))->toBe('10.00');
});

// -----------------------------------------------------------------------
// 5. Venta directa crea movimiento de caja (INCOME)
// -----------------------------------------------------------------------
it('direct sale creates INCOME cash movement', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    expect(
        CashMovementModel::query()
            ->where('movement_type', 'INCOME')
            ->where('description', 'like', 'Venta directa%')
            ->exists()
    )->toBeTrue();
});

// -----------------------------------------------------------------------
// 6. No hay caja abierta → rechaza
// -----------------------------------------------------------------------
it('rejects direct sale without open cash session', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Debe tener una caja abierta para cobrar.');
});

// -----------------------------------------------------------------------
// 7. Garzón no puede venta directa (sin permiso)
// -----------------------------------------------------------------------
it('denies waiter from creating direct sale', function () {
    $cashierToken = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($cashierToken);

    $waiterToken = nightposLoginPin('5678');

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($waiterToken))
        ->assertForbidden();
});

// -----------------------------------------------------------------------
// 8. CON_ACOMPANANTE sin chica → rechaza
// -----------------------------------------------------------------------
it('rejects CON_ACOMPANANTE item without girl_user_id', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 20, 'girl_amount' => 10, 'house_amount' => 10],
    ]);
    nightposOpenCashSession($token);

    $payload = [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'CON_ACOMPANANTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [['method' => 'CASH', 'amount' => 20]],
    ];

    test()->postJson('/api/v1/direct-sales', $payload, nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Hay ítems CON_ACOMPANANTE sin chica asignada.');
});

// -----------------------------------------------------------------------
// 9. CON_ACOMPANANTE con chica → acepta y alimenta liquidación
// -----------------------------------------------------------------------
it('accepts CON_ACOMPANANTE item with girl and stores girl snapshots', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 20, 'girl_amount' => 10, 'house_amount' => 10],
    ]);
    nightposOpenCashSession($token);

    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    $payload = [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'CON_ACOMPANANTE', 'quantity' => 1, 'girl_user_id' => $girlId],
        ],
        'payments' => [['method' => 'CASH', 'amount' => 20]],
    ];

    $response = test()->postJson('/api/v1/direct-sales', $payload, nightposOperationalHeaders($token))
        ->assertCreated();

    expect($response->json('data.sale.items.0.girl_user_id'))->toBe($girlId);
    expect($response->json('data.sale.items.0.girl_amount_snapshot'))->toBe('10.00');
    expect($response->json('data.sale.items.0.house_amount_snapshot'))->toBe('10.00');
});

// -----------------------------------------------------------------------
// 10. Multi-tenant: venta directa de tenant A no visible en tenant B
// -----------------------------------------------------------------------
it('tenant isolation: direct sale not visible to other tenant', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct();
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', directSalePayload($productId), nightposOperationalHeaders($token))
        ->assertCreated();

    $otherTenant = TenantModel::query()->where('slug', '!=', 'casa-demo')->first();

    if ($otherTenant) {
        expect(
            SaleModel::query()
                ->whereNull('order_id')
                ->where('tenant_id', $otherTenant->id)
                ->exists()
        )->toBeFalse();
    } else {
        expect(SaleModel::query()->whereNull('order_id')->count())->toBe(1);
    }
});

// -----------------------------------------------------------------------
// 11. Producto sin precio activo → 422 con mensaje claro (DSP-4)
// -----------------------------------------------------------------------
it('rejects direct sale of product without active price', function () {
    $token = nightposLoginPin('1234');
    nightposOpenCashSession($token);

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    // Producto creado SIN ninguna fila en product_prices
    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'Producto sin precio',
        'product_type' => 'food',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    $payload = [
        'items' => [
            ['product_id' => (int) $product->id, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [['method' => 'CASH', 'amount' => 10]],
    ];

    test()->postJson('/api/v1/direct-sales', $payload, nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Este producto no tiene precio configurado para la modalidad SOLO_CLIENTE.');

    expect(SaleModel::query()->whereNull('order_id')->exists())->toBeFalse();
});

// -----------------------------------------------------------------------
// 12. Pago mixto efectivo + QR
// -----------------------------------------------------------------------
it('accepts direct sale with mixed CASH and QR payments', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([], 200);
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 100],
            ['method' => 'QR', 'amount' => 100],
        ],
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.sale.payment_mode', 'MIXED')
        ->assertJsonPath('data.sale.total', '200.00');

    expect($response->json('data.sale.payments'))->toHaveCount(2);

    expect(
        CashMovementModel::query()
            ->where('movement_type', 'INCOME')
            ->where('payment_method', 'CASH')
            ->where('amount', '100.00')
            ->exists()
    )->toBeTrue();

    expect(
        CashMovementModel::query()
            ->where('movement_type', 'INCOME')
            ->where('payment_method', 'QR')
            ->where('amount', '100.00')
            ->exists()
    )->toBeTrue();
});

// -----------------------------------------------------------------------
// 13. Pago mixto efectivo + QR + tarjeta
// -----------------------------------------------------------------------
it('accepts direct sale with CASH QR and CARD payments', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([], 200);
    nightposOpenCashSession($token);

    $response = test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 100],
            ['method' => 'QR', 'amount' => 70],
            ['method' => 'CARD', 'amount' => 30],
        ],
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.sale.payment_mode', 'MIXED');

    expect($response->json('data.sale.payments'))->toHaveCount(3);
});

// -----------------------------------------------------------------------
// 14. Rechaza pagos menores al total
// -----------------------------------------------------------------------
it('rejects direct sale when payments sum is less than total', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([], 200);
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 100],
            ['method' => 'QR', 'amount' => 50],
        ],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'La suma de pagos no coincide con el total de la comanda.');

    expect(SaleModel::query()->whereNull('order_id')->exists())->toBeFalse();
});

// -----------------------------------------------------------------------
// 15. Rechaza pagos mayores al total
// -----------------------------------------------------------------------
it('rejects direct sale when payments sum exceeds total', function () {
    $token = nightposLoginPin('1234');
    $productId = directSaleSeedProduct([], 200);
    nightposOpenCashSession($token);

    test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 150],
            ['method' => 'QR', 'amount' => 100],
        ],
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'La suma de pagos no coincide con el total de la comanda.');
});
