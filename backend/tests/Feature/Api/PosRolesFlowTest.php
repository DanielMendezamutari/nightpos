<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('marks cashier login as requires_open_shift when no open shift exists', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'POS-GATE-01',
        'name' => 'Gate Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
        'pin_code' => '1234',
    ]);

    $this->postJson('/api/auth/login', ['pin' => $cashier->pin_code])
        ->assertOk()
        ->assertJsonPath('data.requires_open_shift', true);
});

it('allows waiter to create session, order and add item', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'POS-WAIT-01',
        'name' => 'Wait Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    DB::table('shift_turns')->insert([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 100,
        'opened_at' => now()->subHour(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $this->actingAs($waiter);

    $productId = $this->postJson('/api/products', [
        'sku' => 'POS-TEST-01',
        'name' => 'Pos test drink',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 10,
        'track_stock' => true,
    ])->assertForbidden();

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($admin);

    $createProduct = $this->postJson('/api/products', [
        'sku' => 'POS-TEST-01',
        'name' => 'Pos test drink',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 10,
        'track_stock' => true,
    ])->assertCreated();

    $pid = $createProduct->json('data.id');

    $this->actingAs($waiter);

    $session = $this->postJson('/api/pos/sessions', [
        'table_code' => 'M1',
        'zone_code' => 'General',
    ])->assertCreated();

    $sessionId = $session->json('data.id');

    $order = $this->postJson('/api/pos/orders', [
        'customer_session_id' => $sessionId,
    ])->assertCreated();

    $orderId = $order->json('data.id');

    $this->postJson("/api/pos/orders/{$orderId}/items", [
        'product_id' => $pid,
        'quantity' => 2,
        'consumption_type' => 'solo',
    ])->assertCreated();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'product_id' => $pid,
        'quantity' => 2,
    ]);
});

it('creates waiter commission when cashier confirms payment', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'POS-COM-01',
        'name' => 'Com Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $shiftId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 300,
        'opened_at' => now()->subHour(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'M2',
        'zone_code' => 'VIP',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftId,
        'customer_session_id' => $sessionId,
        'waiter_user_id' => $waiter->id,
        'status' => 'pending',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'POS-COM-P',
        'name' => 'Com drink',
        'product_type' => 'drink',
        'price_solo' => 100,
        'price_with_companion' => 180,
        'base_stock' => 0,
        'is_active' => true,
        'purchase_price' => 0,
        'stock_min' => 0,
        'stock_max' => null,
        'track_stock' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderItemId = DB::table('order_items')->insertGetId([
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_user_id' => $waiter->id,
        'companion_id' => null,
        'consumption_type' => 'solo',
        'quantity' => 2,
        'unit_price' => 100,
        'subtotal' => 200,
        'registered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);

    $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 200,
    ])->assertCreated();

    $this->assertDatabaseHas('waiter_commissions', [
        'order_item_id' => $orderItemId,
        'waiter_user_id' => $waiter->id,
        'base_amount' => 200,
        'commission_amount' => 20,
    ]);
});

it('handles room time service lifecycle and payment', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-01',
        'name' => 'Room Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $shiftId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 300,
        'opened_at' => now()->subHour(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);

    $companionId = DB::table('companions')->insertGetId([
        'stage_name' => 'Luna',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $open = $this->postJson('/api/room-services', [
        'room_label' => 'Pieza 3',
        'companion_id' => $companionId,
        'rate_per_hour' => 1200,
        'payment_method' => 'cash',
        'payment_amount' => 100,
    ])->assertCreated();

    $serviceId = $open->json('data.id');

    $this->postJson("/api/room-services/{$serviceId}/extend", [
        'added_minutes' => 30,
    ])->assertOk();

    $this->postJson("/api/room-services/{$serviceId}/close", [])->assertOk();

    $this->assertDatabaseHas('room_time_services', [
        'id' => $serviceId,
        'status' => 'closed',
    ]);

    $this->postJson("/api/room-services/{$serviceId}/pay", [
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 200,
    ])->assertCreated();

    $this->assertDatabaseHas('room_time_service_payments', [
        'room_time_service_id' => $serviceId,
        'amount' => 200,
    ]);
});
