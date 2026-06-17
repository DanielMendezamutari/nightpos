<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
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

function autoRotationCreateOrder(string $token): int
{
    $response = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa rotación',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($token))->assertCreated();

    return (int) $response->json('data.order.id');
}

it('auto shift rotates to a new shift on the next business window', function () {
    // Noche del 14: turno auto se crea
    Carbon::setTestNow('2026-06-14 22:00:00');
    $waiter = nightposLoginPin('5678');

    $order1 = autoRotationCreateOrder($waiter);
    $shift1 = (int) OrderModel::query()->where('id', $order1)->value('official_shift_id');

    expect(OfficialShiftModel::query()->find($shift1)->business_date->format('Y-m-d'))->toBe('2026-06-14');

    // Día siguiente, ventana distinta (noche del 15)
    Carbon::setTestNow('2026-06-15 22:00:00');
    $waiter = nightposLoginPin('5678');

    $order2 = autoRotationCreateOrder($waiter);
    $shift2 = (int) OrderModel::query()->where('id', $order2)->value('official_shift_id');

    // Debe ser un turno NUEVO con la fecha de negocio del 15
    expect($shift2)->not->toBe($shift1)
        ->and(OfficialShiftModel::query()->find($shift2)->business_date->format('Y-m-d'))->toBe('2026-06-15');

    // El turno viejo quedó cerrado; solo el nuevo está abierto
    expect(OfficialShiftModel::query()->find($shift1)->status)->toBe('CLOSED')
        ->and(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(1)
        ->and((int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id'))->toBe($shift2);
});

it('auto shift is reused within the same business window', function () {
    Carbon::setTestNow('2026-06-14 14:00:00');
    $waiter = nightposLoginPin('5678');

    $order1 = autoRotationCreateOrder($waiter);
    $shift1 = (int) OrderModel::query()->where('id', $order1)->value('official_shift_id');

    // Mismo día, misma ventana DAY (3 horas después)
    Carbon::setTestNow('2026-06-14 17:00:00');
    $waiter = nightposLoginPin('5678');

    $order2 = autoRotationCreateOrder($waiter);
    $shift2 = (int) OrderModel::query()->where('id', $order2)->value('official_shift_id');

    expect($shift2)->toBe($shift1)
        ->and(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(1);
});

it('new cash session is associated to the rotated new shift not the expired one', function () {
    // Turno auto noche del 14
    Carbon::setTestNow('2026-06-14 22:00:00');
    $cashier = nightposLoginPin('1234');
    test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($cashier))
        ->assertCreated();
    $oldShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    // Cierra caja antes de rotar (sin ventas ni liquidaciones)
    $cashier = nightposLoginPin('1234');
    \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()
        ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);
    test()->postJson('/api/v1/cash/session/close', ['declared_closing_amount' => 0], nightposOperationalHeaders($cashier))
        ->assertOk();

    // Día siguiente: abre caja nueva → debe rotar turno
    Carbon::setTestNow('2026-06-15 22:00:00');
    $cashier = nightposLoginPin('1234');
    $session = test()->postJson('/api/v1/cash/session/open', ['opening_amount' => 0], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->json('data.session');

    $newShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    expect($newShiftId)->not->toBe($oldShiftId)
        ->and((int) $session['official_shift_id'])->toBe($newShiftId)
        ->and(OfficialShiftModel::query()->find($oldShiftId)->status)->toBe('CLOSED')
        ->and(OfficialShiftModel::query()->find($oldShiftId)->notes)->toContain('Cerrado automáticamente');
});

it('night shift crossing midnight is reused not rotated', function () {
    // Noche del 14: turno auto 21:00 14 → 09:00 15
    Carbon::setTestNow('2026-06-14 23:30:00');
    $waiter = nightposLoginPin('5678');
    $order1 = autoRotationCreateOrder($waiter);
    $shift1 = (int) OrderModel::query()->where('id', $order1)->value('official_shift_id');

    // Pasada la medianoche pero aún dentro de la ventana (02:00 del 15)
    Carbon::setTestNow('2026-06-15 02:00:00');
    $waiter = nightposLoginPin('5678');
    $order2 = autoRotationCreateOrder($waiter);
    $shift2 = (int) OrderModel::query()->where('id', $order2)->value('official_shift_id');

    expect($shift2)->toBe($shift1)
        ->and(OfficialShiftModel::query()->where('status', 'OPEN')->count())->toBe(1);
});

it('manually opened shift is never auto closed by rotation', function () {
    Carbon::setTestNow('2026-06-14 14:00:00');
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    // Turno manual DAY del 14
    test()->postJson('/api/v1/shifts/open', [
        'shift_type' => 'DAY',
        'business_date' => '2026-06-14',
    ], nightposOperationalHeaders($admin))->assertCreated();

    $manualShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    // Día siguiente, ventana distinta: el turno manual NO debe rotar
    Carbon::setTestNow('2026-06-15 22:00:00');
    $waiter = nightposLoginPin('5678');

    $orderId = autoRotationCreateOrder($waiter);
    $orderShiftId = (int) OrderModel::query()->where('id', $orderId)->value('official_shift_id');

    expect($orderShiftId)->toBe($manualShiftId)
        ->and(OfficialShiftModel::query()->find($manualShiftId)->status)->toBe('OPEN');
});
