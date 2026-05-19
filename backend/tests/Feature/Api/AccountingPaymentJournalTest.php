<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('records balanced journal lines when a pos payment is registered', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ACC-01',
        'name' => 'Accounting site',
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
        'table_code' => 'M9',
        'zone_code' => 'Z',
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

    $this->actingAs($cashier);

    $this->postJson('/api/payments', [
        'order_id' => $orderId,
        'shift_turn_id' => $shiftId,
        'method' => 'cash',
        'amount' => 450,
    ])->assertCreated();

    $paymentId = (int) DB::table('payments')->where('order_id', $orderId)->value('id');
    expect($paymentId)->toBeGreaterThan(0);

    $entry = DB::table('journal_entries')
        ->where('reference_type', 'payment')
        ->where('reference_id', $paymentId)
        ->first();
    expect($entry)->not->toBeNull();
    expect((int) $entry->site_id)->toBe($siteId);

    $lines = DB::table('journal_lines')->where('journal_entry_id', $entry->id)->get();
    expect($lines)->toHaveCount(2);

    $totalDebit = (int) $lines->sum('debit');
    $totalCredit = (int) $lines->sum('credit');
    expect($totalDebit)->toBe(450);
    expect($totalCredit)->toBe(450);

    expect(DB::table('accounts')->where('site_id', $siteId)->count())->toBeGreaterThanOrEqual(2);
});
