<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('registers drawer movement and includes it in expected cash on close', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CD-01',
        'name' => 'Caja mov',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($cashier);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'day',
        'opening_cash' => 1000,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson("/api/shifts/{$shiftTurnId}/cash-movements", [
        'direction' => 'out',
        'amount' => 200,
        'notes' => 'Retiro parcial',
    ])->assertCreated();

    $this->postJson("/api/shifts/{$shiftTurnId}/cash-movements", [
        'direction' => 'in',
        'amount' => 50,
        'notes' => 'Cambio suelto',
    ])->assertCreated();

    $this->getJson("/api/shifts/{$shiftTurnId}/cash-summary")
        ->assertOk()
        ->assertJsonPath('data.expected_cash', 850);

    $this->postJson("/api/shifts/{$shiftTurnId}/close", [
        'closing_cash' => 850,
    ])->assertOk()
        ->assertJsonPath('data.expected_cash', 850)
        ->assertJsonPath('data.difference', 0)
        ->assertJsonPath('data.drawer_in', 50)
        ->assertJsonPath('data.drawer_out', 200);
});

it('returns current open shift for site', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'CD-02',
        'name' => 'Caja cur',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);
    $this->actingAs($cashier);

    $shiftTurnId = DB::table('shift_turns')->insertGetId([
        'site_id' => $siteId,
        'cashier_user_id' => $cashier->id,
        'period' => 'night',
        'opening_cash' => 300,
        'opened_at' => now(),
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/shifts/current')
        ->assertOk()
        ->assertJsonPath('data.id', $shiftTurnId);
});
