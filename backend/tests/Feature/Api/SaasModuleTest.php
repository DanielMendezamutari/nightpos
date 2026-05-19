<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('shows owner saas overview with active branches', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteActive = DB::table('sites')->insertGetId([
        'code' => 'SAAS-ACT',
        'name' => 'Sucursal Activa',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $siteSuspended = DB::table('sites')->insertGetId([
        'code' => 'SAAS-SUS',
        'name' => 'Sucursal Suspendida',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        [
            'site_id' => $siteActive,
            'monthly_fee' => 700,
            'status' => 'active',
            'last_paid_at' => now()->subDays(5),
            'next_due_at' => now()->addDays(25),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteSuspended,
            'monthly_fee' => 700,
            'status' => 'suspended',
            'last_paid_at' => now()->subDays(40),
            'next_due_at' => now()->subDays(10),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson('/api/saas/overview');

    $response->assertOk()
        ->assertJsonPath('data.active_branches', 1)
        ->assertJsonPath('data.suspended_branches', 1)
        ->assertJsonPath('data.total_branches', 2);
});

it('registers subscription payment for a branch', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'SAAS-PAY',
        'name' => 'Sucursal Pago',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 650,
        'status' => 'suspended',
        'last_paid_at' => now()->subDays(40),
        'next_due_at' => now()->subDays(10),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson("/api/saas/subscriptions/{$siteId}/payments", [
        'amount' => 1300,
        'months_covered' => 2,
        'note' => 'Pago de 2 meses',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.site_id', $siteId)
        ->assertJsonPath('data.amount', 1300)
        ->assertJsonPath('data.status', 'active');

    $this->assertDatabaseHas('saas_subscription_payments', [
        'site_id' => $siteId,
        'amount' => 1300,
        'months_covered' => 2,
    ]);

    $this->assertDatabaseHas('saas_subscriptions', [
        'site_id' => $siteId,
        'status' => 'active',
    ]);
});

it('blocks branch users when saas subscription is suspended', function (): void {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'SAAS-LOCK',
        'name' => 'Sucursal Bloqueada',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier->site_id = $siteId;
    $cashier->save();
    $this->actingAs($cashier);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 700,
        'status' => 'suspended',
        'last_paid_at' => now()->subDays(35),
        'next_due_at' => now()->subDays(5),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/shifts/open', [
        'cashier_user_id' => $cashier->id,
        'site_id' => $siteId,
        'period' => 'night',
        'opening_cash' => 200,
    ])->assertStatus(423);
});

it('shows due status and days remaining in subscriptions list', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteOk = DB::table('sites')->insertGetId([
        'code' => 'SAAS-OK',
        'name' => 'Sucursal OK',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $siteSoon = DB::table('sites')->insertGetId([
        'code' => 'SAAS-SOON',
        'name' => 'Sucursal Por Vencer',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        [
            'site_id' => $siteOk,
            'monthly_fee' => 700,
            'status' => 'active',
            'last_paid_at' => now()->subDays(5),
            'next_due_at' => now()->addDays(20),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteSoon,
            'monthly_fee' => 700,
            'status' => 'active',
            'last_paid_at' => now()->subDays(20),
            'next_due_at' => now()->addDays(3),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson('/api/saas/subscriptions');
    $response->assertOk();

    $okRow = collect($response->json('data'))->firstWhere('site_id', $siteOk);
    $soonRow = collect($response->json('data'))->firstWhere('site_id', $siteSoon);

    expect($okRow['due_status'])->toBe('ok');
    expect($soonRow['due_status'])->toBe('warning');
});

it('returns payment history per branch', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'SAAS-HIST',
        'name' => 'Sucursal Historial',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 700,
        'status' => 'active',
        'last_paid_at' => now()->subDays(8),
        'next_due_at' => now()->addDays(22),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscription_payments')->insert([
        [
            'site_id' => $siteId,
            'amount' => 700,
            'months_covered' => 1,
            'paid_at' => now()->subDays(35),
            'note' => 'Pago anterior',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteId,
            'amount' => 700,
            'months_covered' => 1,
            'paid_at' => now()->subDays(5),
            'note' => 'Pago actual',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson("/api/saas/subscriptions/{$siteId}/payments");
    $response->assertOk()->assertJsonCount(2, 'data');
});

it('blocks super admin from creating branches', function (): void {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->actingAs($superAdmin);

    $this->postJson('/api/branches', [
        'code' => 'NOT-ALLOWED',
        'name' => 'No permitido',
        'is_active' => true,
    ])->assertForbidden();
});

it('filters subscriptions by due status', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteOk = DB::table('sites')->insertGetId([
        'code' => 'F-OK',
        'name' => 'OK',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteWarning = DB::table('sites')->insertGetId([
        'code' => 'F-WARN',
        'name' => 'WARN',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteOverdue = DB::table('sites')->insertGetId([
        'code' => 'F-OVER',
        'name' => 'OVER',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        [
            'site_id' => $siteOk,
            'monthly_fee' => 700,
            'status' => 'active',
            'suspended_reason' => null,
            'last_paid_at' => now(),
            'next_due_at' => now()->addDays(10),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteWarning,
            'monthly_fee' => 700,
            'status' => 'active',
            'suspended_reason' => null,
            'last_paid_at' => now(),
            'next_due_at' => now()->addDays(3),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteOverdue,
            'monthly_fee' => 700,
            'status' => 'suspended',
            'suspended_reason' => 'Vencida',
            'last_paid_at' => now()->subDays(40),
            'next_due_at' => now()->subDays(5),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $warningResponse = $this->getJson('/api/saas/subscriptions?due_status=warning');
    $warningResponse->assertOk()->assertJsonCount(1, 'data');

    $overdueResponse = $this->getJson('/api/saas/subscriptions?due_status=overdue');
    $overdueResponse->assertOk()->assertJsonCount(1, 'data');
});

it('exports subscription payments to csv', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CSV-01',
        'name' => 'CSV Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 700,
        'status' => 'active',
        'last_paid_at' => now(),
        'next_due_at' => now()->addDays(20),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscription_payments')->insert([
        'site_id' => $siteId,
        'amount' => 700,
        'months_covered' => 1,
        'paid_at' => now(),
        'note' => 'Pago CSV',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->get('/api/saas/subscriptions/'.$siteId.'/payments/export');
    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->getContent())->toContain('site_id,amount,months_covered,paid_at,note');
});

it('returns saas alerts grouped by critical and warning', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteCritical = DB::table('sites')->insertGetId([
        'code' => 'AL-CRIT',
        'name' => 'Critica',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteWarning = DB::table('sites')->insertGetId([
        'code' => 'AL-WARN',
        'name' => 'Warning',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        [
            'site_id' => $siteCritical,
            'monthly_fee' => 700,
            'status' => 'suspended',
            'suspended_reason' => 'Falta de pago',
            'last_paid_at' => now()->subDays(50),
            'next_due_at' => now()->subDays(10),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'site_id' => $siteWarning,
            'monthly_fee' => 700,
            'status' => 'active',
            'suspended_reason' => null,
            'last_paid_at' => now()->subDays(25),
            'next_due_at' => now()->addDays(2),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->getJson('/api/saas/alerts');
    $response->assertOk()
        ->assertJsonPath('data.critical_count', 1)
        ->assertJsonPath('data.warning_count', 1);
});

it('updates monthly fee for a branch subscription', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'FEE-01',
        'name' => 'Tarifa',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 700,
        'status' => 'active',
        'suspended_reason' => null,
        'last_paid_at' => now(),
        'next_due_at' => now()->addDays(20),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->patchJson("/api/saas/subscriptions/{$siteId}/monthly-fee", [
        'monthly_fee' => 900,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.site_id', $siteId)
        ->assertJsonPath('data.monthly_fee', 900);

    $this->assertDatabaseHas('saas_subscriptions', [
        'site_id' => $siteId,
        'monthly_fee' => 900,
    ]);
});

it('calculates saas quote with discount rules by months', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'Q-01',
        'name' => 'Sucursal Quote',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 1000,
        'billing_contact_name' => 'Cobranza Quote',
        'status' => 'active',
        'suspended_reason' => null,
        'last_paid_at' => now(),
        'next_due_at' => now()->addDays(20),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson("/api/saas/quote?site_id={$siteId}&months_covered=6");

    $response->assertOk()
        ->assertJsonPath('data.base_amount', 6000)
        ->assertJsonPath('data.discount_percent', 10)
        ->assertJsonPath('data.discount_amount', 600)
        ->assertJsonPath('data.total_amount', 5400);
});

it('applies discount breakdown when registering saas payment', function (): void {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    $siteId = DB::table('sites')->insertGetId([
        'code' => 'Q-02',
        'name' => 'Sucursal Pago Desc',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('saas_subscriptions')->insert([
        'site_id' => $siteId,
        'monthly_fee' => 1000,
        'billing_contact_name' => 'Cobranza Desc',
        'status' => 'active',
        'suspended_reason' => null,
        'last_paid_at' => now(),
        'next_due_at' => now()->addDays(20),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->postJson("/api/saas/subscriptions/{$siteId}/payments", [
        'months_covered' => 12,
        'note' => 'Pago anual con descuento',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.base_amount', 12000)
        ->assertJsonPath('data.discount_percent', 20)
        ->assertJsonPath('data.discount_amount', 2400)
        ->assertJsonPath('data.amount', 9600);

    $this->assertDatabaseHas('saas_subscription_payments', [
        'site_id' => $siteId,
        'base_amount' => 12000,
        'discount_percent' => 20,
        'discount_amount' => 2400,
        'final_amount' => 9600,
    ]);
});
