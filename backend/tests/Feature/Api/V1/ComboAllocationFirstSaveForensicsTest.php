<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\DB;

function forensicGirlIds(): array
{
    $girl1 = (int) UserModel::query()->where('username', 'chica.centro')->value('id');
    $girl2 = (int) UserModel::query()->where('username', 'chica2.demo')->value('id');

    expect($girl1)->toBeGreaterThan(0);
    expect($girl2)->toBeGreaterThan(0);

    return [$girl1, $girl2];
}

function forensicComboProductId(int $tenantId, int $branchId, int $braceletUnits = 6): int
{
    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Combo Forensics '.date('YmdHis'),
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

it('forensics: first save persists allocations and returns updated item in one request', function () {
    nightposEnsureShiftOpen();

    $waiterToken = nightposLoginPin('1234');
    [$girl1, $girl2] = forensicGirlIds();

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    expect($tenantId)->toBeGreaterThan(0);
    expect($branchId)->toBeGreaterThan(0);
    expect($waiterId)->toBeGreaterThan(0);

    $productId = forensicComboProductId($tenantId, $branchId, 6);

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Forensics Mesa Combo',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken));
    $orderResponse->assertCreated();

    $orderId = (int) $orderResponse->json('data.order.id');

    $addResponse = test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken));
    $addResponse->assertCreated();

    $addedItemId = (int) $addResponse->json('data.added_item_id');
    $addedItem = collect($addResponse->json('data.order.items', []))
        ->firstWhere('id', $addedItemId);

    expect($addedItemId)->toBeGreaterThan(0);
    expect($addedItem)->not->toBeNull();

    $requiredTotal = (int) ($addedItem['required_bracelet_units'] ?? 0);

    $beforeRows = DB::table('order_item_allocations')
        ->where('order_item_id', $addedItemId)
        ->orderBy('id')
        ->get(['id', 'girl_user_id', 'units'])
        ->map(static fn ($row) => (array) $row)
        ->all();

    $beforeAssigned = (int) DB::table('order_item_allocations')
        ->where('order_item_id', $addedItemId)
        ->sum('units');

    $requestPayload = [
        'allocations' => [
            ['girl_user_id' => $girl1, 'units' => 3],
            ['girl_user_id' => $girl2, 'units' => 3],
        ],
    ];

    $syncResponse = test()->putJson(
        "/api/v1/orders/{$orderId}/items/{$addedItemId}/allocations",
        $requestPayload,
        nightposOperationalHeaders($waiterToken),
    );

    $syncStatus = $syncResponse->getStatusCode();
    $syncBody = $syncResponse->json();

    $syncResponse->assertOk();

    $syncedItem = collect($syncResponse->json('data.order.items', []))
        ->firstWhere('id', $addedItemId);

    $afterRows = DB::table('order_item_allocations')
        ->where('order_item_id', $addedItemId)
        ->orderBy('id')
        ->get(['id', 'girl_user_id', 'units'])
        ->map(static fn ($row) => (array) $row)
        ->all();

    $afterAssigned = (int) DB::table('order_item_allocations')
        ->where('order_item_id', $addedItemId)
        ->sum('units');

    expect($requiredTotal)->toBe(6);
    expect($beforeRows)->toBe([]);
    expect($beforeAssigned)->toBe(0);
    expect($afterRows)->toHaveCount(2);
    expect($afterAssigned)->toBe(6);
    expect((int) ($syncedItem['required_bracelet_units'] ?? 0))->toBe(6);
    expect((int) ($syncedItem['allocated_bracelet_units'] ?? 0))->toBe(6);
    expect((bool) ($syncedItem['allocation_complete'] ?? false))->toBeTrue();
    expect((int) DB::table('orders')->where('id', $orderId)->count())->toBe(1);

    fwrite(STDOUT, "\n[FORensics Combo/Repartir]\n");
    fwrite(STDOUT, json_encode([
        'before_save' => [
            'order_id' => $orderId,
            'order_item_id' => $addedItemId,
            'required_total' => $requiredTotal,
            'assigned_total' => $beforeAssigned,
            'local_allocations_equivalent' => $requestPayload['allocations'],
            'sql_rows' => $beforeRows,
        ],
        'request' => [
            'endpoint' => "/api/v1/orders/{$orderId}/items/{$addedItemId}/allocations",
            'payload_json' => $requestPayload,
        ],
        'response' => [
            'http_status' => $syncStatus,
            'payload' => $syncBody,
        ],
        'after_save' => [
            'sql_rows' => $afterRows,
            'assigned_total' => $afterAssigned,
            'required_total' => (int) ($syncedItem['required_bracelet_units'] ?? 0),
            'item_returned' => $syncedItem,
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
});

it('forensics: two lines of same product keep allocations isolated by order_item_id', function () {
    nightposEnsureShiftOpen();

    $waiterToken = nightposLoginPin('1234');
    [$girl1, $girl2] = forensicGirlIds();

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $productId = forensicComboProductId($tenantId, $branchId, 6);

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Forensics Mesa Doble',
        'waiter_user_id' => $waiterId,
    ], nightposOperationalHeaders($waiterToken));
    $orderResponse->assertCreated();
    $orderId = (int) $orderResponse->json('data.order.id');

    $add1 = test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $add2 = test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
    ], nightposOperationalHeaders($waiterToken))->assertCreated();

    $itemId1 = (int) $add1->json('data.added_item_id');
    $itemId2 = (int) $add2->json('data.added_item_id');

    expect($itemId1)->toBeGreaterThan(0);
    expect($itemId2)->toBeGreaterThan(0);
    expect($itemId1)->not->toBe($itemId2);

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId2}/allocations", [
        'allocations' => [
            ['girl_user_id' => $girl1, 'units' => 2],
            ['girl_user_id' => $girl2, 'units' => 4],
        ],
    ], nightposOperationalHeaders($waiterToken))->assertOk();

    $item1Units = (int) DB::table('order_item_allocations')->where('order_item_id', $itemId1)->sum('units');
    $item2Units = (int) DB::table('order_item_allocations')->where('order_item_id', $itemId2)->sum('units');

    expect($item1Units)->toBe(0);
    expect($item2Units)->toBe(6);
});
