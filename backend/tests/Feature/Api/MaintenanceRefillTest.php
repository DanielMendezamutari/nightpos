<?php

use App\Models\User;
use App\Support\ProductStockAggregator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('applies refill recipe and moves stock between products', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'MT-01',
        'name' => 'Mantenimiento 01',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sourceId = DB::table('products')->insertGetId([
        'sku' => 'MOEMA-LATA',
        'name' => 'Moema Lata',
        'product_type' => 'drink',
        'price_solo' => 0,
        'price_with_companion' => 0,
        'base_stock' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $targetId = DB::table('products')->insertGetId([
        'sku' => 'CORONA-BOT',
        'name' => 'Corona Botella',
        'product_type' => 'drink',
        'price_solo' => 0,
        'price_with_companion' => 0,
        'base_stock' => 2,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_product_stocks')->insert([
        [
            'site_id' => $siteId,
            'product_id' => $sourceId,
            'quantity' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteId,
            'product_id' => $targetId,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    ProductStockAggregator::syncBaseStock($sourceId);
    ProductStockAggregator::syncBaseStock($targetId);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $create = $this->postJson('/api/maintenance/refill-recipes', [
        'source_product_id' => $sourceId,
        'target_product_id' => $targetId,
        'source_units' => 1,
        'target_units' => 2,
        'notes' => 'Prueba relleno',
    ])->assertCreated();

    $recipeId = $create->json('data.id');
    expect($recipeId)->toBeInt();

    $this->postJson("/api/maintenance/refill-recipes/{$recipeId}/apply", [
        'batches' => 3,
    ])->assertCreated()
        ->assertJsonPath('data.source_out', 3)
        ->assertJsonPath('data.target_in', 6);

    $this->assertDatabaseHas('products', ['id' => $sourceId, 'base_stock' => 7]);
    $this->assertDatabaseHas('products', ['id' => $targetId, 'base_stock' => 8]);
    $this->assertDatabaseCount('inventory_movements', 2);
});
