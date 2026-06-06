<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('creates tenant as superadmin', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->postJson('/api/v1/admin/tenants', [
        'name' => 'Bar Nueva',
        'slug' => 'bar-nueva',
        'status' => 'active',
        'plan_name' => 'pro',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'bar-nueva');

    $this->getJson('/api/v1/admin/tenants', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonFragment(['slug' => 'bar-nueva']);
});

it('creates branch when superadmin sends tenant header', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->postJson('/api/v1/admin/branches', [
        'name' => 'Sucursal Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.$token,
        'X-Tenant-Slug' => 'casa-demo',
        'Accept' => 'application/json',
    ])
        ->assertCreated();

    $this->getJson('/api/v1/admin/branches', [
        'Authorization' => 'Bearer '.$token,
        'X-Tenant-Slug' => 'casa-demo',
        'Accept' => 'application/json',
    ])
        ->assertOk()
        ->assertJsonFragment(['code' => 'NORTE']);
});
