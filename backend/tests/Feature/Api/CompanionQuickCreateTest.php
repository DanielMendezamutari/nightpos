<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates companion and site contact when cashier registers quick', function (): void {
    $siteId = DB::table('sites')->insertGetId([
        'code' => 'QC-1',
        'name' => 'Quick Site',
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

    $this->postJson('/api/companions/quick-create', [
        'stage_name' => '  StellaNova  ',
    ])->assertCreated()
        ->assertJsonPath('data.stage_name', 'StellaNova')
        ->assertJsonPath('data.reused', false);

    $this->assertDatabaseHas('companions', [
        'stage_name' => 'StellaNova',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('site_contacts', [
        'site_id' => $siteId,
        'contact_type' => 'companion',
        'display_name' => 'StellaNova',
    ]);
});
