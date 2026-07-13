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

it('allows admin to resolve duplicated open shifts keeping the one with open cash session', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashierToken = nightposLoginPin('1234');

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $keeperShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $keeper = OfficialShiftModel::query()->findOrFail($keeperShiftId);

    $duplicate = OfficialShiftModel::query()->create([
        'tenant_id' => (int) $keeper->tenant_id,
        'branch_id' => (int) $keeper->branch_id,
        'name' => 'Turno Duplicado',
        'shift_type' => 'NIGHT',
        'business_date' => now()->format('Y-m-d'),
        'starts_at' => now()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addHours(12)->format('Y-m-d H:i:s'),
        'status' => 'OPEN',
        'opened_by_user_id' => $keeper->opened_by_user_id,
        'opened_at' => now(),
        'notes' => 'duplicado test',
    ]);

    $this->postJson('/api/v1/shifts/resolve-open-conflicts', [], nightposOperationalHeaders($adminToken))
        ->assertOk()
        ->assertJsonPath('data.kept_shift_id', $keeperShiftId)
        ->assertJsonPath('data.closed_count', 1);

    expect(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(1)
        ->and((int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id'))->toBe($keeperShiftId)
        ->and((string) OfficialShiftModel::query()->findOrFail($duplicate->id)->status)->toBe('CLOSED');
});

it('requires explicit keep_shift_id when more than one open shift has open cash session', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashierToken = nightposLoginPin('1234');

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $firstShift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    $secondShift = OfficialShiftModel::query()->create([
        'tenant_id' => (int) $firstShift->tenant_id,
        'branch_id' => (int) $firstShift->branch_id,
        'name' => 'Turno Duplicado 2',
        'shift_type' => 'NIGHT',
        'business_date' => now()->format('Y-m-d'),
        'starts_at' => now()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addHours(12)->format('Y-m-d H:i:s'),
        'status' => 'OPEN',
        'opened_by_user_id' => (int) $firstShift->opened_by_user_id,
        'opened_at' => now(),
        'notes' => 'duplicado con caja',
    ]);

    CashSessionModel::query()->create([
        'tenant_id' => (int) $firstShift->tenant_id,
        'branch_id' => (int) $firstShift->branch_id,
        'official_shift_id' => (int) $secondShift->id,
        'opened_by_user_id' => (int) $firstShift->opened_by_user_id,
        'status' => 'OPEN',
        'opening_amount' => '50.00',
        'opened_at' => now(),
    ]);

    $this->postJson('/api/v1/shifts/resolve-open-conflicts', [], nightposOperationalHeaders($adminToken))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Hay mas de un turno abierto con caja activa. Seleccione manualmente el turno a conservar.');
});

it('returns 403 for close-check when user lacks shifts.close permission', function () {
    $token = nightposLoginPin('1234');

    $this->getJson('/api/v1/shifts/current/close-check', nightposOperationalHeaders($token))
        ->assertStatus(403);
});

it('returns clear 422 for close-check when superadmin has no operational context', function () {
    $token = nightposLoginPassword('superadmin', 'SuperAdmin123!', null);

    $this->getJson('/api/v1/shifts/current/close-check', nightposOperationalHeaders($token, null))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Contexto operativo incompleto.');
});

it('returns close-check with deterministic selected shift and conflict metadata when multiple opens exist', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposOpenShift($token, 'DAY', '2026-06-11');
    $firstShift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    $secondShift = OfficialShiftModel::query()->create([
        'tenant_id' => (int) $firstShift->tenant_id,
        'branch_id' => (int) $firstShift->branch_id,
        'name' => 'Turno Duplicado Metadata',
        'shift_type' => 'NIGHT',
        'business_date' => '2026-06-11',
        'starts_at' => now()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addHours(12)->format('Y-m-d H:i:s'),
        'status' => 'OPEN',
        'opened_by_user_id' => (int) $firstShift->opened_by_user_id,
        'opened_at' => now()->addSecond(),
    ]);

    $response = $this->getJson('/api/v1/shifts/current/close-check', nightposOperationalHeaders($token));

    $response->assertOk()
        ->assertJsonPath('data.shift_context.has_open_shift_conflict', true)
        ->assertJsonPath('data.shift_context.open_shift_ids.0', (int) $secondShift->id)
        ->assertJsonPath('data.shift_id', (int) $secondShift->id);
});

