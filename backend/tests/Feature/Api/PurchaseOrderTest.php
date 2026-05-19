<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('registers purchase and increases branch stock', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-01',
        'name' => 'Compras test',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'PEPSI-LATA',
        'name' => 'Pepsi Lata',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 5,
        'purchase_price' => 40,
        'stock_min' => 2,
        'track_stock' => true,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'document_ref' => 'F-A-001',
        'lines' => [
            ['product_id' => $productId, 'quantity' => 12, 'unit_cost' => 38],
        ],
    ])->assertCreated();

    $qty = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteId)
        ->where('product_id', $productId)
        ->value('quantity');
    expect($qty)->toBe(17);

    $this->assertDatabaseHas('products', ['id' => $productId, 'purchase_price' => 38]);
});

it('registers purchase by box and converts to base stock units', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-02',
        'name' => 'Compras caja',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'LATA-12',
        'name' => 'Lata pack',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 4,
        'purchase_price' => 40,
        'stock_min' => 1,
        'track_stock' => true,
        'purchase_units_per_box' => 12,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'lines' => [
            [
                'product_id' => $productId,
                'purchase_packaging' => 'box',
                'pack_quantity' => 2,
                'units_per_pack' => 12,
                'cost_per_pack' => 600,
            ],
        ],
    ])->assertCreated();

    $qty = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteId)
        ->where('product_id', $productId)
        ->value('quantity');
    expect($qty)->toBe(28);

    $this->assertDatabaseHas('products', ['id' => $productId, 'purchase_price' => 50]);
});

it('rejects custom packaging without label', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-03',
        'name' => 'Compras custom',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'PAL-01',
        'name' => 'Palet test',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();

    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'lines' => [
            [
                'product_id' => $productId,
                'purchase_packaging' => 'custom',
                'pack_quantity' => 1,
                'units_per_pack' => 48,
                'cost_per_pack' => 100,
            ],
        ],
    ])->assertUnprocessable();
});

it('lists purchases with supplier and totals', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-LIST',
        'name' => 'Compras list',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'LIST-01',
        'name' => 'List product',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();
    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'document_ref' => 'F-L-1',
        'lines' => [
            ['product_id' => $productId, 'quantity' => 3, 'unit_cost' => 20],
        ],
    ])->assertCreated();

    $res = $this->getJson('/api/maintenance/purchases')->assertOk();
    $data = $res->json('data');
    expect($data)->toBeArray()->not->toBeEmpty();
    $row = collect($data)->firstWhere('document_ref', 'F-L-1');
    expect($row)->not->toBeNull();
    expect($row['line_count'])->toBe(1);
    expect($row['total_amount'])->toBe(60);
    expect($row['status'])->toBe('received');
});

it('shows purchase detail and cancels with stock reversal', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-SHOW',
        'name' => 'Compras show',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'CAN-01',
        'name' => 'Cancel test',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();
    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'lines' => [
            ['product_id' => $productId, 'quantity' => 5, 'unit_cost' => 10],
        ],
    ])->assertCreated();

    $orderId = (int) DB::table('purchase_orders')->orderByDesc('id')->value('id');
    $before = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteId)
        ->where('product_id', $productId)
        ->value('quantity');
    expect($before)->toBe(5);

    $this->getJson("/api/maintenance/purchases/{$orderId}")->assertOk()
        ->assertJsonPath('data.order.line_count', 1)
        ->assertJsonPath('data.order.status', 'received');

    $this->postJson("/api/maintenance/purchases/{$orderId}/cancel", [])->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $after = (int) DB::table('site_product_stocks')
        ->where('site_id', $siteId)
        ->where('product_id', $productId)
        ->value('quantity');
    expect($after)->toBe(0);

    expect((string) DB::table('purchase_orders')->where('id', $orderId)->value('status'))->toBe('cancelled');
});

it('attaches a pdf to a purchase and downloads it', function (): void {
    Storage::fake('public');

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-DOC',
        'name' => 'Compras doc',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'DOC-01',
        'name' => 'Doc test',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();
    $productId = $create->json('data.id');

    $pdf = UploadedFile::fake()->create('remito-f-9.pdf', 50, 'application/pdf');

    $this->post('/api/maintenance/purchases', [
        'lines' => json_encode([
            ['product_id' => $productId, 'quantity' => 1, 'unit_cost' => 20],
        ]),
        'document' => $pdf,
    ], [
        'Accept' => 'application/json',
    ])->assertCreated();

    $orderId = (int) DB::table('purchase_orders')->orderByDesc('id')->value('id');
    $path = (string) DB::table('purchase_orders')->where('id', $orderId)->value('document_file_path');
    $orig = (string) DB::table('purchase_orders')->where('id', $orderId)->value('document_original_name');
    expect($path)->not->toBe('');
    expect($orig)->toContain('remito');

    Storage::disk('public')->assertExists($path);

    $list = $this->getJson('/api/maintenance/purchases')->assertOk()->json('data');
    $row = collect($list)->firstWhere('id', $orderId);
    expect($row['has_document'] ?? false)->toBeTrue();

    $this->actingAs($admin)->get("/api/maintenance/purchases/{$orderId}/document")->assertOk();
});

it('uploads document to an existing purchase without one', function (): void {
    Storage::fake('public');

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-UP',
        'name' => 'Compras upload',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'UP-01',
        'name' => 'Upload later',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();
    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'lines' => [
            ['product_id' => $productId, 'quantity' => 1, 'unit_cost' => 10],
        ],
    ])->assertCreated();

    $orderId = (int) DB::table('purchase_orders')->orderByDesc('id')->value('id');
    expect(DB::table('purchase_orders')->where('id', $orderId)->value('document_file_path'))->toBeNull();

    $pdf = UploadedFile::fake()->create('factura-a.pdf', 40, 'application/pdf');

    $this->post("/api/maintenance/purchases/{$orderId}/document", [
        'document' => $pdf,
    ], ['Accept' => 'application/json'])->assertOk()
        ->assertJsonPath('data.has_document', true);

    expect(DB::table('purchase_orders')->where('id', $orderId)->value('document_file_path'))->not->toBeNull();
});

it('downloads system-generated pdf for a purchase', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PO-PDF',
        'name' => 'Compras PDF',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/products', [
        'sku' => 'PDF-01',
        'name' => 'PDF test',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 0,
        'track_stock' => true,
    ])->assertCreated();
    $productId = $create->json('data.id');

    $this->postJson('/api/maintenance/purchases', [
        'document_ref' => 'F-PDF-1',
        'lines' => [
            ['product_id' => $productId, 'quantity' => 2, 'unit_cost' => 30],
        ],
    ])->assertCreated();

    $orderId = (int) DB::table('purchase_orders')->orderByDesc('id')->value('id');

    $res = $this->actingAs($admin)->get("/api/maintenance/purchases/{$orderId}/pdf");
    $res->assertOk();
    expect($res->headers->get('Content-Type'))->toContain('application/pdf');
    expect($res->headers->get('Content-Disposition'))->toContain('compra-'.$orderId);
});
