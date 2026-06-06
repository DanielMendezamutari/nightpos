<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
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
    nightposEnsureShiftOpen();
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function reconToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function reconGet(string $token, array $query = []): array
{
    $qs = empty($query) ? '' : ('?' . http_build_query($query));

    return test()->withHeaders(nightposOperationalHeaders($token))
        ->get("/api/v1/reports/product-reconciliation{$qs}")
        ->assertOk()
        ->json('data');
}

function reconRow(array $data, string $section, int $productId): ?array
{
    foreach ($data[$section] as $row) {
        if ((int) $row['product_id'] === $productId) {
            return $row;
        }
    }

    return null;
}

function reconChargeOrder(string $token, int $orderId, float $amount = 50): void
{
    nightposOpenCashSession($token);
    $headers = nightposOperationalHeaders($token);
    test()->withHeaders($headers)->post("/api/v1/orders/{$orderId}/send-to-bar")->assertSuccessful();
    test()->withHeaders($headers)->post("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $amount]],
    ])->assertSuccessful();
}

function reconSeedExtraProduct(string $name = 'Producto Extra', float $price = 25): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id'       => $tenantId,
        'branch_id'       => null,
        'name'            => $name,
        'product_type'    => 'beverage',
        'unit'            => 'unit',
        'track_inventory' => false,
        'status'          => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id'  => $tenantId,
        'branch_id'  => $branchId,
        'product_id' => $product->id,
        'sale_mode'  => 'SOLO_CLIENTE',
        'price'      => $price,
        'currency'   => 'BOB',
        'status'     => 'active',
    ]);

    return (int) $product->id;
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('1. comanda cobrada normal da OK', function () {
    $token   = reconToken();
    $result  = nightposCreateOrderWithItem($token);
    reconChargeOrder($token, $result['order_id']);

    $data = reconGet($token);
    $row  = reconRow($data, 'comparison', $result['product_id']);

    expect($row)->not->toBeNull();
    expect($row['status'])->toBe('OK');
    expect($row['ordered_quantity'])->toBe(2);
    expect($row['sold_quantity'])->toBe(2);
    expect($row['difference_quantity'])->toBe(0);
});

it('2. venta directa aparece como DIRECT_SALE_ONLY', function () {
    $token   = reconToken();
    $headers = nightposOperationalHeaders($token);

    nightposOpenCashSession($token);

    $productId = nightposSeedOrderProduct();
    $priceId   = (int) ProductPriceModel::query()->where('product_id', $productId)->value('id');

    $this->withHeaders($headers)
        ->post('/api/v1/direct-sales', [
            'items'    => [['product_id' => $productId, 'price_id' => $priceId, 'quantity' => 1, 'sale_mode' => 'SOLO_CLIENTE']],
            'payments' => [['method' => 'CASH', 'amount' => 25]],
        ])
        ->assertCreated();

    $data = reconGet($token);
    $row  = reconRow($data, 'comparison', $productId);

    expect($row)->not->toBeNull();
    expect($row['status'])->toBe('DIRECT_SALE_ONLY');
    expect($row['sold_quantity'])->toBe(1);
    expect($row['ordered_quantity'])->toBe(0);

    $sold = reconRow($data, 'sold', $productId);
    expect($sold['direct_sale_quantity'])->toBe(1);
    expect($sold['order_sale_quantity'])->toBe(0);
});

it('3. comanda abierta sin cobrar queda pendiente', function () {
    $token  = reconToken();
    $result = nightposCreateOrderWithItem($token); // order stays OPEN

    $data = reconGet($token);
    $row  = reconRow($data, 'comparison', $result['product_id']);

    expect($row)->not->toBeNull();
    expect($row['status'])->toBe('PENDING_NOT_SOLD');
    expect($row['sold_quantity'])->toBe(0);

    $ordered = reconRow($data, 'ordered', $result['product_id']);
    expect($ordered['open_quantity'])->toBe(2);
    expect($ordered['billed_quantity'])->toBe(0);
});

