<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns waiter tables from home site when active_site points elsewhere but assignments are only at home', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'WT-A',
        'name' => 'Alpha',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'WT-B',
        'name' => 'Beta',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteA,
        'active_site_id' => $siteB,
    ]);

    DB::table('user_site_accesses')->insert([
        ['user_id' => $waiter->id, 'site_id' => $siteA, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $waiter->id, 'site_id' => $siteB, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteA,
        'site_room_id' => null,
        'code' => 'T-1',
        'seats' => 4,
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $siteA,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/waiter/tables')
        ->assertOk()
        ->assertJsonPath('meta.site_id', $siteA)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.table_code', 'T-1');
});

it('includes home site_id in accessible sites when user_site_access omits it so assignments on home are visible', function (): void {
    $siteHome = DB::table('sites')->insertGetId([
        'code' => 'WT-H',
        'name' => 'Home',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteOther = DB::table('sites')->insertGetId([
        'code' => 'WT-O',
        'name' => 'Other',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteHome,
        'active_site_id' => $siteOther,
    ]);

    DB::table('user_site_accesses')->insert([
        'user_id' => $waiter->id,
        'site_id' => $siteOther,
        'is_default' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteHome,
        'site_room_id' => null,
        'code' => 'H-1',
        'seats' => 4,
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $siteHome,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/waiter/tables')
        ->assertOk()
        ->assertJsonPath('meta.site_id', $siteHome)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.table_code', 'H-1');
});

it('returns tables when waiter only has site_table_assignments and no user_site_access', function (): void {
    $site = DB::table('sites')->insertGetId([
        'code' => 'WT-ASG',
        'name' => 'Solo asignaciones',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => null,
        'active_site_id' => null,
    ]);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $site,
        'site_room_id' => null,
        'code' => 'Z-9',
        'seats' => 2,
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $site,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/waiter/tables')
        ->assertOk()
        ->assertJsonPath('meta.site_id', $site)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.table_code', 'Z-9');
});

it('allows waiter to GET pos orders without duplicate route masking as cashier-only', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'WT-LIST-ORD',
        'name' => 'Orders list',
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

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteId,
        'active_site_id' => $siteId,
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/pos/orders')
        ->assertOk()
        ->assertJsonStructure(['data']);
});
