<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('lists only active branch users for admin', function (): void {
    $siteA = DB::table('sites')->insertGetId([
        'code' => 'UL-A',
        'name' => 'A',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $siteB = DB::table('sites')->insertGetId([
        'code' => 'UL-B',
        'name' => 'B',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteA, 'active_site_id' => $siteA]);
    User::factory()->create(['role' => 'waiter', 'site_id' => $siteA, 'active_site_id' => $siteA, 'name' => 'A waiter']);
    User::factory()->create(['role' => 'waiter', 'site_id' => $siteB, 'active_site_id' => $siteB, 'name' => 'B waiter']);

    $this->actingAs($admin);

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'A waiter');
});
