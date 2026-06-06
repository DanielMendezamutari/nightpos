<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('allows superadmin pin login in tenant branch scope', function () {
    $response = $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '0001',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.username', 'superadmin')
        ->assertJsonPath('data.user.role', 'super_admin');
});
