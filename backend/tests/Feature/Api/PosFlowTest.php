<?php

use App\Models\User;
use App\Support\ProductStockAggregator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates products with both prices from admin input', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'TEST-POS',
        'name' => 'Sucursal test POS',
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

    $response = $this->postJson('/api/products', [
        'sku' => 'GIN-001',
        'name' => 'Gin',
        'category_id' => productCategoryId('spirits'),
        'price_solo' => 45,
        'price_with_companion' => 90,
        'base_stock' => 25,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.sku', 'GIN-001')
        ->assertJsonPath('data.category_id', productCategoryId('spirits'))
        ->assertJsonPath('data.product_type', 'drink')
        ->assertJsonPath('data.price_solo', 45)
        ->assertJsonPath('data.price_with_companion', 90);

    $this->assertDatabaseHas('products', [
        'sku' => 'GIN-001',
        'name' => 'Gin',
        'category_id' => productCategoryId('spirits'),
        'price_solo' => 45,
        'price_with_companion' => 90,
        'base_stock' => 25,
    ]);
});

it('opens a shift from api', function (): void {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CASA22',
        'name' => 'Casa 22',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson('/api/shifts/open', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteId,
        'period' => 'night',
        'opening_cash' => 500,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'open');

    $this->assertDatabaseHas('shift_turns', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteId,
        'period' => 'night',
        'opening_cash' => 500,
        'status' => 'open',
    ]);
});

it('adds order item with automatic price based on consumption type', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'VIP',
        'name' => 'VIP',
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
        'table_code' => 'MESA-1',
        'zone_code' => 'A',
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
        'sku' => 'BEER-001',
        'name' => 'Cerveza',
        'category_id' => productCategoryId('beer'),
        'product_type' => 'drink',
        'price_solo' => 42,
        'price_with_companion' => 85,
        'base_stock' => 100,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson('/api/orders/items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_id' => $waiter->id,
        'companion_id' => null,
        'quantity' => 2,
        'consumption_type' => 'solo',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'consumption_type' => 'solo',
        'quantity' => 2,
        'unit_price' => 42,
        'subtotal' => 84,
    ]);
});

it('requires companion when consumption type is with_companion', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'VIP2',
        'name' => 'VIP 2',
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
        'table_code' => 'MESA-3',
        'zone_code' => 'A',
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
        'sku' => 'WHISKY-001',
        'name' => 'Whisky',
        'category_id' => productCategoryId('spirits'),
        'product_type' => 'drink',
        'price_solo' => 60,
        'price_with_companion' => 120,
        'base_stock' => 50,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson('/api/orders/items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_id' => $waiter->id,
        'companion_id' => null,
        'quantity' => 1,
        'consumption_type' => 'with_companion',
    ]);

    $response->assertStatus(422);
});

it('uses product companion price when consumption type is with_companion', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'VIP3',
        'name' => 'VIP 3',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $companionId = DB::table('companions')->insertGetId([
        'stage_name' => 'Maria',
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
        'table_code' => 'MESA-4',
        'zone_code' => 'A',
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
        'sku' => 'VODKA-001',
        'name' => 'Vodka',
        'category_id' => productCategoryId('spirits'),
        'product_type' => 'drink',
        'price_solo' => 50,
        'price_with_companion' => 90,
        'base_stock' => 30,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson('/api/orders/items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_id' => $waiter->id,
        'companion_id' => $companionId,
        'quantity' => 2,
        'consumption_type' => 'with_companion',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'consumption_type' => 'with_companion',
        'quantity' => 2,
        'unit_price' => 90,
        'subtotal' => 180,
    ]);
});

it('discounts product stock and logs inventory movement when adding order item', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CASA-STOCK',
        'name' => 'Casa Stock',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $waiter->id,
        'period' => 'night',
        'opening_cash' => 700,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'MESA-5',
        'zone_code' => 'C',
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
        'sku' => 'RUM-001',
        'name' => 'Ron',
        'category_id' => productCategoryId('spirits'),
        'product_type' => 'drink',
        'price_solo' => 35,
        'price_with_companion' => 70,
        'base_stock' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('site_product_stocks')->insert([
        'site_id' => $siteId,
        'product_id' => $productId,
        'quantity' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    ProductStockAggregator::syncBaseStock($productId);

    $this->postJson('/api/orders/items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_id' => $waiter->id,
        'companion_id' => null,
        'quantity' => 3,
        'consumption_type' => 'solo',
    ])->assertCreated();

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'base_stock' => 7,
    ]);

    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $productId,
        'site_id' => $siteId,
        'movement_type' => 'sale_out',
        'quantity' => 3,
        'reference_type' => 'order_item',
    ]);
});

