<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function seedSiteWithWaiter(): array
{
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'BW-1',
        'name' => 'BW Site',
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
        'waiter_commission_rate_pct' => null,
    ]);

    return [$siteId, $cashier, $waiter];
}

it('allows cashier to list branch waiters with default commission pct', function (): void {
    [$siteId, $cashier, $waiter] = seedSiteWithWaiter();

    DB::table('system_settings')->updateOrInsert(
        ['key' => 'waiter_commission_rate_pct'],
        ['is_locked' => false, 'reason' => '11', 'created_at' => now(), 'updated_at' => now()],
    );

    $this->actingAs($cashier);
    $res = $this->getJson('/api/branch/waiters');
    $res->assertOk()
        ->assertJsonPath('data.default_commission_rate_pct', 11);

    $list = collect($res->json('data.waiters'));
    expect($list->pluck('id')->contains($waiter->id))->toBeTrue();
});

it('allows cashier to patch waiter compensation for same site', function (): void {
    [$siteId, $cashier, $waiter] = seedSiteWithWaiter();

    $this->actingAs($cashier);
    $this->patchJson("/api/branch/waiters/{$waiter->id}/compensation", [
        'waiter_compensation_type' => 'per_payment',
        'waiter_commission_rate_pct' => 12.5,
    ])->assertOk()
        ->assertJsonPath('data.waiter_commission_rate_pct', 12.5);

    $this->assertDatabaseHas('users', [
        'id' => $waiter->id,
        'waiter_commission_rate_pct' => 12.5,
    ]);
});

it('forbids patching waiter from another site', function (): void {
    [$siteId, $cashier] = seedSiteWithWaiter();

    $otherSite = DB::table('sites')->insertGetId([
        'code' => 'BW-2',
        'name' => 'Other',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $foreignWaiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $otherSite,
        'active_site_id' => $otherSite,
    ]);

    $this->actingAs($cashier);
    $this->patchJson("/api/branch/waiters/{$foreignWaiter->id}/compensation", [
        'waiter_compensation_type' => 'per_payment',
        'waiter_commission_rate_pct' => 5,
    ])->assertNotFound();
});

it('recalculates past waiter_commissions when commission pct changes', function (): void {
    [$siteId, $cashier, $waiter] = seedSiteWithWaiter();

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
        'waiter_user_id' => $waiter->id,
        'status' => 'pending',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'BW-REC',
        'name' => 'Drink',
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
    $payRes = $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 100,
    ]);
    $payRes->assertCreated();
    $paymentId = $payRes->json('data.payment_id');

    $this->assertDatabaseHas('waiter_commissions', [
        'payment_id' => $paymentId,
        'order_item_id' => $orderItemId,
        'waiter_user_id' => $waiter->id,
        'base_amount' => 100,
        'rate_pct' => 10,
        'commission_amount' => 10,
    ]);

    $this->patchJson("/api/branch/waiters/{$waiter->id}/compensation", [
        'waiter_compensation_type' => 'per_payment',
        'waiter_commission_rate_pct' => 18,
    ])->assertOk();

    $this->assertDatabaseHas('waiter_commissions', [
        'payment_id' => $paymentId,
        'order_item_id' => $orderItemId,
        'waiter_user_id' => $waiter->id,
        'base_amount' => 100,
        'rate_pct' => 18,
        'commission_amount' => 18,
    ]);

    $this->patchJson("/api/branch/waiters/{$waiter->id}/compensation", [
        'waiter_compensation_type' => 'payroll_monthly',
    ])->assertOk();

    expect(DB::table('waiter_commissions')->where('waiter_user_id', $waiter->id)->count())->toBe(0);
});
