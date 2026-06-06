<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
});

it('bootstraps operational data for empty branch catalog', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->delete();
    \App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel::query()->delete();

    $this->postJson('/api/v1/settings/bootstrap-operational', [], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.skipped', false);

    expect(\App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->count())->toBeGreaterThan(0);
});

it('lists audit logs after charging an order', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    $this->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $this->getJson('/api/v1/audit-logs', nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.logs.0.action', 'sale.charged');
});

it('exports shift summary as csv', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenShift($token);

    $shiftId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()->value('id');

    $response = $this->get("/api/v1/shifts/{$shiftId}/export.csv", nightposOperationalHeaders($token));

    $response->assertOk();
    expect((string) $response->headers->get('content-type'))->toContain('text/csv');
});
