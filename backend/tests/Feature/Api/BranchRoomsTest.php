<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('lists rooms for admin branch', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-T1',
        'name' => 'Test Club',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    DB::table('site_rooms')->insert([
        'site_id' => $siteId,
        'code' => 'VIP',
        'name' => 'VIP',
        'kind' => 'vip',
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/branch/rooms')
        ->assertOk()
        ->assertJsonPath('data.site_id', $siteId)
        ->assertJsonCount(1, 'data.rooms')
        ->assertJsonPath('data.rooms.0.code', 'VIP')
        ->assertJsonPath('data.rooms.0.kind_label', 'VIP / palcos');
});

it('requires site_id for super_admin', function (): void {
    $super = User::factory()->create(['role' => 'super_admin', 'site_id' => null]);
    $this->actingAs($super);

    $this->getJson('/api/branch/rooms')
        ->assertStatus(422);
});

it('creates room with normalized code', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-T2',
        'name' => 'Test 2',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->postJson('/api/branch/rooms', [
        'code' => 'pista-1',
        'name' => 'Pista',
        'kind' => 'dance_floor',
    ])->assertCreated()
        ->assertJsonPath('data.code', 'PISTA_1');

    $this->assertDatabaseHas('site_rooms', [
        'site_id' => $siteId,
        'code' => 'PISTA_1',
        'kind' => 'dance_floor',
    ]);
});

it('rejects duplicate room code per site', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-T3',
        'name' => 'Test 3',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->postJson('/api/branch/rooms', [
        'code' => 'BAR',
        'name' => 'Barra',
        'kind' => 'bar',
    ])->assertCreated();

    $this->postJson('/api/branch/rooms', [
        'code' => 'BAR',
        'name' => 'Otra',
        'kind' => 'bar',
    ])->assertStatus(422);
});

it('forbids delete when sessions reference room', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-T4',
        'name' => 'Test 4',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roomId = DB::table('site_rooms')->insertGetId([
        'site_id' => $siteId,
        'code' => 'X1',
        'name' => 'X',
        'kind' => 'other',
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_sessions')->insert([
        'site_id' => $siteId,
        'site_room_id' => $roomId,
        'table_code' => 'M1',
        'zone_code' => 'X1',
        'status' => 'open',
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->deleteJson("/api/branch/rooms/{$roomId}")
        ->assertStatus(422);
});

it('allows delete when room unused', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'ROOM-T5',
        'name' => 'Test 5',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roomId = DB::table('site_rooms')->insertGetId([
        'site_id' => $siteId,
        'code' => 'DELME',
        'name' => 'Borrar',
        'kind' => 'other',
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->deleteJson("/api/branch/rooms/{$roomId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('site_rooms', ['id' => $roomId]);
});

it('forbids waiter from branch rooms', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $this->getJson('/api/branch/rooms')->assertForbidden();
});
