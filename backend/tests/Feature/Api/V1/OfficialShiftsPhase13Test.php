<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
});

it('opens day shift with correct window', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $response = $this->postJson('/api/v1/shifts/open', [
        'shift_type' => 'DAY',
        'business_date' => '2026-06-02',
        'notes' => 'Apertura día',
    ], nightposOperationalHeaders($token));

    $response->assertCreated()
        ->assertJsonPath('data.shift.shift_type', 'DAY')
        ->assertJsonPath('data.shift.status', 'OPEN')
        ->assertJsonPath('data.shift.starts_at', '2026-06-02 09:00:00')
        ->assertJsonPath('data.shift.ends_at', '2026-06-02 21:00:00');
});

it('opens night shift ending next morning', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposOpenShift($token, 'DAY', '2026-06-01');

    $this->postJson('/api/v1/shifts/'.OfficialShiftModel::query()->value('id').'/close', [
        'counted_cash' => 0,
    ], nightposOperationalHeaders($token))->assertOk();

    $response = $this->postJson('/api/v1/shifts/open', [
        'shift_type' => 'NIGHT',
        'business_date' => '2026-06-02',
    ], nightposOperationalHeaders($token));

    $response->assertCreated()
        ->assertJsonPath('data.shift.shift_type', 'NIGHT')
        ->assertJsonPath('data.shift.starts_at', '2026-06-02 21:00:00')
        ->assertJsonPath('data.shift.ends_at', '2026-06-03 09:00:00');
});

it('denies second open shift on same branch', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenShift($token);

    $this->postJson('/api/v1/shifts/open', [
        'shift_type' => 'NIGHT',
        'business_date' => date('Y-m-d'),
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Ya hay un turno oficial abierto en esta sucursal.');
});

it('opens cash session with auto shift when none was opened manually', function () {
    $token = nightposLoginPin('1234');

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($token))
        ->assertCreated();

    expect(OfficialShiftModel::query()->where('status', 'OPEN')->exists())->toBeTrue();
});

it('associates sale order and cash session with official shift', function () {
    $token = nightposLoginPin('1234');
    $orderId = nightposSeedChargeableOrder($token);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $this->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    expect(OrderModel::query()->find($orderId)->official_shift_id)->toBe($shiftId);
    expect(SaleModel::query()->where('order_id', $orderId)->value('official_shift_id'))->toBe($shiftId);
    expect(CashSessionModel::query()->where('status', 'OPEN')->value('official_shift_id'))->toBe($shiftId);
});

it('closes shift and generates closure summary', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $orderId = nightposSeedChargeableOrder($token);

    $this->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($token))->assertCreated();

    nightposPrepareCashSessionClose($token);

    $this->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 150,
    ], nightposOperationalHeaders($token))->assertOk();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $this->postJson("/api/v1/shifts/{$shiftId}/close", [
        'counted_cash' => 150,
        'notes' => 'Cierre OK',
    ], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.closure.total_sales', '50.00')
        ->assertJsonPath('data.closure.total_cash', '50.00')
        ->assertJsonPath('data.shift.status', 'CLOSED');
});

it('does not expose shift from another tenant', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenShift($token);

    $otherTenant = TenantModel::query()->create([
        'name' => 'Externa',
        'slug' => 'externa-shift',
        'status' => 'active',
        'plan_name' => 'basic',
    ]);

    $foreignShift = OfficialShiftModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => 1,
        'name' => 'Turno Ajeno',
        'shift_type' => 'DAY',
        'business_date' => '2026-06-02',
        'starts_at' => now(),
        'ends_at' => now()->addHours(12),
        'status' => 'OPEN',
        'opened_by_user_id' => 1,
        'opened_at' => now(),
    ]);

    $this->getJson("/api/v1/shifts/{$foreignShift->id}", nightposOperationalHeaders($token))
        ->assertNotFound();
});
