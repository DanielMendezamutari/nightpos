<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function assertPdf(TestResponse $response): void
{
    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
}

it('exports maintenance catalog, valued kardex, product kardex, branch profile and refill recipes pdfs', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-M',
        'name' => 'PDF mant.',
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

    $productId = $this->postJson('/api/products', [
        'sku' => 'PDF-P1',
        'name' => 'PDF prod',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 1,
        'track_stock' => true,
    ])->assertCreated()->json('data.id');

    assertPdf($this->get('/api/maintenance/products/pdf'));
    assertPdf($this->get('/api/maintenance/kardex-valued/pdf'));
    assertPdf($this->get("/api/maintenance/products/{$productId}/kardex/pdf"));
    assertPdf($this->get('/api/branch/profile/pdf'));
    assertPdf($this->get('/api/maintenance/refill-recipes/pdf'));
});

it('exports stock transfer pdf when admin has access to both branches', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'PDF-TR-A',
        'name' => 'A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'PDF-TR-B',
        'name' => 'B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);
    $now = now();
    DB::table('user_site_accesses')->insert([
        [
            'user_id' => $admin->id,
            'site_id' => $siteA,
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'user_id' => $admin->id,
            'site_id' => $siteB,
            'is_default' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $this->actingAs($admin);

    $productId = $this->postJson('/api/products', [
        'sku' => 'PDF-TR-P',
        'name' => 'Tr prod',
        'category_id' => productCategoryId('soft_drinks'),
        'price_solo' => 10,
        'price_with_companion' => 18,
        'base_stock' => 5,
        'purchase_price' => 5,
        'track_stock' => true,
    ])->assertCreated()->json('data.id');

    $this->postJson('/api/maintenance/transfers', [
        'to_site_id' => $siteB,
        'lines' => [
            ['product_id' => $productId, 'quantity' => 1],
        ],
    ])->assertCreated();

    $transferId = (int) DB::table('site_stock_transfers')->orderByDesc('id')->value('id');

    assertPdf($this->get("/api/maintenance/transfers/{$transferId}/pdf"));
});

it('exports shift cash pdf for cashier when shift belongs to active site', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-SH',
        'name' => 'Shift site',
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
        'opening_cash' => 100,
        'opened_at' => now()->subHour(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($cashier);

    assertPdf($this->get("/api/shifts/{$shiftId}/pdf"));
});

it('exports pos order pdf for admin scoped to branch', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-PO',
        'name' => 'POS PDF',
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
    $admin = User::factory()->create([
        'role' => 'admin',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $shiftId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 100,
        'opened_at' => now()->subHour(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'T1',
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

    $this->actingAs($admin);

    assertPdf($this->get("/api/pos/orders/{$orderId}/pdf"));
});

it('exports companion ranking and waiter commissions pdfs as manager', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-RP',
        'name' => 'Rep site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $manager = User::factory()->create([
        'role' => 'manager',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($manager);

    assertPdf($this->get('/api/reports/companions/ranking/pdf'));
    assertPdf($this->get('/api/reports/waiters/commissions/pdf'));
    assertPdf($this->get('/api/reports/products/sold/pdf'));
    assertPdf($this->get('/api/reports/sales/summary/pdf'));
    assertPdf($this->get('/api/reports/staff/sales/pdf'));
});

it('exports saas subscription payments pdf as owner', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-SAAS',
        'name' => 'SaaS PDF',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 500,
        'status' => 'active',
        'last_paid_at' => now()->subDay(),
        'next_due_at' => now()->addMonth(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($owner);

    assertPdf($this->get("/api/saas/subscriptions/{$siteId}/payments/pdf"));
});

it('exports room time service pdf as cashier', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'PDF-RM',
        'name' => 'Room PDF',
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

    $this->actingAs($cashier);

    $companionId = DB::table('companions')->insertGetId([
        'stage_name' => 'PDF Luna',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $open = $this->postJson('/api/room-services', [
        'room_label' => 'P1',
        'companion_id' => $companionId,
        'rate_per_hour' => 800,
        'payment_method' => 'cash',
        'payment_amount' => 50,
    ])->assertCreated();

    $serviceId = $open->json('data.id');

    assertPdf($this->get("/api/room-services/{$serviceId}/pdf"));
});