it('adds blocker when open cash sessions point to historical closed shifts', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposOpenShift($token, 'DAY', '2026-06-12');
    $openShift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    $historical = OfficialShiftModel::query()->create([
        'tenant_id' => (int) $openShift->tenant_id,
        'branch_id' => (int) $openShift->branch_id,
        'name' => 'Turno Historico Cerrado',
        'shift_type' => 'DAY',
        'business_date' => '2026-06-10',
        'starts_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
        'ends_at' => now()->subDays(2)->addHours(12)->format('Y-m-d H:i:s'),
        'status' => 'CLOSED',
        'opened_by_user_id' => (int) $openShift->opened_by_user_id,
        'opened_at' => now()->subDays(2),
        'closed_by_user_id' => (int) $openShift->opened_by_user_id,
        'closed_at' => now()->subDays(2)->addHours(10),
    ]);

    CashSessionModel::query()->create([
        'tenant_id' => (int) $openShift->tenant_id,
        'branch_id' => (int) $openShift->branch_id,
        'official_shift_id' => (int) $historical->id,
        'opened_by_user_id' => (int) $openShift->opened_by_user_id,
        'status' => 'OPEN',
        'opening_amount' => '25.00',
        'opened_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/shifts/current/close-check', nightposOperationalHeaders($token));

    $response->assertOk()
        ->assertJsonPath('data.shift_context.open_cash_sessions_on_historical_shifts.0.official_shift_id', (int) $historical->id);

    expect(collect($response->json('data.blockers'))->pluck('code')->all())
        ->toContain('open_cash_sessions_on_historical_shifts');
});

it('does not auto-close shifts when resolver receives only historical open-cash references', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposOpenShift($adminToken, 'DAY', '2026-06-13');
    $openShift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    $historical = OfficialShiftModel::query()->create([
        'tenant_id' => (int) $openShift->tenant_id,
        'branch_id' => (int) $openShift->branch_id,
        'name' => 'Turno Historico 2',
        'shift_type' => 'DAY',
        'business_date' => '2026-06-09',
        'starts_at' => now()->subDays(4)->format('Y-m-d H:i:s'),
        'ends_at' => now()->subDays(4)->addHours(12)->format('Y-m-d H:i:s'),
        'status' => 'CLOSED',
        'opened_by_user_id' => (int) $openShift->opened_by_user_id,
        'opened_at' => now()->subDays(4),
        'closed_by_user_id' => (int) $openShift->opened_by_user_id,
        'closed_at' => now()->subDays(4)->addHours(8),
    ]);

    CashSessionModel::query()->create([
        'tenant_id' => (int) $openShift->tenant_id,
        'branch_id' => (int) $openShift->branch_id,
        'official_shift_id' => (int) $historical->id,
        'opened_by_user_id' => (int) $openShift->opened_by_user_id,
        'status' => 'OPEN',
        'opening_amount' => '30.00',
        'opened_at' => now(),
    ]);

    $openBefore = OfficialShiftModel::query()->where('status', 'OPEN')->pluck('id')->all();
    $cashBefore = CashSessionModel::query()->where('status', 'OPEN')->count();

    $this->postJson('/api/v1/shifts/resolve-open-conflicts', [], nightposOperationalHeaders($adminToken))
        ->assertOk();

    $openAfter = OfficialShiftModel::query()->where('status', 'OPEN')->pluck('id')->all();
    $cashAfter = CashSessionModel::query()->where('status', 'OPEN')->count();

    expect($openAfter)->toBe($openBefore)
        ->and($cashAfter)->toBe($cashBefore);
});
