<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('creates tenant as superadmin with full provisioning', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
    $starterId = PlanModel::query()->where('code', 'STARTER')->value('id');

    $this->postJson('/api/v1/admin/tenants', [
        'name' => 'Bar Nueva',
        'slug' => 'bar-nueva',
        'status' => 'active',
        'plan_id' => $starterId,
        'branch' => [
            'name' => 'Sucursal Norte',
            'code' => 'NORTE',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin Bar',
            'username' => 'admin.bar',
            'password' => 'BarAdmin123!',
            'pin' => '8888',
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])
        ->assertCreated()
        ->assertJsonPath('data.tenant.slug', 'bar-nueva')
        ->assertJsonPath('data.branch.code', 'NORTE');

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
