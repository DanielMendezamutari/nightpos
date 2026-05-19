<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeBranchForContacts(string $code): int
{
    return DB::table('sites')->insertGetId([
        'code' => $code,
        'name' => 'Sucursal '.$code,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('creates and lists client contact', function (): void {
    $siteId = makeBranchForContacts('CNT-01');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $this->postJson('/api/branch/contacts', [
        'contact_type' => 'client',
        'display_name' => 'Cliente Uno',
        'phone' => '70000001',
    ])->assertCreated()
        ->assertJsonPath('data.contact_type', 'client');

    $this->getJson('/api/branch/contacts?type=client')
        ->assertOk()
        ->assertJsonCount(1, 'data.contacts')
        ->assertJsonPath('data.contacts.0.display_name', 'Cliente Uno');
});

it('creates companion contact with commission', function (): void {
    $siteId = makeBranchForContacts('CNT-02');
    $manager = User::factory()->create(['role' => 'manager', 'site_id' => $siteId]);
    $this->actingAs($manager);

    $this->postJson('/api/branch/contacts', [
        'contact_type' => 'companion',
        'display_name' => 'Chica Ana',
        'commission_percent' => 35.5,
    ])->assertCreated();

    $this->assertDatabaseHas('site_contacts', [
        'site_id' => $siteId,
        'contact_type' => 'companion',
        'display_name' => 'Chica Ana',
    ]);
});

it('updates supplier contact', function (): void {
    $siteId = makeBranchForContacts('CNT-03');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $id = DB::table('site_contacts')->insertGetId([
        'site_id' => $siteId,
        'contact_type' => 'supplier',
        'display_name' => 'Proveedor X',
        'phone' => null,
        'email' => null,
        'document_type' => null,
        'document_number' => null,
        'business_name' => 'Comercial X',
        'service_category' => null,
        'commission_percent' => null,
        'notes' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->patchJson("/api/branch/contacts/{$id}", [
        'phone' => '70012345',
        'service_category' => 'Bebidas',
    ])->assertOk();

    $this->assertDatabaseHas('site_contacts', [
        'id' => $id,
        'phone' => '70012345',
        'service_category' => 'Bebidas',
    ]);
});

it('deletes contact', function (): void {
    $siteId = makeBranchForContacts('CNT-04');
    $admin = User::factory()->create(['role' => 'admin', 'site_id' => $siteId]);
    $this->actingAs($admin);

    $id = DB::table('site_contacts')->insertGetId([
        'site_id' => $siteId,
        'contact_type' => 'client',
        'display_name' => 'Eliminar',
        'phone' => null,
        'email' => null,
        'document_type' => null,
        'document_number' => null,
        'business_name' => null,
        'service_category' => null,
        'commission_percent' => null,
        'notes' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/branch/contacts/{$id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('site_contacts', ['id' => $id]);
});
