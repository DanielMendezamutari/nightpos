<?php

declare(strict_types=1);

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function cmcpRegisterDevice(): void
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'Caja Print '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token))->assertCreated();
}

function cmcpCashierToken(): string
{
    return nightposLoginPin('1234');
}

function cmcpIncomeReasonId(): int
{
    return (int) CashMovementReasonModel::query()
        ->where('name', 'Otro ingreso')
        ->value('id');
}

function cmcpExpenseReasonId(): int
{
    return (int) CashMovementReasonModel::query()
        ->where('name', 'Otro egreso')
        ->value('id');
}

function cmcpRegisterMovement(string $type = 'INCOME', ?string $token = null, array $overrides = []): \Illuminate\Testing\TestResponse
{
    $token ??= cmcpCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 200, false);

    $payload = array_merge([
        'movement_type' => $type,
        'amount' => 75,
        'cash_movement_reason_id' => $type === 'INCOME' ? cmcpIncomeReasonId() : cmcpExpenseReasonId(),
        'payment_method' => 'CASH',
        'notes' => 'Nota operativa',
    ], $overrides);

    return test()->postJson('/api/v1/cash/movements', $payload, nightposOperationalHeaders($token));
}

it('creates CASH_MOVEMENT print job on income movement', function () {
    cmcpRegisterDevice();

    $response = cmcpRegisterMovement('INCOME')->assertCreated();
    $movementId = (int) $response->json('data.movement.id');

    expect($response->json('data.print_job.type'))->toBe('CASH_MOVEMENT');

    $job = PrintJobModel::query()
        ->where('source_type', 'cash_movement')
        ->where('source_id', $movementId)
        ->first();

    expect($job)->not->toBeNull()
        ->and($job->type)->toBe('CASH_MOVEMENT');
});

it('creates CASH_MOVEMENT print job on expense movement', function () {
    cmcpRegisterDevice();

    $response = cmcpRegisterMovement('EXPENSE')->assertCreated();
    $movementId = (int) $response->json('data.movement.id');

    expect(PrintJobModel::query()
        ->where('source_type', 'cash_movement')
        ->where('source_id', $movementId)
        ->where('type', 'CASH_MOVEMENT')
        ->exists())->toBeTrue();
});

it('saves movement and returns print_warning when no active printer', function () {
    $response = cmcpRegisterMovement('INCOME')->assertCreated();

    expect($response->json('data.print_warning'))->toContain('no se pudo imprimir')
        ->and($response->json('data.print_job'))->toBeNull()
        ->and(CashMovementModel::query()->whereKey($response->json('data.movement.id'))->exists())->toBeTrue();
});

it('includes movement fields in ticket content', function () {
    cmcpRegisterDevice();

    $response = cmcpRegisterMovement('EXPENSE', overrides: [
        'amount' => 120,
        'payment_method' => 'QR',
        'notes' => 'Compra hielo turno',
    ])->assertCreated();

    $job = PrintJobModel::query()->find($response->json('data.print_job.id'));
    $content = app(PrintTicketContentBuilder::class)->buildCashMovement(
        $job->payload['movement'] ?? [],
        $job->payload['branch_name'] ?? null,
        $job->payload['cashier_name'] ?? null,
    );

    expect($content)
        ->toContain('MOVIMIENTO DE CAJA')
        ->toContain('Egreso')
        ->toContain('120.00')
        ->toContain('Compra hielo turno');
});

it('creates new print job on movement reprint', function () {
    cmcpRegisterDevice();

    $response = cmcpRegisterMovement('INCOME')->assertCreated();
    $movementId = (int) $response->json('data.movement.id');
    $firstJobId = (int) $response->json('data.print_job.id');

    test()->postJson("/api/v1/cash/movements/{$movementId}/print", [
        'reprint' => true,
    ], nightposOperationalHeaders(cmcpCashierToken()))
        ->assertOk();

    $jobs = PrintJobModel::query()
        ->where('source_type', 'cash_movement')
        ->where('source_id', $movementId)
        ->orderBy('id')
        ->pluck('id');

    expect($jobs)->toHaveCount(2)
        ->and($jobs->first())->toBe($firstJobId);
});

