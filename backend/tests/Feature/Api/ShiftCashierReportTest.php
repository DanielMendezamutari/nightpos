<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns shift cashier report with waiters, products, manillas, piezas and history', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'REPORT-1',
        'name' => 'Report site',
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

    DB::table('system_settings')->updateOrInsert(
        ['key' => 'waiter_commission_rate_pct'],
        [
            'is_locked' => false,
            'reason' => '10',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    DB::table('system_settings')->updateOrInsert(
        ['key' => 'companion_manilla_commission_pct'],
        [
            'is_locked' => false,
            'reason' => '40',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    DB::table('system_settings')->updateOrInsert(
        ['key' => 'companion_pieza_commission_pct'],
        [
            'is_locked' => false,
            'reason' => '50',
            'created_at' => now(),
            'updated_at' => now(),
        ]
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
        'stage_name' => 'Luna',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'T-1',
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

    $productSoloId = DB::table('products')->insertGetId([
        'sku' => 'RPT-SOLO',
        'name' => 'Cerveza',
        'category_id' => productCategoryId('beer'),
        'product_type' => 'drink',
        'price_solo' => 50,
        'price_with_companion' => 100,
        'base_stock' => 100,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productWithId = DB::table('products')->insertGetId([
        'sku' => 'RPT-WITH',
        'name' => 'Fernet',
        'category_id' => productCategoryId('spirits'),
        'product_type' => 'drink',
        'price_solo' => 60,
        'price_with_companion' => 120,
        'base_stock' => 50,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('order_items')->insert([
        [
            'order_id' => $orderId,
            'product_id' => $productSoloId,
            'waiter_user_id' => $waiter->id,
            'companion_id' => null,
            'consumption_type' => 'solo',
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'order_id' => $orderId,
            'product_id' => $productWithId,
            'waiter_user_id' => $waiter->id,
            'companion_id' => $companionId,
            'consumption_type' => 'with_companion',
            'quantity' => 1,
            'unit_price' => 120,
            'subtotal' => 120,
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($cashier);
    $pay = $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftTurnId,
        'method' => 'cash',
        'amount' => 220,
    ]);
    $pay->assertCreated();

    $serviceId = DB::table('room_time_services')->insertGetId([
        'site_id' => $siteId,
        'shift_turn_id' => $shiftTurnId,
        'cashier_user_id' => $cashier->id,
        'waiter_user_id' => $waiter->id,
        'companion_id' => $companionId,
        'customer_name' => 'Cliente',
        'room_label' => 'VIP-2',
        'rate_per_hour' => 10000,
        'grace_minutes' => 5,
        'started_at' => now(),
        'closed_at' => now(),
        'manual_minutes' => 60,
        'billed_minutes' => 60,
        'subtotal' => 8000,
        'status' => 'paid',
        'notes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('room_time_service_payments')->insert([
        'room_time_service_id' => $serviceId,
        'shift_turn_id' => $shiftTurnId,
        'cashier_user_id' => $cashier->id,
        'method' => 'cash',
        'amount' => 3000,
        'paid_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('cash_drawer_movements')->insert([
        'shift_turn_id' => $shiftTurnId,
        'user_id' => $cashier->id,
        'direction' => 'in',
        'amount' => 500,
        'notes' => 'Cambio',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $res = $this->getJson("/api/shifts/{$shiftTurnId}/cashier-report");
    $res->assertOk()
        ->assertJsonPath('data.shift_turn_id', $shiftTurnId)
        ->assertJsonPath('data.cash_totals.cash_from_sales', 220)
        ->assertJsonPath('data.cash_totals.drawer_in', 500)
        ->assertJsonPath('data.payout_settings.waiter_commission_rate_pct', 10)
        ->assertJsonPath('data.payout_settings.companion_manilla_commission_pct', 40)
        ->assertJsonPath('data.payout_settings.companion_pieza_commission_pct', 50);

    $waiters = $res->json('data.waiters');
    expect($waiters)->toBeArray()->not->toBeEmpty();
    $w0 = collect($waiters)->firstWhere('waiter_user_id', $waiter->id);
    expect($w0['items_subtotal'])->toBe(220);
    expect($w0['commission_owed'])->toBeGreaterThan(0);

    $products = $res->json('data.products_sold');
    expect(collect($products)->sum('subtotal'))->toBe(220);

    $manillas = $res->json('data.companions_manillas');
    expect($manillas)->toHaveCount(1);
    expect($manillas[0]['manilla_subtotal'])->toBe(120);
    expect($manillas[0]['manilla_units'])->toBe(1);

    $overview = $res->json('data.companions_overview');
    $ov = collect($overview)->firstWhere('companion_id', $companionId);
    expect($ov['manilla_subtotal'])->toBe(120);
    expect($ov['pieza_subtotal'])->toBe(8000);
    expect($ov['suggested_payout_manillas'])->toBe(48);
    expect($ov['suggested_payout_piezas'])->toBe(4000);

    $piezas = $res->json('data.pieza_services');
    expect($piezas)->toHaveCount(1);
    expect($piezas[0]['balance_due'])->toBe(5000);

    $hist = $this->getJson('/api/shifts/history?limit=5');
    $hist->assertOk();
    expect($hist->json('data.0.id'))->toBe($shiftTurnId);

    $erp = $res->json('data.erp_summary');
    expect($erp['product_sales_subtotal'])->toBe(220)
        ->and($erp['payments_collected']['cash'])->toBe(220)
        ->and($erp['payments_collected']['total'])->toBe(220)
        ->and($erp['companion_payouts_total'])->toBe(0)
        ->and($erp['waiter_commissions_total'])->toBeGreaterThan(0);
});

it('forbids cashier report when shift belongs to another site', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'SA',
        'name' => 'A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'SB',
        'name' => 'B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);

    $otherCashier = User::factory()->create(['role' => 'cashier', 'site_id' => $siteB, 'active_site_id' => $siteB]);

    $shiftB = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteB,
        'cashier_user_id' => $otherCashier->id,
        'period' => 'night',
        'opening_cash' => 100,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);
    $this->getJson("/api/shifts/{$shiftB}/cashier-report")
        ->assertForbidden();
});