it('registers payments immediately from api', function (): void {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);
    $waiter = User::factory()->create(['role' => 'waiter']);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CORONA',
        'name' => 'Corona',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 600,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'MESA-2',
        'zone_code' => 'B',
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

    $response = $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftTurnId,
        'method' => 'cash',
        'amount' => 160,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.method', 'cash')
        ->assertJsonPath('data.amount', 160);

    $this->assertDatabaseHas('payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftTurnId,
        'method' => 'cash',
        'amount' => 160,
    ]);
});

it('closes shift with totals by payment method and difference', function (): void {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);
    $waiter = User::factory()->create(['role' => 'waiter']);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CLOSE-01',
        'name' => 'Close Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 500,
        'opened_at' => now()->subHours(5),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'MESA-6',
        'zone_code' => 'D',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftTurnId,
        'customer_session_id' => $sessionId,
        'waiter_user_id' => $waiter->id,
        'status' => 'paid',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('payments')->insert([
        [
            'order_id' => $orderId,
            'shift_turn_id' => $shiftTurnId,
            'method' => 'cash',
            'amount' => 200,
            'paid_at' => now()->subHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'order_id' => $orderId,
            'shift_turn_id' => $shiftTurnId,
            'method' => 'qr',
            'amount' => 150,
            'paid_at' => now()->subHours(1),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'order_id' => $orderId,
            'shift_turn_id' => $shiftTurnId,
            'method' => 'card',
            'amount' => 50,
            'paid_at' => now()->subMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->postJson("/api/shifts/{$shiftTurnId}/close", [
        'closing_cash' => 680,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.totals.cash', 200)
        ->assertJsonPath('data.totals.qr', 150)
        ->assertJsonPath('data.totals.card', 50)
        ->assertJsonPath('data.expected_cash', 700)
        ->assertJsonPath('data.closing_cash', 680)
        ->assertJsonPath('data.difference', -20);

    $this->assertDatabaseHas('shift_turns', [
        'id' => $shiftTurnId,
        'status' => 'closed',
        'closing_cash' => 680,
    ]);
});

it('blocks waiter from creating products', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $response = $this->postJson('/api/products', [
        'sku' => 'GIN-999',
        'name' => 'Gin Pro',
        'category_id' => productCategoryId('spirits'),
        'price_solo' => 60,
        'price_with_companion' => 120,
        'base_stock' => 10,
    ]);

    $response->assertForbidden();
});

it('allows waiter to list only active products', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    DB::table('products')->insert([
        [
            'sku' => 'ACTIVE-001',
            'name' => 'Activo',
            'category_id' => productCategoryId('beer'),
            'product_type' => 'drink',
            'price_solo' => 30,
            'price_with_companion' => 60,
            'base_stock' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'sku' => 'INACTIVE-001',
            'name' => 'Inactivo',
            'category_id' => productCategoryId('beer'),
            'product_type' => 'drink',
            'price_solo' => 25,
            'price_with_companion' => 50,
            'base_stock' => 10,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson('/api/products');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.sku', 'ACTIVE-001');
});

it('allows admin to update product prices and activation', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'UPD-001',
        'name' => 'Editable',
        'category_id' => productCategoryId('soft_drinks'),
        'product_type' => 'drink',
        'price_solo' => 20,
        'price_with_companion' => 40,
        'base_stock' => 15,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->patchJson("/api/products/{$productId}", [
        'category_id' => productCategoryId('spirits'),
        'price_solo' => 33,
        'price_with_companion' => 77,
        'is_active' => false,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $productId)
        ->assertJsonPath('data.category_id', productCategoryId('spirits'))
        ->assertJsonPath('data.category_slug', 'spirits')
        ->assertJsonPath('data.price_solo', 33)
        ->assertJsonPath('data.price_with_companion', 77)
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'category_id' => productCategoryId('spirits'),
        'product_type' => 'drink',
        'price_solo' => 33,
        'price_with_companion' => 77,
        'is_active' => false,
    ]);
});

it('blocks waiter from updating products', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'UPD-002',
        'name' => 'NoPermiso',
        'category_id' => productCategoryId('food'),
        'product_type' => 'drink',
        'price_solo' => 20,
        'price_with_companion' => 40,
        'base_stock' => 15,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->patchJson("/api/products/{$productId}", [
        'price_solo' => 99,
    ]);

    $response->assertForbidden();
});

it('allows manager to view companion ranking report', function (): void {
    $manager = User::factory()->create(['role' => 'manager']);
    $cashier = User::factory()->create(['role' => 'cashier']);
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($manager);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'RPT-01',
        'name' => 'Report Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $companionId = DB::table('companions')->insertGetId([
        'stage_name' => 'Lucia',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 500,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'MESA-R1',
        'zone_code' => 'A',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftTurnId,
        'customer_session_id' => $sessionId,
        'waiter_user_id' => $waiter->id,
        'status' => 'paid',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'RPT-DRINK-1',
        'name' => 'Reporte Drink',
        'category_id' => productCategoryId('cocktails'),
        'product_type' => 'drink',
        'price_solo' => 40,
        'price_with_companion' => 80,
        'base_stock' => 100,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_user_id' => $waiter->id,
        'companion_id' => $companionId,
        'consumption_type' => 'with_companion',
        'quantity' => 3,
        'unit_price' => 80,
        'subtotal' => 240,
        'registered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/reports/companions/ranking');

    $response->assertOk()
        ->assertJsonPath('data.0.stage_name', 'Lucia')
        ->assertJsonPath('data.0.drinks_count', 3)
        ->assertJsonPath('data.0.total_generated', 240);
});

it('allows owner to lock and unlock the whole system', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $this->patchJson('/api/system/lock', [
        'is_locked' => true,
        'reason' => 'Mensualidad vencida',
    ])->assertOk()
        ->assertJsonPath('data.is_locked', true);

    $this->assertDatabaseHas('system_settings', [
        'key' => 'global_lock',
        'is_locked' => true,
        'reason' => 'Mensualidad vencida',
    ]);

    $this->patchJson('/api/system/lock', [
        'is_locked' => false,
        'reason' => 'Pago confirmado',
    ])->assertOk()
        ->assertJsonPath('data.is_locked', false);
});

