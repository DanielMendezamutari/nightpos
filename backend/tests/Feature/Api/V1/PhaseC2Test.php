<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phaseC2AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function phaseC2CashierToken(): string
{
    return nightposLoginPin('1234');
}

function phaseC2FetchConsole(?string $token = null, ?string $branchCode = 'CENTRO'): \Illuminate\Testing\TestResponse
{
    return test()->getJson('/api/v1/shift-console/current', nightposOperationalHeaders(
        $token ?? phaseC2AdminToken(),
        $branchCode,
    ));
}

it('returns current shift on shift console endpoint', function () {
    nightposEnsureShiftOpen();

    $response = phaseC2FetchConsole()->assertOk();

    expect($response->json('data.shift'))->not->toBeNull()
        ->and($response->json('data.shift.status'))->toBe('OPEN');
});

it('returns cash session state on shift console', function () {
    nightposEnsureShiftOpen();
    $token = phaseC2CashierToken();

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 150,
    ], nightposOperationalHeaders($token))->assertCreated();

    $response = phaseC2FetchConsole($token)->assertOk();

    expect($response->json('data.cash_session.status'))->toBe('OPEN')
        ->and($response->json('data.cash_totals.opening_amount'))->toBe('150.00');
});

it('returns open orders summary on shift console', function () {
    nightposEnsureShiftOpen();
    nightposCreateOrderWithItem(phaseC2CashierToken());

    $response = phaseC2FetchConsole(phaseC2CashierToken())->assertOk();

    expect($response->json('data.orders_summary.counts.open'))->toBeGreaterThan(0)
        ->and($response->json('data.cards.open_orders'))->toBeGreaterThan(0);
});

it('returns rooms summary by status on shift console', function () {
    $response = phaseC2FetchConsole()->assertOk();

    expect($response->json('data.rooms_summary.available'))->toBeGreaterThan(0)
        ->and($response->json('data.rooms_summary.cleaning'))->toBeGreaterThan(0)
        ->and($response->json('data.cards.cleaning_rooms'))->toBeGreaterThan(0);
});

it('returns shift services summary on shift console', function () {
    nightposEnsureShiftOpen();

    $response = phaseC2FetchConsole()->assertOk();

    expect($response->json('data.services_summary'))->toHaveKeys([
        'bracelets_count',
        'active_room_services_count',
        'shows_count',
    ]);
});

it('returns operational alerts on shift console', function () {
    $response = phaseC2FetchConsole()->assertOk();
    $types = collect($response->json('data.alerts'))->pluck('type');

    expect($types)->toContain('rooms_cleaning');
});

it('respects tenant and branch on shift console', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Sur',
        'code' => 'SUR',
        'status' => 'active',
    ]);

    nightposEnsureShiftOpen();
    nightposCreateOrderWithItem(phaseC2CashierToken());

    $centroOrders = (int) OrderModel::query()->where('branch_id', 1)->count();
    expect($centroOrders)->toBeGreaterThan(0);

    phaseC2FetchConsole(phaseC2CashierToken(), 'SUR')->assertForbidden();
});

it('denies shift console without permission', function () {
    $token = nightposLoginPin('5678');

    phaseC2FetchConsole($token)->assertForbidden();
});
