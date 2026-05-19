<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('moves stock between sites and records inventory movements', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'TR-A',
        'name' => 'Sucursal A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'TR-B',
        'name' => 'Sucursal B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);
    $now = now();
    DB::table('user_site_accesses')->insert([
        [
            'user_id' => $admin->id,
            'site_id' => $siteA,
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'user_id' => $admin->id,
            'site_id' => $siteB,
            'is_default' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'TR-PEPSI',
        'name' => 'Pepsi',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 10,
        'purchase_price' => 50,
        'track_stock' => true,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/transfers', [
        'to_site_id' => $siteB,
        'document_ref' => 'R-99',
        'lines' => [
            ['product_id' => $productId, 'quantity' => 3],
        ],
    ])->assertCreated();

    $qtyA = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteA)
        ->where('product_id', $productId)
        ->value('quantity');
    $qtyB = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteB)
        ->where('product_id', $productId)
        ->value('quantity');
    expect($qtyA)->toBe(7);
    expect($qtyB)->toBe(3);

    $base = (int) DB::table('products')->where('id', $productId)->value('base_stock');
    expect($base)->toBe(10);

    $lines = DB::table('site_stock_transfer_lines')->where('product_id', $productId)->get();
    expect($lines)->toHaveCount(1);
    $lineId = (int) $lines->first()->id;

    $movements = DB::table('inventory_movements')
        ->where('reference_type', 'site_stock_transfer_line')
        ->where('reference_id', $lineId)
        ->orderBy('site_id')
        ->get();
    expect($movements)->toHaveCount(2);
});

it('rejects transfer when stock is insufficient', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'TR2-A',
        'name' => 'A2',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'TR2-B',
        'name' => 'B2',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);
    $now = now();
    DB::table('user_site_accesses')->insert([
        [
            'user_id' => $admin->id,
            'site_id' => $siteA,
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'user_id' => $admin->id,
            'site_id' => $siteB,
            'is_default' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'TR-SMALL',
        'name' => 'Small',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 2,
        'track_stock' => true,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/transfers', [
        'to_site_id' => $siteB,
        'lines' => [
            ['product_id' => $productId, 'quantity' => 10],
        ],
    ])->assertStatus(422);
});

it('lists and shows transfer detail for involved branch', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'TR3-A',
        'name' => 'A3',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'TR3-B',
        'name' => 'B3',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);
    $now = now();
    DB::table('user_site_accesses')->insert([
        [
            'user_id' => $admin->id,
            'site_id' => $siteA,
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'user_id' => $admin->id,
            'site_id' => $siteB,
            'is_default' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'TR-LIST',
        'name' => 'List',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 5,
        'track_stock' => true,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $res = $this->postJson('/api/maintenance/transfers', [
        'to_site_id' => $siteB,
        'lines' => [
            ['product_id' => $productId, 'quantity' => 1],
        ],
    ])->assertCreated();

    $tid = $res->json('data.site_stock_transfer_id');

    $list = $this->getJson('/api/maintenance/transfers')->assertOk();
    $ids = collect($list->json('data'))->pluck('id')->all();
    expect($ids)->toContain($tid);

    $detail = $this->getJson("/api/maintenance/transfers/{$tid}")->assertOk();
    expect($detail->json('data.lines'))->toHaveCount(1);
    expect($detail->json('data.lines.0.sku'))->toBe('TR-LIST');
});