it('creates CASH_CLOSE print job on normal cash session close', function () {
    cmcpRegisterDevice();
    $token = cmcpCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 500, false);
    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 500,
    ], nightposOperationalHeaders($token))->assertOk();

    $sessionId = (int) $response->json('data.session.id');

    expect($response->json('data.print_job.type'))->toBe('CASH_CLOSE');

    expect(PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $sessionId)
        ->where('type', 'CASH_CLOSE')
        ->exists())->toBeTrue();
});

it('closes cash session and returns print_warning when no active printer', function () {
    $token = cmcpCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 300, false);
    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 300,
    ], nightposOperationalHeaders($token))->assertOk();

    expect($response->json('data.print_warning'))->toContain('no se pudo imprimir')
        ->and($response->json('data.session.status'))->toBe('CLOSED');
});

it('includes admin fields on forced close ticket', function () {
    cmcpRegisterDevice();
    $cashier = cmcpCashierToken();
    nightposOpenCashSession($cashier, 150);
    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');

    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        [
            'forced_close_reason' => 'cashier_left',
            'forced_close_notes' => 'Salida anticipada.',
        ],
        nightposOperationalHeaders($admin),
    )->assertOk()
        ->assertJsonPath('data.print_job.type', 'CASH_CLOSE');

    $job = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $sessionId)
        ->where('type', 'CASH_CLOSE')
        ->latest('id')
        ->first();

    $content = app(PrintTicketContentBuilder::class)->buildCashClose($job->payload ?? []);

    expect($content)
        ->toContain('CIERRE ADMINISTRATIVO')
        ->toContain('INFORMACION GENERAL')
        ->toContain('RESUMEN DE VENTAS')
        ->toContain('ARQUEO')
        ->toContain('Admin Demo')
        ->toContain('Powered by Ribersoft');
});

it('includes section layout on normal cash close ticket', function () {
    cmcpRegisterDevice();
    $token = cmcpCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 500, false);
    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 500,
    ], nightposOperationalHeaders($token))->assertOk();

    $job = PrintJobModel::query()->find($response->json('data.print_job.id'));
    $content = app(PrintTicketContentBuilder::class)->buildCashClose($job->payload ?? []);

    expect($content)
        ->toContain('CIERRE NORMAL')
        ->toContain('INFORMACION GENERAL')
        ->toContain('METODOS DE PAGO')
        ->toContain('RESUMEN DE VENTAS')
        ->toContain('Powered by Ribersoft');
});

it('creates SHIFT_CLOSE print job on demand after shift close', function () {
    cmcpRegisterDevice();
    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    nightposEnsureShiftOpen();
    nightposOpenCashSession($admin, 100, false);
    nightposPrepareCashSessionClose($admin);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 100,
    ], nightposOperationalHeaders($admin))->assertOk();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    test()->postJson("/api/v1/shifts/{$shiftId}/close", [
        'counted_cash' => 100,
    ], nightposOperationalHeaders($admin))->assertOk();

    test()->postJson("/api/v1/shifts/{$shiftId}/print-closure", [], nightposOperationalHeaders($admin))
        ->assertOk()
        ->assertJsonPath('data.print_job.type', 'SHIFT_CLOSE');
});

it('does not duplicate print jobs without reprint flag', function () {
    cmcpRegisterDevice();

    $response = cmcpRegisterMovement('INCOME')->assertCreated();
    $movementId = (int) $response->json('data.movement.id');
    $firstJobId = (int) $response->json('data.print_job.id');

    $reprint = test()->postJson("/api/v1/cash/movements/{$movementId}/print", [], nightposOperationalHeaders(cmcpCashierToken()))
        ->assertOk();

    expect((int) $reprint->json('data.print_job.id'))->toBe($firstJobId);

    expect(PrintJobModel::query()
        ->where('source_type', 'cash_movement')
        ->where('source_id', $movementId)
        ->count())->toBe(1);
});
