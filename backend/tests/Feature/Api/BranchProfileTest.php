<?php

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function seedSite(array $overrides = []): Site
{
    return Site::create(array_merge([
        'code' => 'TST'.uniqid(),
        'name' => 'Sucursal prueba',
        'is_active' => true,
    ], $overrides));
}

it('allows admin to read branch profile for their site', function (): void {
    $site = seedSite(['code' => 'ADM1']);
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $response = $this->getJson('/api/branch/profile');

    $response->assertOk()
        ->assertJsonPath('data.code', 'ADM1')
        ->assertJsonPath('data.currency_code', 'BOB');
});

it('allows admin to update fiscal and series fields', function (): void {
    $site = seedSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $response = $this->patchJson('/api/branch/profile', [
        'legal_document_type' => 'NIT',
        'legal_document_number' => '123456789',
        'legal_name' => 'Razon Social SA',
        'branch_address' => 'Av. Principal 100',
        'authorization_date' => '2025-01-15',
        'manager_document_type' => 'CI',
        'manager_full_name' => 'Maria Encargada',
        'currency_code' => 'BOB',
        'ticket_series_start' => 100,
        'boleta_series_start' => 200,
        'factura_series_start' => 300,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.legal_name', 'Razon Social SA')
        ->assertJsonPath('data.ticket_series_start', 100)
        ->assertJsonPath('data.boleta_series_start', 200)
        ->assertJsonPath('data.factura_series_start', 300);

    $this->assertDatabaseHas('sites', [
        'id' => $site->id,
        'legal_name' => 'Razon Social SA',
        'ticket_series_start' => 100,
    ]);
});

it('requires site_id for super_admin on branch profile', function (): void {
    $super = User::factory()->create(['role' => 'super_admin', 'site_id' => null]);
    $this->actingAs($super);

    $this->getJson('/api/branch/profile')->assertStatus(422);
});

it('allows super_admin with site_id to read and update', function (): void {
    $site = seedSite(['code' => 'SUP1']);
    $super = User::factory()->create(['role' => 'super_admin', 'site_id' => null]);
    $this->actingAs($super);

    $this->getJson('/api/branch/profile?site_id='.$site->id)
        ->assertOk()
        ->assertJsonPath('data.code', 'SUP1');

    $this->patchJson('/api/branch/profile?site_id='.$site->id, [
        'branch_phone' => '70000000',
    ])->assertOk()
        ->assertJsonPath('data.branch_phone', '70000000');
});

it('allows manager to update branch profile', function (): void {
    $site = seedSite();
    $manager = User::factory()->create(['role' => 'manager', 'site_id' => $site->id]);
    $this->actingAs($manager);

    $this->patchJson('/api/branch/profile', [
        'economic_activity' => 'Restaurante',
    ])->assertOk()
        ->assertJsonPath('data.economic_activity', 'Restaurante');
});

it('forbids waiter from branch profile', function (): void {
    $site = seedSite();
    $waiter = User::factory()->create(['role' => 'waiter', 'site_id' => $site->id]);
    $this->actingAs($waiter);

    $this->getJson('/api/branch/profile')->assertForbidden();
});

it('allows owner to manage branch with site_id', function (): void {
    $site = seedSite();
    $owner = User::factory()->create(['role' => 'owner', 'site_id' => null]);
    $this->actingAs($owner);

    $this->getJson('/api/branch/profile?site_id='.$site->id)->assertOk();
});

it('lists sites for owner and super_admin', function (): void {
    seedSite(['code' => 'L1', 'name' => 'Alpha']);
    seedSite(['code' => 'L2', 'name' => 'Beta']);
    $owner = User::factory()->create(['role' => 'owner', 'site_id' => null]);
    $this->actingAs($owner);

    $response = $this->getJson('/api/sites');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('stores branch logo', function (): void {
    Storage::fake('public');
    $site = seedSite();
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $site->id]);
    $this->actingAs($admin);

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    $tmp = tempnam(sys_get_temp_dir(), 'np_logo');
    file_put_contents($tmp, $png);
    $file = new UploadedFile($tmp, 'logo.png', 'image/png', null, true);

    $response = $this->post('/api/branch/logo', [
        'logo' => $file,
    ]);

    $response->assertOk();
    $path = $response->json('data.logo_path');
    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});
