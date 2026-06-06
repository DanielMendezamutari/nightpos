<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('rejects duplicate pin when creating a user', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->postJson('/api/v1/admin/users', [
        'name' => 'Otro Cajero',
        'username' => 'cajero2.demo',
        'pin' => '1234',
        'staff_role' => 'CASHIER',
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'El PIN ya está asignado a otro usuario.');
});

it('allows waiter login with unique pin', function () {
    expect(nightposLoginPin('5678'))->not->toBeEmpty();
});
