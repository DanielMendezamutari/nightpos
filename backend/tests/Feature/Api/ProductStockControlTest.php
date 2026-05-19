<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not decrement stock or write inventory movement when track_stock is false', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'TS-01',
        'name' => 'Track Stock Test',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $waiter->id,
        'period' => 'night',
        'opening_cash' => 1000,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'T1',
        'zone_code' => null,
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftTurnId,
        'customer_session_id' => $sessionId,
        'waiter_user_id' => $waiter->id,
        'status' => 'pending',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'NO-TRACK-1',
        'name' => 'Servicio sin stock',
        'category_id' => productCategoryId('soft_drinks'),
        'product_type' => 'drink',
        'price_solo' => 10,
        'price_with_companion' => 20,
        'base_stock' => 50,
        'purchase_price' => 5,
        'stock_min' => 0,
        'stock_max' => null,
        'track_stock' => false,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/orders/items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_id' => $waiter->id,
        'companion_id' => null,
        'quantity' => 3,
        'consumption_type' => 'solo',
    ])->assertCreated();

    $this->assertDatabaseHas('products', ['id' => $productId, 'base_stock' => 50]);
    $this->assertDatabaseCount('inventory_movements', 0);
});

it('creates product with purchase price and stock limits', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'TEST-STK',
        'name' => 'Sucursal stock test',
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

    $this->postJson('/api/products', [
        'sku' => 'FULL-CTRL-1',
        'name' => 'Cerveza controlada',
        'category_id' => productCategoryId('beer'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 24,
        'purchase_price' => 55,
        'stock_min' => 12,
        'stock_max' => 120,
        'track_stock' => true,
    ])->assertCreated();

    $this->assertDatabaseHas('products', [
        'sku' => 'FULL-CTRL-1',
        'purchase_price' => 55,
        'stock_min' => 12,
        'stock_max' => 120,
        'track_stock' => true,
    ]);
});
