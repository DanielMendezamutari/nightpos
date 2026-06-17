<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function cashReasonAdminHeaders(): array
{
    return nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!'));
}

function cashReasonCashierHeaders(): array
{
    return nightposOperationalHeaders(nightposLoginPin('1234'));
}

it('admin lists cash movement reasons', function () {
    $response = test()->getJson('/api/v1/cash-movement-reasons', cashReasonAdminHeaders())->assertOk();

    expect($response->json('data.cash_movement_reasons'))->not->toBeEmpty();
});

it('admin creates income reason', function () {
    $response = test()->postJson('/api/v1/cash-movement-reasons', [
        'type' => 'INCOME',
        'name' => 'Propina VIP',
    ], cashReasonAdminHeaders())->assertCreated();

    expect($response->json('data.cash_movement_reason.type'))->toBe('INCOME');
});

it('admin creates expense reason', function () {
    $response = test()->postJson('/api/v1/cash-movement-reasons', [
        'type' => 'EXPENSE',
        'name' => 'Gasto operativo',
    ], cashReasonAdminHeaders())->assertCreated();

    expect($response->json('data.cash_movement_reason.type'))->toBe('EXPENSE');
});

it('cashier lists active reasons via cash endpoint', function () {
    $response = test()->getJson('/api/v1/cash/movement-reasons?active_only=1', cashReasonCashierHeaders())->assertOk();

    expect($response->json('data.cash_movement_reasons'))->not->toBeEmpty();
});

it('cashier cannot create reasons without manage permission', function () {
    test()->postJson('/api/v1/cash-movement-reasons', [
        'type' => 'EXPENSE',
        'name' => 'Motivo hack cajera',
    ], cashReasonCashierHeaders())->assertForbidden();
});

it('income movement accepts income and both reasons only', function () {
    nightposEnsureShiftOpen();
    $token = nightposLoginPin('1234');

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], cashReasonCashierHeaders())->assertCreated();

    $income = CashMovementReasonModel::query()->where('type', 'INCOME')->first();
    $expense = CashMovementReasonModel::query()->where('type', 'EXPENSE')->first();
    $both = CashMovementReasonModel::query()->firstOrCreate(
        ['tenant_id' => $income->tenant_id, 'type' => 'BOTH', 'name' => 'Corrección test'],
        ['status' => 'active'],
    );

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 10,
        'cash_movement_reason_id' => $income->id,
        'payment_method' => 'CASH',
    ], cashReasonCashierHeaders())->assertCreated();

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 5,
        'cash_movement_reason_id' => $both->id,
        'payment_method' => 'CASH',
    ], cashReasonCashierHeaders())->assertCreated();

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 5,
        'cash_movement_reason_id' => $expense->id,
        'payment_method' => 'CASH',
    ], cashReasonCashierHeaders())->assertStatus(422);
});

it('expense movement accepts expense and both reasons only', function () {
    nightposEnsureShiftOpen();

    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], cashReasonCashierHeaders())->assertCreated();

    $expense = CashMovementReasonModel::query()->where('type', 'EXPENSE')->where('name', 'Pago taxi')->first();
    $income = CashMovementReasonModel::query()->where('type', 'INCOME')->first();
    $both = CashMovementReasonModel::query()->where('type', 'BOTH')->first();

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 10,
        'cash_movement_reason_id' => $expense->id,
        'payment_method' => 'CASH',
    ], cashReasonCashierHeaders())->assertCreated();

    if ($both) {
        test()->postJson('/api/v1/cash/movements', [
            'movement_type' => 'EXPENSE',
            'amount' => 5,
            'cash_movement_reason_id' => $both->id,
            'payment_method' => 'CASH',
        ], cashReasonCashierHeaders())->assertCreated();
    }

    test()->postJson('/api/v1/cash/movements', [
        'movement_type' => 'EXPENSE',
        'amount' => 5,
        'cash_movement_reason_id' => $income->id,
        'payment_method' => 'CASH',
    ], cashReasonCashierHeaders())->assertStatus(422);
});

it('inactive reason is excluded from active cash listing', function () {
    $reason = CashMovementReasonModel::query()->where('type', 'EXPENSE')->first();
    $reason->update(['status' => 'inactive']);

    $response = test()->getJson('/api/v1/cash/movement-reasons?active_only=1', cashReasonCashierHeaders())->assertOk();

    $ids = collect($response->json('data.cash_movement_reasons'))->pluck('id')->all();

    expect($ids)->not->toContain($reason->id);
});

it('isolates cash movement reasons by tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otro Tenant Motivos',
        'slug' => 'otro-motivos',
        'status' => 'active',
    ]);

    CashMovementReasonModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'type' => 'EXPENSE',
        'name' => 'Motivo aislado',
        'status' => 'active',
    ]);

    $response = test()->getJson('/api/v1/cash-movement-reasons', cashReasonAdminHeaders())->assertOk();

    $names = collect($response->json('data.cash_movement_reasons'))->pluck('name')->all();

    expect($names)->not->toContain('Motivo aislado');
});

it('isolates branch scoped reasons from other branches', function () {
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $adminId = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'admin.demo')
        ->value('id');

    $norte = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Norte Motivos',
        'code' => 'NORTE-M',
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel::query()->firstOrCreate([
        'user_id' => $adminId,
        'branch_id' => $norte->id,
    ], [
        'tenant_id' => $tenantId,
    ]);

    CashMovementReasonModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $norte->id,
        'type' => 'EXPENSE',
        'name' => 'Solo Norte',
        'status' => 'active',
    ]);

    $centroResponse = test()->getJson('/api/v1/cash-movement-reasons', cashReasonAdminHeaders())->assertOk();
    $centroNames = collect($centroResponse->json('data.cash_movement_reasons'))->pluck('name')->all();

    expect($centroNames)->not->toContain('Solo Norte');

    $norteResponse = test()->getJson('/api/v1/cash-movement-reasons', nightposOperationalHeaders(
        nightposLoginPassword('admin.demo', 'AdminDemo123!'),
        'NORTE-M',
    ))->assertOk();

    $norteNames = collect($norteResponse->json('data.cash_movement_reasons'))->pluck('name')->all();

    expect($norteNames)->toContain('Solo Norte');
});
