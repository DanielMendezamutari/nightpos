<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('updates user basic fields for admin in same branch', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'UC-A',
        'name' => 'Sucursal A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId, 'active_site_id' => $siteId]);
    $staff = User::factory()->create(['role' => 'cashier', 'site_id' => $siteId, 'active_site_id' => $siteId]);
    $this->actingAs($admin);

    $this->patchJson("/api/users/{$staff->id}", [
        'name' => 'Caja Nueva',
        'email' => 'caja.nueva@example.com',
        'role' => 'manager',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Caja Nueva')
        ->assertJsonPath('data.role', 'manager');
});

it('deletes user in same branch by admin', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'UC-B',
        'name' => 'Sucursal B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId, 'active_site_id' => $siteId]);
    $staff = User::factory()->create(['role' => 'waiter', 'site_id' => $siteId, 'active_site_id' => $siteId]);
    $this->actingAs($admin);

    $this->deleteJson("/api/users/{$staff->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $staff->id]);
});
