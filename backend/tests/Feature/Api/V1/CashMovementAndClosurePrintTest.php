<?php

declare(strict_types=1);

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
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

function cmcpSeedDirectSaleProduct(string $name, float $price = 25.0): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => $name,
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => $price,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
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

    expect($response->json('data.print_job.type'))->toBe('CASH_CLOSE')
        ->and($response->json('data.print_jobs'))->toHaveCount(2)
        ->and($response->json('data.print_warnings') ?? [])->toHaveCount(0);

    expect(PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $sessionId)
        ->where('type', 'CASH_CLOSE')
        ->count())->toBe(2);
});

it('splits cash close ticket content between summary and personnel tickets', function () {
    cmcpRegisterDevice();
    $token = cmcpCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 350, false);
    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 350,
    ], nightposOperationalHeaders($token))->assertOk();

    $jobs = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $response->json('data.session.id'))
        ->orderBy('id')
        ->get();

    expect($jobs)->toHaveCount(2);

    $builder = app(PrintTicketContentBuilder::class);
    $summary = $builder->buildCashCloseSummaryTicket($jobs[0]->payload ?? []);
    $personnel = $builder->buildCashPersonnelTicket($jobs[1]->payload ?? []);

    expect($summary)
        ->toContain('REPORTE DE VENTAS / CIERRE DE CAJA')
        ->toContain('VENTAS POR ORIGEN')
        ->not->toContain('PAGOS DEL PERSONAL');

    expect($personnel)
        ->toContain('PAGOS DEL PERSONAL')
        ->toContain('GARZONES')
        ->toContain('CHICAS')
        ->toContain('LIMPIEZA');
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
        ->toContain('REPORTE DE VENTAS / CIERRE DE CAJA')
        ->toContain('ENCABEZADO')
        ->toContain('MONTO INICIAL')
        ->toContain('COBROS / VENTAS')
        ->toContain('VENTAS POR ORIGEN')
        ->toContain('CAJA FISICA')
        ->toContain('VENTAS TOTALES / PRODUCTOS')
        ->toContain('PENDIENTES')
        ->toContain('OBSERVACIONES')
        ->toContain('FIRMA CAJERA')
        ->toContain('Impreso:')
        ->toContain('Admin Demo')
        ->toContain('Powered by Ribersoft')
        ->not->toContain('Ticket promedio')
        ->not->toContain('KPIs')
        ->not->toContain('ALERTA')
        ->not->toContain('RANKING');
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
    $lineCount = count(array_filter(explode("\n", trim($content)), static fn (string $line): bool => $line !== ''));

    expect($content)
        ->toContain('REPORTE DE VENTAS / CIERRE DE CAJA')
        ->toContain('MONTO INICIAL')
        ->toContain('COBROS / VENTAS')
        ->toContain('VENTAS POR ORIGEN')
        ->toContain('CAJA FISICA')
        ->toContain('VENTAS TOTALES / PRODUCTOS')
        ->toContain('PENDIENTES')
        ->toContain('Impreso:')
        ->toContain('Powered by Ribersoft');

    expect($lineCount)->toBeLessThanOrEqual(110);
});

it('cash close ticket keeps mixed-payment product once and cash-only expected cash', function () {
    $content = app(PrintTicketContentBuilder::class)->buildCashClose([
        'branch_name' => 'Sucursal Centro',
        'tenant_name' => 'Casa Demo NightPOS',
        'cashier_name' => 'Admin Demo',
        'session' => [
            'id' => 27,
            'opened_at' => '2026-07-13T15:00:00Z',
            'closed_at' => '2026-07-13T16:29:00Z',
            'opening_amount' => '100.00',
            'expected_amount' => '110.00',
            'declared_closing_amount' => '110.00',
            'difference_amount' => '0.00',
            'closing_notes' => 'Cierre sin novedad',
        ],
        'shift_label' => 'Turno Apertura',
        'current_shift_label' => 'Turno Operativo',
        'financial_dashboard' => [
            'sales_summary' => [
                'total_sales' => '25.00',
                'sales_count' => 1,
                'average_ticket' => '25.00',
                'sales_cash' => '10.00',
                'sales_qr' => '15.00',
                'sales_card' => '0.00',
                'mixed_sales_count' => 1,
            ],
            'cash_summary' => [
                'opening_cash' => '100.00',
                'cash_income_manual' => '0.00',
                'cash_expense_operational' => '0.00',
                'cash_expense_purchases' => '0.00',
                'cash_expense_other' => '0.00',
                'cash_expense_settlements' => '0.00',
                'expected_cash' => '110.00',
                'counted_cash' => '110.00',
                'cash_difference' => '0.00',
            ],
            'settlement_summary' => [
                'waiters' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'girls' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'cleaning' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'totals' => ['paid_total_net' => '0.00', 'pending_total_net' => '0.00'],
            ],
            'scope_summary' => [
                'scope_label' => 'Turno Operativo',
                'crosses_multiple_shifts' => false,
            ],
        ],
        'products_sold' => [
            [
                'product_name' => 'Producto Mixto Cierre',
                'quantity_sold' => 1,
                'total_amount' => '25.00',
            ],
        ],
    ]);

    expect($content)
        ->toContain('Venta total')
        ->toContain('10.00')
        ->toContain('Efectivo')
        ->toContain('15.00')
        ->toContain('Mixto')
        ->toContain('Cantidad de ventas')
        ->toContain('PRODUCTO MIXTO CIERRE')
        ->toContain('TOTAL ITEMS VENDIDOS')
        ->toContain('OTROS PRODUCTOS')
        ->toContain('Total pendiente: 0');

    expect(substr_count($content, 'PRODUCTO MIXTO CIERRE'))->toBe(1);
    expect($content)->not->toContain('Ticket promedio');
});

