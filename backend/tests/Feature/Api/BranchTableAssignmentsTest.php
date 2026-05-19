<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeBranchForAssign(string $code): int
{
    return DB::table('sites')->insertGetId([
        'code' => $code,
        'name' => 'Sucursal '.$code,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('updates waiter table limit from admin', function (): void {
    $siteId = makeBranchForAssign('ASG-01');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->patchJson("/api/branch/waiters/{$waiter->id}/table-limit", [
        'max_active_tables' => 7,
    ])->assertOk()
        ->assertJsonPath('data.max_active_tables', 7);

    $this->assertDatabaseHas('users', [
        'id' => $waiter->id,
        'max_active_tables' => 7,
    ]);
});

it('assigns table to waiter when under limit', function (): void {
    $siteId = makeBranchForAssign('ASG-02');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId, 'max_active_tables' => 2]);
    $this->actingAs($admin);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-1',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson("/api/branch/tables/{$tableId}/assign", [
        'waiter_user_id' => $waiter->id,
    ])->assertOk()
        ->assertJsonPath('data.waiter_user_id', $waiter->id);

    $this->assertDatabaseHas('site_table_assignments', [
        'site_id' => $siteId,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
    ]);
});

it('blocks assignment when waiter reaches limit', function (): void {
    $siteId = makeBranchForAssign('ASG-03');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId, 'max_active_tables' => 1]);
    $this->actingAs($admin);

    $tableA = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-1',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $tableB = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-2',
        'seats' => 4,
        'sort_order' => 20,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $siteId,
        'site_table_id' => $tableA,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson("/api/branch/tables/{$tableB}/assign", [
        'waiter_user_id' => $waiter->id,
    ])->assertStatus(422);
});

it('allows assigning waiter whose home site differs when they have user_site_access', function (): void {
    $siteA = makeBranchForAssign('ASG-HOME');
    $siteB = makeBranchForAssign('ASG-ROT');
    $adminB = User::factory()->create(['role' => 'admin', 'site_id' => $siteB]);
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteA, 'max_active_tables' => 5]);

    DB::table('user_site_accesses')->insert([
        'user_id' => $waiter->id,
        'site_id' => $siteB,
        'is_default' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($adminB);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteB,
        'site_room_id' => null,
        'code' => 'M-ROT',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson("/api/branch/tables/{$tableId}/assign", [
        'waiter_user_id' => $waiter->id,
    ])->assertOk()
        ->assertJsonPath('data.waiter_user_id', $waiter->id);
});

it('unassigns table from waiter', function (): void {
    $siteId = makeBranchForAssign('ASG-04');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteId,
        'site_room_id' => null,
        'code' => 'M-5',
        'seats' => 4,
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $siteId,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/branch/tables/{$tableId}/assign")
        ->assertNoContent();

    $this->assertDatabaseMissing('site_table_assignments', [
        'site_table_id' => $tableId,
    ]);
});
