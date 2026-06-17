<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);

    OfficialShiftModel::query()->where('status', 'OPEN')->update([
        'status' => 'CLOSED',
        'closed_at' => now(),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('auto creates shift when opening cash without manual shift', function () {
    Carbon::setTestNow('2026-06-02 14:30:00');

    expect(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(0);

    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 50,
    ], nightposOperationalHeaders($token))->assertCreated();

    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->first();

    expect($shift)->not->toBeNull()
        ->and($shift->shift_type)->toBe('DAY')
        ->and($shift->business_date->format('Y-m-d'))->toBe('2026-06-02')
        ->and($shift->notes)->toContain('automáticamente')
        ->and(CashSessionModel::query()->where('status', 'OPEN')->value('official_shift_id'))->toBe($shift->id);
});

it('auto creates night shift after 21:00', function () {
    Carbon::setTestNow('2026-06-02 22:15:00');

    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($token))
        ->assertCreated();

    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->first();

    expect($shift->shift_type)->toBe('NIGHT')
        ->and($shift->business_date->format('Y-m-d'))->toBe('2026-06-02')
        ->and($shift->starts_at->format('Y-m-d H:i:s'))->toBe('2026-06-02 21:00:00')
        ->and($shift->ends_at->format('Y-m-d H:i:s'))->toBe('2026-06-03 09:00:00');
});

it('auto creates night shift before 09:00 with previous business date', function () {
    Carbon::setTestNow('2026-06-03 02:00:00');

    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/orders', [
        'table_label' => 'Madrugada',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))
        ->assertCreated();

    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->first();

    expect($shift->shift_type)->toBe('NIGHT')
        ->and($shift->business_date->format('Y-m-d'))->toBe('2026-06-02');
});

it('auto creates shift when creating order without manual shift', function () {
    Carbon::setTestNow('2026-06-02 11:00:00');

    $token = nightposLoginPin('5678');

    $response = test()->postJson('/api/v1/orders', ['table_label' => 'Mesa Auto'], nightposOperationalHeaders($token))
        ->assertCreated();

    $orderId = (int) $response->json('data.order.id');
    $order = OrderModel::query()->findOrFail($orderId);

    expect($order->official_shift_id)->not->toBeNull();
    expect(OfficialShiftModel::query()->find($order->official_shift_id)->notes)->toContain('automáticamente');
});

it('auto creates shift when charging after previous shift was closed', function () {
    Carbon::setTestNow('2026-06-02 15:00:00');

    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $cashier = nightposLoginPin('1234');
    $waiter = nightposLoginPin('5678');

    $orderId = nightposCreateOrderWithItem($waiter)['order_id'];
    $firstShiftId = (int) OrderModel::query()->where('id', $orderId)->value('official_shift_id');

    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($cashier));

    OrderModel::query()
        ->where('id', '!=', $orderId)
        ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    nightposPrepareCashSessionClose($cashier, $admin);
    test()->postJson('/api/v1/cash/session/close', ['declared_closing_amount' => 50], nightposOperationalHeaders($cashier))->assertOk();
    test()->postJson("/api/v1/shifts/{$firstShiftId}/close", ['counted_cash' => 50], nightposOperationalHeaders($admin))->assertOk();

    expect(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(0);

    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $newShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    expect($newShiftId)->not->toBe($firstShiftId);

    $newOrderId = nightposCreateOrderWithItem($waiter)['order_id'];

    test()->postJson("/api/v1/orders/{$newOrderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashier))->assertCreated();

    expect(SaleModel::query()->where('order_id', $newOrderId)->value('official_shift_id'))->toBe($newShiftId);
    expect(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(1);
});

it('allows cashier without shifts.open to operate and auto create shift', function () {
    Carbon::setTestNow('2026-06-02 10:00:00');

    $role = RoleModel::query()->where('slug', 'cashier')->first();
    $role->permissions()->detach(
        PermissionModel::query()->where('slug', 'shifts.open')->pluck('id')
    );

    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 10], nightposOperationalHeaders($token))
        ->assertCreated();

    test()->postJson('/api/v1/shifts/open', [
        'shift_type' => 'DAY',
        'business_date' => '2026-06-02',
    ], nightposOperationalHeaders($token))
        ->assertForbidden();
});

it('still requires shifts.open for manual shift open', function () {
    Carbon::setTestNow('2026-06-02 10:00:00');

    $waiter = nightposLoginPin('5678');

    test()->postJson('/api/v1/shifts/open', [
        'shift_type' => 'DAY',
        'business_date' => '2026-06-02',
    ], nightposOperationalHeaders($waiter))
        ->assertForbidden();
});

it('allows admin manual shift open and close', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/shifts/open', [
        'shift_type' => 'DAY',
        'business_date' => date('Y-m-d'),
    ], nightposOperationalHeaders($admin))->assertCreated();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    test()->postJson("/api/v1/shifts/{$shiftId}/close", ['counted_cash' => 0], nightposOperationalHeaders($admin))
        ->assertOk();
});

it('does not expose shifts from another tenant', function () {
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposOpenShift($admin);

    $otherTenant = TenantModel::query()->create([
        'name' => 'Externa',
        'slug' => 'externa-shift-2',
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

    test()->getJson("/api/v1/shifts/{$foreignShift->id}", nightposOperationalHeaders($admin))
        ->assertNotFound();
});