it('4. linea cancelada no cuenta como vendida', function () {
    $token   = reconToken();
    $headers = nightposOperationalHeaders($token);

    $result  = nightposCreateOrderWithItem($token); // product A, qty 2
    $orderId = $result['order_id'];
    $productA = $result['product_id'];

    // Add a second product B that will actually be billed
    $productB = reconSeedExtraProduct('Producto B');
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productB,
        'sale_mode'  => 'SOLO_CLIENTE',
        'quantity'   => 1,
    ])->assertCreated();

    // Send to bar (items become SENT) then cancel product A line
    nightposOpenCashSession($token);
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/send-to-bar")->assertSuccessful();

    $itemA = (int) OrderItemModel::query()
        ->where('order_id', $orderId)
        ->where('product_id', $productA)
        ->value('id');
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/items/{$itemA}/cancel", [
        'reason' => 'Cliente cambió de opinión',
    ])->assertSuccessful();

    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 25]],
    ])->assertSuccessful();

    $data = reconGet($token);

    $rowA = reconRow($data, 'comparison', $productA);
    expect($rowA['sold_quantity'])->toBe(0);
    expect($rowA['status'])->toBe('CANCELLED');

    $rowB = reconRow($data, 'comparison', $productB);
    expect($rowB['status'])->toBe('OK');
    expect($rowB['sold_quantity'])->toBe(1);
});

it('5. producto corregido antes de cobrar refleja producto final', function () {
    $token   = reconToken();
    $headers = nightposOperationalHeaders($token);

    $result  = nightposCreateOrderWithItem($token, [], ['quantity' => 1]); // product A qty 1
    $orderId = $result['order_id'];
    $productA = $result['product_id'];

    // Correction: original A plus final product B, then cancel A before charging
    $productB = reconSeedExtraProduct('Producto Final B');
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productB,
        'sale_mode'  => 'SOLO_CLIENTE',
        'quantity'   => 1,
    ])->assertCreated();

    nightposOpenCashSession($token);
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/send-to-bar")->assertSuccessful();

    $itemA = (int) OrderItemModel::query()
        ->where('order_id', $orderId)
        ->where('product_id', $productA)
        ->value('id');
    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/items/{$itemA}/cancel", [
        'reason' => 'Corrección: cliente pidió otro producto',
    ])->assertSuccessful();

    $this->withHeaders($headers)->post("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 25]],
    ])->assertSuccessful();

    $data = reconGet($token);

    $rowB = reconRow($data, 'comparison', $productB);
    expect($rowB)->not->toBeNull();
    expect($rowB['status'])->toBe('OK');
    expect($rowB['sold_quantity'])->toBe(1);

    // The corrected-away product must not count as sold
    $rowA = reconRow($data, 'comparison', $productA);
    expect($rowA['sold_quantity'])->toBe(0);
});

it('6. sale_item con order_item_id coincide y cuenta como venta de comanda', function () {
    $token  = reconToken();
    $result = nightposCreateOrderWithItem($token);
    reconChargeOrder($token, $result['order_id']);

    $data = reconGet($token);
    $sold = reconRow($data, 'sold', $result['product_id']);

    expect($sold)->not->toBeNull();
    expect($sold['order_sale_quantity'])->toBe(2);
    expect($sold['direct_sale_quantity'])->toBe(0);

    // sale_item is linked to the order_item
    expect(SaleItemModel::query()
        ->where('product_id', $result['product_id'])
        ->whereNotNull('order_item_id')
        ->count())->toBeGreaterThan(0);
});

it('7. diferencia de cantidad se detecta como QUANTITY_MISMATCH', function () {
    $token  = reconToken();
    $result = nightposCreateOrderWithItem($token);
    reconChargeOrder($token, $result['order_id']);

    // Tamper the sold quantity so it no longer matches the billed order
    SaleItemModel::query()
        ->where('product_id', $result['product_id'])
        ->whereNotNull('order_item_id')
        ->update(['quantity' => 1]);

    $data = reconGet($token);
    $row  = reconRow($data, 'comparison', $result['product_id']);

    expect($row['status'])->toBe('QUANTITY_MISMATCH');
    expect($row['ordered_quantity'])->toBe(2);
    expect($row['sold_quantity'])->toBe(1);
    expect($row['difference_quantity'])->toBe(-1);
    expect($data['summary']['has_differences'])->toBeTrue();
});

