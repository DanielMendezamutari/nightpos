<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('skips waiter commission rows for payroll monthly waiter', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'WP-1',
        'name' => 'WP Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $waiterPayroll = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
        'waiter_compensation_type' => 'payroll_monthly',
    ]);

    DB::table('system_settings')->updateOrInsert(
        ['key' => 'waiter_commission_rate_pct'],
        ['is_locked' => false, 'reason' => '10', 'created_at' => now(), 'updated_at' => now()],
    );

    $shiftId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 100,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'T1',
        'zone_code' => 'A',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftId,
        'customer_session_id' => $sessionId,
        'waiter_user_id' => $waiterPayroll->id,
        'status' => 'pending',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'WP-P',
        'name' => 'Drink',
        'category_id' => productCategoryId('beer'),
        'product_type' => 'drink',
        'price_solo' => 50,
        'price_with_companion' => 80,
        'base_stock' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_user_id' => $waiterPayroll->id,
        'companion_id' => null,
        'consumption_type' => 'solo',
        'quantity' => 1,
        'unit_price' => 50,
        'subtotal' => 50,
        'registered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);
    $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 50,
    ])->assertCreated();

    expect(DB::table('waiter_commissions')->where('waiter_user_id', $waiterPayroll->id)->count())->toBe(0);
});

it('still creates commission for per_payment waiter', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'WP-2',
        'name' => 'WP2',
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
        'waiter_compensation_type' => 'per_payment',
    ]);

    DB::table('system_settings')->updateOrInsert(
        ['key' => 'waiter_commission_rate_pct'],
        ['is_locked' => false, 'reason' => '10', 'created_at' => now(), 'updated_at' => now()],
    );

    $shiftId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 100,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'T2',
        'zone_code' => 'A',
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
        'sku' => 'WP-P2',
        'name' => 'Drink2',
        'category_id' => productCategoryId('beer'),
        'product_type' => 'drink',
        'price_solo' => 100,
        'price_with_companion' => 150,
        'base_stock' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderItemId = DB::table('order_items')->insertGetId([
        'order_id' => $orderId,
        'product_id' => $productId,
        'waiter_user_id' => $waiter->id,
        'companion_id' => null,
        'consumption_type' => 'solo',
        'quantity' => 1,
        'unit_price' => 100,
        'subtotal' => 100,
        'registered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);
    $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 100,
    ])->assertCreated();

    $this->assertDatabaseHas('waiter_commissions', [
        'order_item_id' => $orderItemId,
        'waiter_user_id' => $waiter->id,
        'commission_amount' => 10,
    ]);
});