it('cash close ticket sorts products by quantity and reports total units sold', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $products = [];
    for ($index = 1; $index <= 11; $index++) {
        $products[] = [
            'product_name' => sprintf('Prod-%02d', $index),
            'quantity_sold' => $index,
            'total_amount' => number_format($index * 10, 2, '.', ''),
        ];
    }
    $products[] = [
        'product_name' => 'Producto Cancelado',
        'quantity_sold' => 0,
        'total_amount' => '0.00',
    ];

    $content = $builder->buildCashClose([
        'branch_name' => 'Sucursal Centro',
        'tenant_name' => 'Casa Demo NightPOS',
        'cashier_name' => 'Admin Demo',
        'printed_at' => '2026-07-13T16:29:00Z',
        'session' => [
            'id' => 1,
            'opened_at' => '2026-07-13T15:00:00Z',
            'closed_at' => '2026-07-13T16:29:00Z',
            'opening_amount' => '100.00',
            'expected_amount' => '110.00',
            'declared_closing_amount' => '110.00',
            'difference_amount' => '0.00',
            'closing_notes' => 'Cierre sin novedad',
        ],
        'shift_label' => 'Turno Apertura',
        'current_shift_label' => 'Turno Operativo',
        'financial_dashboard' => [
            'sales_summary' => [
                'total_sales' => '110.00',
                'sales_count' => 11,
                'average_ticket' => '10.00',
                'sales_cash' => '110.00',
                'sales_qr' => '0.00',
                'sales_card' => '0.00',
                'mixed_sales_count' => 0,
            ],
            'cash_summary' => [
                'opening_cash' => '100.00',
                'cash_income_manual' => '0.00',
                'cash_expense_operational' => '0.00',
                'cash_expense_purchases' => '0.00',
                'cash_expense_other' => '0.00',
                'cash_expense_settlements' => '0.00',
                'expected_cash' => '110.00',
                'counted_cash' => '110.00',
                'cash_difference' => '0.00',
            ],
            'settlement_summary' => [
                'waiters' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'girls' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'cleaning' => ['paid_count' => 0, 'paid_net_amount' => '0.00'],
                'totals' => ['paid_total_net' => '0.00', 'pending_total_net' => '0.00'],
            ],
            'scope_summary' => [
                'scope_label' => 'Turno Operativo',
                'crosses_multiple_shifts' => true,
            ],
        ],
        'products_sold' => $products,
    ]);

    $pos11 = strpos($content, 'PROD-11');
    $pos10 = strpos($content, 'PROD-10');
    $pos01 = strpos($content, 'PROD-01');

    expect($content)
        ->toContain('VENTAS TOTALES / PRODUCTOS')
        ->toContain('11 - PROD-11')
        ->toContain(' 1 - PROD-01')
        ->toContain('TOTAL ITEMS VENDIDOS')
        ->toContain('66');

    expect($pos11)->not->toBeFalse();
    expect($pos10)->not->toBeFalse();
    expect($pos01)->not->toBeFalse();
    expect($pos11)->toBeLessThan($pos10);
    expect($pos10)->toBeLessThan($pos01);
    expect($content)->not->toContain('PRODUCTO CANCELADO');
});

it('highlights pending balances when they are not zero', function () {
    $content = app(PrintTicketContentBuilder::class)->buildCashClose([
        'branch_name' => 'Sucursal Centro',
        'tenant_name' => 'Casa Demo NightPOS',
        'cashier_name' => 'Admin Demo',
        'session' => [
            'id' => 27,
            'opened_at' => '2026-07-13T15:00:00Z',
            'closed_at' => '2026-07-13T16:29:00Z',
            'opening_amount' => '100.00',
            'expected_amount' => '110.00',
            'declared_closing_amount' => '110.00',
            'difference_amount' => '-10.00',
            'closing_notes' => 'Cierre sin novedad',
        ],
        'financial_dashboard' => [
            'sales_summary' => [
                'total_sales' => '25.00',
                'sales_count' => 1,
                'average_ticket' => '25.00',
                'sales_cash' => '10.00',
                'sales_qr' => '15.00',
                'sales_card' => '0.00',
                'mixed_sales_count' => 1,
            ],
            'cash_summary' => [
                'opening_cash' => '100.00',
                'cash_income_manual' => '0.00',
                'cash_expense_operational' => '0.00',
                'cash_expense_purchases' => '0.00',
                'cash_expense_other' => '0.00',
                'cash_expense_settlements' => '0.00',
                'expected_cash' => '110.00',
                'counted_cash' => '100.00',
                'cash_difference' => '-10.00',
            ],
            'settlement_summary' => [
                'waiters' => ['paid_count' => 0, 'paid_net_amount' => '0.00', 'pending_net_amount' => '80.00'],
                'girls' => ['paid_count' => 0, 'paid_net_amount' => '0.00', 'pending_net_amount' => '350.00'],
                'cleaning' => ['paid_count' => 0, 'paid_net_amount' => '0.00', 'pending_net_amount' => '20.00'],
                'totals' => ['paid_total_net' => '0.00', 'pending_total_net' => '450.00'],
            ],
            'scope_summary' => [
                'scope_label' => 'Turno Operativo',
                'crosses_multiple_shifts' => false,
            ],
        ],
        'products_sold' => [
            ['product_name' => 'Corona', 'quantity_sold' => 18, 'total_amount' => '540.00'],
        ],
    ]);

    expect($content)
        ->toContain('PENDIENTES')
        ->toContain('********************************')
        ->toContain('Chicas')
        ->toContain('Garzones')
        ->toContain('Limpieza')
        ->toContain('TOTAL PENDIENTE');
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