it('8. tenant isolation: datos de otro tenant no aparecen', function () {
    $token   = reconToken();
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $otherTenant = TenantModel::query()->create([
        'name'   => 'Otro Tenant',
        'slug'   => 'otro-tenant',
        'status' => 'active',
    ]);
    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name'      => 'Otra Sucursal',
        'code'      => 'OTRA',
        'status'    => 'active',
    ]);

    // Product belongs to tenant1 but is only used inside the foreign tenant's order
    $productId = reconSeedExtraProduct('Producto Ajeno Tenant');

    $order = OrderModel::query()->create([
        'tenant_id'         => $otherTenant->id,
        'branch_id'         => $otherBranch->id,
        'order_number'      => 'OT-001',
        'status'            => 'BILLED',
        'opened_by_user_id' => $adminId,
        'subtotal'          => 50,
        'total'             => 50,
        'currency'          => 'BOB',
    ]);
    OrderItemModel::query()->create([
        'tenant_id'    => $otherTenant->id,
        'branch_id'    => $otherBranch->id,
        'order_id'     => $order->id,
        'product_id'   => $productId,
        'product_name' => 'Producto Ajeno Tenant',
        'sale_mode'    => 'SOLO_CLIENTE',
        'quantity'     => 5,
        'unit_price'   => 10,
        'line_total'   => 50,
        'item_status'  => 'PENDING',
    ]);

    $data = reconGet($token);

    expect(reconRow($data, 'comparison', $productId))->toBeNull();
    expect(reconRow($data, 'ordered', $productId))->toBeNull();
});

it('9. branch isolation: datos de otra sucursal no aparecen', function () {
    $token    = reconToken();
    $adminId  = (int) UserModel::query()->where('username', 'admin.demo')->value('id');
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name'      => 'Sucursal Norte',
        'code'      => 'NORTE',
        'status'    => 'active',
    ]);

    $productId = reconSeedExtraProduct('Producto Otra Sucursal');

    $order = OrderModel::query()->create([
        'tenant_id'         => $tenantId,
        'branch_id'         => $otherBranch->id,
        'order_number'      => 'NB-001',
        'status'            => 'BILLED',
        'opened_by_user_id' => $adminId,
        'subtotal'          => 30,
        'total'             => 30,
        'currency'          => 'BOB',
    ]);
    OrderItemModel::query()->create([
        'tenant_id'    => $tenantId,
        'branch_id'    => $otherBranch->id,
        'order_id'     => $order->id,
        'product_id'   => $productId,
        'product_name' => 'Producto Otra Sucursal',
        'sale_mode'    => 'SOLO_CLIENTE',
        'quantity'     => 3,
        'unit_price'   => 10,
        'line_total'   => 30,
        'item_status'  => 'PENDING',
    ]);

    $data = reconGet($token);

    expect(reconRow($data, 'comparison', $productId))->toBeNull();
});

it('10. filtra por cash_session_id', function () {
    $token  = reconToken();
    $result = nightposCreateOrderWithItem($token);
    reconChargeOrder($token, $result['order_id']);

    $sessionId = (int) SaleModel::query()
        ->where('order_id', $result['order_id'])
        ->value('cash_session_id');

    // With the right session the product is present
    $matching = reconGet($token, ['cash_session_id' => $sessionId]);
    expect(reconRow($matching, 'comparison', $result['product_id']))->not->toBeNull();

    // With a non-existing session the product is excluded
    $empty = reconGet($token, ['cash_session_id' => 999999]);
    expect(reconRow($empty, 'comparison', $result['product_id']))->toBeNull();
});