it('blocks non owner users when system is locked', function (): void {
    DB::table('system_settings')->insert([
        'key' => 'global_lock',
        'is_locked' => true,
        'reason' => 'Mensualidad vencida',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'LOCK-01',
        'name' => 'Locked Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/shifts/open', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteId,
        'period' => 'night',
        'opening_cash' => 100,
    ])->assertStatus(423);
});

it('allows super admin to open shifts in any branch', function (): void {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($superAdmin);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'SUPER-01',
        'name' => 'Sucursal Global',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/shifts/open', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteId,
        'period' => 'day',
        'opening_cash' => 300,
    ])->assertCreated();
});

it('blocks admin when trying to operate another assigned branch', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'ADM-A',
        'name' => 'Sucursal A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $siteB = DB::table('sites')->insertGetId([
        'code' => 'ADM-B',
        'name' => 'Sucursal B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
    ]);

    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($admin);

    $this->postJson('/api/shifts/open', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteB,
        'period' => 'night',
        'opening_cash' => 250,
    ])->assertForbidden();
});

it('allows owner to create branch and admin assigned to that branch', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $response = $this->postJson('/api/branches', [
        'code' => 'NEW-01',
        'name' => 'Sucursal Nueva',
        'is_active' => true,
        'monthly_fee' => 850,
        'billing_contact_name' => 'Maria Cobros',
        'billing_contact_phone' => '77700011',
        'billing_contact_email' => 'cobros.sucursal@example.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.code', 'NEW-01')
        ->assertJsonPath('data.monthly_fee', 850)
        ->assertJsonPath('data.billing_contact_name', 'Maria Cobros');

    $this->assertDatabaseHas('saas_subscriptions', [
        'site_id' => DB::table('sites')->where('code', 'NEW-01')->value('id'),
        'monthly_fee' => 850,
        'billing_contact_name' => 'Maria Cobros',
    ]);

    $siteId = DB::table('sites')->where('code', 'NEW-01')->value('id');

    $userResponse = $this->postJson('/api/users', [
        'name' => 'Admin Sucursal',
        'email' => 'admin.sucursal@example.com',
        'password' => 'password',
        'role' => 'admin',
        'site_id' => $siteId,
    ]);

    $userResponse->assertCreated()
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonPath('data.site_id', $siteId);
});

it('allows admin to create only branch staff in its own branch', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'STAFF-01',
        'name' => 'Staff Branch',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $response = $this->postJson('/api/users', [
        'name' => 'Cajera Uno',
        'email' => 'cajera.uno@example.com',
        'password' => 'password',
        'role' => 'cashier',
        'site_id' => $siteId,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.role', 'cashier')
        ->assertJsonPath('data.site_id', $siteId);
});

it('blocks admin from creating users in another branch', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'STAFF-A',
        'name' => 'Branch A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $siteB = DB::table('sites')->insertGetId([
        'code' => 'STAFF-B',
        'name' => 'Branch B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
    ]);
    $this->actingAs($admin);

    $this->postJson('/api/users', [
        'name' => 'Garzon Fuera',
        'email' => 'garzon.fuera@example.com',
        'password' => 'password',
        'role' => 'waiter',
        'site_id' => $siteB,
    ])->assertForbidden();
});

it('blocks admin from creating super admin or owner users', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'STAFF-02',
        'name' => 'Staff Branch 2',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $this->postJson('/api/users', [
        'name' => 'No Permitido',
        'email' => 'no.permitido@example.com',
        'password' => 'password',
        'role' => 'super_admin',
        'site_id' => $siteId,
    ])->assertForbidden();
});
