<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeBranchForTables(string $code): int
{
    return DB::table('sites')->insertGetId([
        'code' => $code,
        'name' => 'Sucursal '.$code,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('creates multiple tables in batch for a room', function (): void {
    $siteId = makeBranchForTables('TB-01');
    $roomId = DB::table('site_rooms')->insertGetId([
        'site_id' => $siteId,
        'code' => 'VIP',
        'name' => 'VIP',
        'kind' => 'vip',
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->postJson('/api/branch/tables', [
        'site_room_id' => $roomId,
        'prefix' => 'VIP',
        'quantity' => 3,
        'start_number' => 1,
        'seats' => 6,
    ])->assertCreated()
        ->assertJsonPath('data.created_count', 3)
        ->assertJsonPath('data.tables.0.code', 'VIP-1')
        ->assertJsonPath('data.tables.2.code', 'VIP-3');

    $this->assertDatabaseHas('site_tables', ['site_id' => $siteId, 'code' => 'VIP-2', 'seats' => 6]);
});

it('lists branch tables', function (): void {
    $siteId = makeBranchForTables('TB-02');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    DB::table('site_tables')->insert([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-1',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/branch/tables')
        ->assertOk()
        ->assertJsonPath('data.site_id', $siteId)
        ->assertJsonCount(1, 'data.tables')
        ->assertJsonPath('data.tables.0.code', 'M-1');
});

it('rejects creating duplicate table codes', function (): void {
    $siteId = makeBranchForTables('TB-03');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    DB::table('site_tables')->insert([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-1',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/branch/tables', [
        'prefix' => 'M',
        'quantity' => 2,
        'start_number' => 1,
    ])->assertStatus(422);
});

it('forbids deleting table with open session', function (): void {
    $siteId = makeBranchForTables('TB-04');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-7',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_sessions')->insert([
        'site_id' => $siteId,
        'table_code' => 'M-7',
        'zone_code' => null,
        'status' => 'open',
        'opened_at' => now(),
        'closed_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/branch/tables/{$tableId}")
        ->assertStatus(422);
});

it('allows deleting table when not in use', function (): void {
    $siteId = makeBranchForTables('TB-05');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-9',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/branch/tables/{$tableId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('site_tables', ['id' => $tableId]);
});
