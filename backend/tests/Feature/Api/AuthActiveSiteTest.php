<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeSitePair(): array
{
    $a = DB::table('sites')->insertGetId([
        'code' => 'AS-A',
        'name' => 'Alpha',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $b = DB::table('sites')->insertGetId([
        'code' => 'AS-B',
        'name' => 'Beta',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$a, $b];
}

it('returns site options for waiter and allows switching active site', function (): void {
    [$siteA, $siteB] = makeSitePair();
    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);

    DB::table('user_site_accesses')->insert([
        ['user_id' => $waiter->id, 'site_id' => $siteA, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $waiter->id, 'site_id' => $siteB, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/auth/site-options')
        ->assertOk()
        ->assertJsonPath('data.active_site_id', $siteA)
        ->assertJsonCount(2, 'data.sites');

    $this->patchJson('/api/auth/active-site', ['site_id' => $siteB])
        ->assertOk()
        ->assertJsonPath('data.active_site_id', $siteB)
        ->assertJsonPath('data.user.active_site_id', $siteB);

    $this->assertDatabaseHas('users', ['id' => $waiter->id, 'active_site_id' => $siteB]);
});

it('forbids switching to unauthorized site', function (): void {
    [$siteA, $siteB] = makeSitePair();
    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);
    DB::table('user_site_accesses')->insert([
        'user_id' => $waiter->id,
        'site_id' => $siteA,
        'is_default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->patchJson('/api/auth/active-site', ['site_id' => $siteB])
        ->assertStatus(403);
});

it('persists active_site_id on login when user has a single accessible site', function (): void {
    $site = DB::table('sites')->insertGetId([
        'code' => 'ONE-S',
        'name' => 'Unica',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => $site,
        'active_site_id' => null,
    ]);

    DB::table('user_site_accesses')->insert([
        'user_id' => $waiter->id,
        'site_id' => $site,
        'is_default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/auth/login', [
        'email' => $waiter->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.active_site_id', $site);

    $this->assertDatabaseHas('users', ['id' => $waiter->id, 'active_site_id' => $site]);
});

it('returns requires_open_shift when cashier switches active site', function (): void {
    [$siteA, $siteB] = makeSitePair();
    $cashier = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $siteA,
        'active_site_id' => $siteA,
    ]);

    DB::table('user_site_accesses')->insert([
        ['user_id' => $cashier->id, 'site_id' => $siteA, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $cashier->id, 'site_id' => $siteB, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($cashier);

    $this->patchJson('/api/auth/active-site', ['site_id' => $siteB])
        ->assertOk()
        ->assertJsonStructure(['data' => ['requires_open_shift', 'user', 'active_site_id']]);
});

it('includes site from table assignments in accessible sites for waiter without user_site_access', function (): void {
    [$siteOnly, $siteOther] = makeSitePair();

    $waiter = User::factory()->create([
        'role' => 'waiter',
        'site_id' => null,
        'active_site_id' => null,
    ]);

    $tableId = DB::table('site_tables')->insertGetId([
        'site_id' => $siteOnly,
        'site_room_id' => null,
        'code' => 'ASG-1',
        'seats' => 2,
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_table_assignments')->insert([
        'site_id' => $siteOnly,
        'site_table_id' => $tableId,
        'waiter_user_id' => $waiter->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($waiter);

    $this->getJson('/api/auth/site-options')
        ->assertOk()
        ->assertJsonCount(1, 'data.sites')
        ->assertJsonPath('data.sites.0.id', $siteOnly);

    $this->patchJson('/api/auth/active-site', ['site_id' => $siteOnly])
        ->assertOk()
        ->assertJsonPath('data.active_site_id', $siteOnly);

    $this->patchJson('/api/auth/active-site', ['site_id' => $siteOther])
        ->assertStatus(403);
});

it('allows login with numeric pin', function (): void {
    $site = DB::table('sites')->insertGetId([
        'code' => 'PIN-1',
        'name' => 'Pin Site',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::factory()->create([
        'role' => 'cashier',
        'site_id' => $site,
        'active_site_id' => $site,
        'pin_code' => '2222',
    ]);

    DB::table('user_site_accesses')->insert([
        'user_id' => $user->id,
        'site_id' => $site,
        'is_default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/auth/login', ['pin' => '2222'])
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.active_site_id', $site);
});
