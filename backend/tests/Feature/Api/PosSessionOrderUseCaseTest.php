<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('rejects pos order when no shift is open for the session site', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'POS-U-1',
        'name' => 'Order no shift',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $sessionId = DB::table('customer_sessions')->insertGetId([
        'site_id' => $siteId,
        'table_code' => 'X1',
        'zone_code' => null,
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->postJson('/api/pos/orders', [
        'customer_session_id' => $sessionId,
    ])->assertStatus(422)
        ->assertJsonPath('message', 'No hay caja/turno abierto para registrar orden.');
});
