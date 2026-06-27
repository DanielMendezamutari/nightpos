<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('1. lista tenants activos para login', function () {
    $response = $this->getJson('/api/v1/auth/login-context/tenants')
        ->assertOk();

    $slugs = collect($response->json('data.tenants'))->pluck('slug')->all();

    expect($slugs)->toContain('casa-demo')
        ->and($response->json('data.tenants.0'))->toHaveKeys(['id', 'name', 'slug']);
});

it('2. no lista tenants inactivos', function () {
    TenantModel::query()->create([
        'name' => 'Bar Inactivo',
        'slug' => 'bar-inactivo',
        'status' => 'inactive',
        'plan_name' => 'pro',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $slugs = collect(
        $this->getJson('/api/v1/auth/login-context/tenants')->json('data.tenants'),
    )->pluck('slug')->all();

    expect($slugs)->not->toContain('bar-inactivo');
});

it('3. lista branches activas por tenant', function () {
    $response = $this->getJson('/api/v1/auth/login-context/branches?tenant_slug=casa-demo')
        ->assertOk();

    $codes = collect($response->json('data.branches'))->pluck('code')->all();

    expect($codes)->toContain('CENTRO')
        ->and($response->json('data.branches.0'))->toHaveKeys(['id', 'name', 'code']);
});

it('4. no lista branches de otro tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro Bar',
        'slug' => 'otro-bar',
        'status' => 'active',
        'plan_name' => 'pro',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal Ajena',
        'code' => 'AJENA',
        'status' => 'active',
    ]);

    $codes = collect(
        $this->getJson('/api/v1/auth/login-context/branches?tenant_slug=casa-demo')->json('data.branches'),
    )->pluck('code')->all();

    expect($codes)->toContain('CENTRO')
        ->and($codes)->not->toContain('AJENA');
});

it('5. tenant inexistente devuelve 404', function () {
    $this->getJson('/api/v1/auth/login-context/branches?tenant_slug=no-existe')
        ->assertNotFound();
});

it('6. no lista branches inactivas', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Cerrada',
        'code' => 'CERRADA',
        'status' => 'inactive',
    ]);

    $codes = collect(
        $this->getJson('/api/v1/auth/login-context/branches?tenant_slug=casa-demo')->json('data.branches'),
    )->pluck('code')->all();

    expect($codes)->not->toContain('CERRADA');
});

it('7. no lista tenants con suscripcion vencida', function () {
    TenantModel::query()->create([
        'name' => 'Bar Expirado',
        'slug' => 'bar-expirado',
        'status' => 'active',
        'plan_name' => 'pro',
        'subscription_starts_at' => now()->subYear(),
        'subscription_ends_at' => now()->subDay(),
    ]);

    $slugs = collect(
        $this->getJson('/api/v1/auth/login-context/tenants')->json('data.tenants'),
    )->pluck('slug')->all();

    expect($slugs)->not->toContain('bar-expirado');
});

it('8. login-context tenants usa una sola query minima sin relaciones', function () {
    \Illuminate\Support\Facades\DB::enableQueryLog();

    $response = $this->getJson('/api/v1/auth/login-context/tenants')
        ->assertOk();

    $queries = \Illuminate\Support\Facades\DB::getQueryLog();

    expect($queries)->toHaveCount(1)
        ->and(strtolower($queries[0]['query']))->toContain('status')
        ->and($response->json('data.tenants.0'))->toHaveKeys(['id', 'name', 'slug'])
        ->and($response->json('data.tenants.0'))->not->toHaveKey('plan_name');
});
