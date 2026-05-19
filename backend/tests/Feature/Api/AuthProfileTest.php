<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates own profile fields', function (): void {
    $user = User::factory()->create([
        'role' => 'admin',
        'pin_code' => '1111',
    ]);
    $this->actingAs($user);

    $this->patchJson('/api/auth/me', [
        'name' => 'Admin Nuevo',
        'email' => 'admin.nuevo@example.com',
        'pin' => '2222',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Admin Nuevo')
        ->assertJsonPath('data.email', 'admin.nuevo@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Admin Nuevo',
        'email' => 'admin.nuevo@example.com',
        'pin_code' => '2222',
    ]);
});
