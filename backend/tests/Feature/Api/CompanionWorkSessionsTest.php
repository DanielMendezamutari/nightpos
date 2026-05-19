<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('starts session, lists totals and settles payout', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CWS-1',
        'name' => 'CWS',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId, 'active_site_id' => $siteId]);

    DB::table('system_settings')->updateOrInsert(
        ['key' => 'companion_manilla_commission_pct'],
        ['is_locked' => false, 'reason' => '50', 'created_at' => now(), 'updated_at' => now()],
    );

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 1000,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $companionId = DB::table('companions')->insertGetId([
        'stage_name' => 'TestLuna',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);

    $start = $this->postJson("/api/shifts/{$shiftTurnId}/companion-work-sessions", [
        'companion_id' => $companionId,
    ]);
    $start->assertCreated();
    $sessionId = (int) $start->json('data.id');
    expect($sessionId)->toBeGreaterThan(0);

    DB::table('companion_work_sessions')->where('id', $sessionId)->update([
        'started_at' => now()->subHour(),
        'updated_at' => now(),
    ]);

    $sessionId2 = $this->postJson("/api/shifts/{$shiftTurnId}/companion-work-sessions", [
        'companion_id' => $companionId,
    ]);
    $sessionId2->assertStatus(422);

    $session = DB::table('companion_work_sessions')->where('id', $sessionId)->first();
    expect($session->status)->toBe('active');

    $cs = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'M-1',
        'zone_code' => 'A',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'shift_turn_id' => $shiftTurnId,
        'customer_session_id' => $cs,
        'waiter_user_id' => $waiter->id,
        'status' => 'pending',
        'ordered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'CWS-BEER',
        'name' => 'Cerveza',
        'category_id' => productCategoryId('beer'),
        'product_type' => 'drink',
        'price_solo' => 10,
        'price_with_companion' => 100,
        'base_stock' => 50,
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
        'quantity' => 1,
        'unit_price' => 100,
        'subtotal' => 100,
        'registered_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $list = $this->getJson("/api/shifts/{$shiftTurnId}/companion-work-sessions");
    $list->assertOk();
    $row = collect($list->json('data'))->first(fn (array $x): bool => (int) $x['id'] === $sessionId);
    expect((int) $row['manilla_subtotal'])->toBe(100);
    expect((int) $row['suggested_payout_manillas'])->toBe(50);

    $settle = $this->postJson("/api/companion-work-sessions/{$sessionId}/settle", [
        'amount' => 50,
        'notes' => 'Efectivo',
    ]);
    $settle->assertOk();
    $this->assertDatabaseHas('companion_work_sessions', [
        'id' => $sessionId,
        'status' => 'settled',
    ]);
    $this->assertDatabaseHas('companion_work_session_payouts', [
        'companion_work_session_id' => $sessionId,
        'amount' => 50,
    ]);
    $this->assertDatabaseHas('cash_drawer_movements', [
        'shift_turn_id' => $shiftTurnId,
        'direction' => 'out',
        'amount' => 50,
    ]);

    $again = $this->postJson("/api/shifts/{$shiftTurnId}/companion-work-sessions", [
        'companion_id' => $companionId,
    ]);
    $again->assertCreated();
    expect((int) $again->json('data.id'))->not->toBe($sessionId);
});
