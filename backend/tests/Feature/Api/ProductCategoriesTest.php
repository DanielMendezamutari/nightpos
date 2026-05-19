<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows manager to list product categories', function (): void {
    $manager = User::factory()->create(['role' => 'manager']);
    $this->actingAs($manager);

    $this->getJson('/api/product-categories')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('creates a category with auto slug', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->postJson('/api/product-categories', [
        'name' => 'Promos Noche',
        'product_type' => 'drink',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Promos Noche')
        ->assertJsonPath('data.slug', 'promos_noche');

    $this->assertDatabaseHas('product_categories', [
        'slug' => 'promos_noche',
        'name' => 'Promos Noche',
        'product_type' => 'drink',
    ]);
});

it('creates a category with explicit slug', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->postJson('/api/product-categories', [
        'name' => 'Mi categoria',
        'slug' => 'mi_cat',
        'sort_order' => 5,
        'product_type' => 'supply',
    ])->assertCreated()
        ->assertJsonPath('data.slug', 'mi_cat')
        ->assertJsonPath('data.sort_order', 5)
        ->assertJsonPath('data.product_type', 'supply');
});

it('rejects duplicate slug', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    DB::table('product_categories')->insert([
        'slug' => 'dup',
        'name' => 'Dup',
        'sort_order' => 1,
        'product_type' => 'drink',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/api/product-categories', [
        'name' => 'Otro',
        'slug' => 'dup',
        'product_type' => 'drink',
    ])->assertStatus(422);
});

it('updates category and syncs product_type on products', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $catId = DB::table('product_categories')->insertGetId([
        'slug' => 'test_cat_u',
        'name' => 'Test U',
        'sort_order' => 1,
        'product_type' => 'drink',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'sku' => 'SKU-CAT-1',
        'name' => 'P',
        'category_id' => $catId,
        'product_type' => 'drink',
        'price_solo' => 1,
        'price_with_companion' => 2,
        'base_stock' => 0,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->patchJson("/api/product-categories/{$catId}", [
        'name' => 'Test U2',
        'product_type' => 'supply',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Test U2')
        ->assertJsonPath('data.product_type', 'supply');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'product_type' => 'supply',
    ]);
});

it('forbids deleting category in use', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $catId = DB::table('product_categories')->insertGetId([
        'slug' => 'cat_en_uso',
        'name' => 'En uso',
        'sort_order' => 500,
        'product_type' => 'drink',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insertGetId([
        'sku' => 'P-CAT-USE',
        'name' => 'Producto',
        'category_id' => $catId,
        'product_type' => 'drink',
        'price_solo' => 1,
        'price_with_companion' => 2,
        'base_stock' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/product-categories/{$catId}")
        ->assertStatus(422);
});

it('allows deleting unused category', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $catId = DB::table('product_categories')->insertGetId([
        'slug' => 'borrar_esto',
        'name' => 'Borrar',
        'sort_order' => 999,
        'product_type' => 'drink',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/product-categories/{$catId}")
        ->assertNoContent();

    $this->assertDatabaseMissing('product_categories', ['id' => $catId]);
});

it('forbids waiter from creating categories', function (): void {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $this->postJson('/api/product-categories', [
        'name' => 'X',
        'product_type' => 'drink',
    ])->assertForbidden();
});
