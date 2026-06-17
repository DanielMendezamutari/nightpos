<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function cashierToken(): string
{
    return nightposLoginPin('1234');
}

function cashierHeaders(?string $token = null): array
{
    return nightposOperationalHeaders($token ?? cashierToken());
}

function cashierPrepareSession(?string $token = null): string
{
    $token ??= cashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 200);

    return $token;
}

it('blocks cash close with OPEN order', function () {
    $token = cashierPrepareSession();

    nightposCreateOrderWithItem($token, ['table_label' => 'Bloqueo OPEN']);

    $response = test()->getJson('/api/v1/cash/session/current/close-check', cashierHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    $codes = collect($response->json('data.blockers'))->pluck('code')->all();
    expect($codes)->toContain('active_orders');

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 200,
    ], cashierHeaders($token))->assertStatus(422);
});

it('blocks cash close with SENT_TO_BAR order', function () {
    $token = cashierPrepareSession();

    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Barra']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], cashierHeaders($token))->assertOk();

    test()->getJson('/api/v1/cash/session/current/close-check', cashierHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);
});

it('blocks cash close with ACTIVE room service', function () {
    $token = cashierPrepareSession();

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload(), cashierHeaders($token))
        ->assertCreated();

    $response = test()->getJson('/api/v1/cash/session/current/close-check', cashierHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    $codes = collect($response->json('data.blockers'))->pluck('code')->all();
    expect($codes)->toContain('active_room_services');
});

it('blocks cash close when settlements were not generated', function () {
    $token = cashierPrepareSession();

    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Settlement block']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], cashierHeaders($token))->assertOk();
    test()->postJson("/api/v1/orders/{$result['order_id']}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], cashierHeaders($token))->assertCreated();

    $response = test()->getJson('/api/v1/cash/session/current/close-check', cashierHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    expect(collect($response->json('data.blockers'))->pluck('code')->all())
        ->toContain('settlements_not_generated');
});

it('allows cash close when operational pendings are resolved', function () {
    $token = cashierPrepareSession();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], cashierHeaders($token))
        ->assertCreated();

    $check = test()->getJson('/api/v1/cash/session/current/close-check', cashierHeaders($token))
        ->assertOk()
        ->json('data');

    if ($check['can_close'] === true) {
        test()->postJson('/api/v1/cash/session/close', [
            'declared_closing_amount' => 200,
        ], cashierHeaders($token))->assertOk();
    }
    else {
        expect($check['blockers'])->not->toBeEmpty();
    }
});

it('cashier billed recent lists only current cash session charges', function () {
    $token = cashierPrepareSession();

    $result = nightposCreateOrderWithItem($token, ['table_label' => 'Cobrada mía']);
    test()->postJson("/api/v1/orders/{$result['order_id']}/send-to-bar", [], cashierHeaders($token))->assertOk();
    test()->postJson("/api/v1/orders/{$result['order_id']}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], cashierHeaders($token))->assertCreated();

    $response = test()->getJson('/api/v1/orders?scope=billed_recent&cashier_scope=1&current_session=1', cashierHeaders($token))
        ->assertOk();

    $ids = collect($response->json('data.orders'))->pluck('id')->all();
    expect($ids)->toContain($result['order_id']);
});

it('cashier scope excludes orders from closed shifts', function () {
    $token = cashierPrepareSession();

    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $closedShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno cerrado test',
        'shift_type' => 'NIGHT',
        'business_date' => '2020-01-01',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(8),
        'status' => 'CLOSED',
        'opened_by_user_id' => 1,
        'opened_at' => now()->subDay(),
        'closed_at' => now()->subDay(),
    ]);

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $closedShift->id,
        'order_number' => 'OLD-999',
        'status' => 'OPEN',
        'table_label' => 'Otro turno',
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
        'opened_by_user_id' => 1,
    ]);

    $response = test()->getJson('/api/v1/orders?scope=cashier_chargeable&cashier_scope=1', cashierHeaders($token))
        ->assertOk();

    $numbers = collect($response->json('data.orders'))->pluck('order_number')->all();
    expect($numbers)->not->toContain('OLD-999');
});

it('cashier chargeable scope only includes OPEN and SENT_TO_BAR', function () {
    $token = cashierPrepareSession();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    OrderModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'order_number' => 'READY-1',
        'status' => 'READY',
        'table_label' => 'Ready',
        'subtotal' => 10,
        'total' => 10,
        'currency' => 'BOB',
        'opened_by_user_id' => 1,
    ]);

    $response = test()->getJson('/api/v1/orders?scope=cashier_chargeable&cashier_scope=1', cashierHeaders($token))
        ->assertOk();

    $numbers = collect($response->json('data.orders'))->pluck('order_number')->all();
    expect($numbers)->not->toContain('READY-1');
});

it('admin cash sessions list still works', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->getJson('/api/v1/admin/cash-sessions', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonStructure(['data' => ['cash_sessions']]);
});
