<?php

declare(strict_types=1);

namespace Tests\Feature\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\ScopeSummaryDTO;
use App\Application\Cash\Services\SummaryBuilders\EloquentScopeSummaryBuilder;
use App\Domain\Cash\Contracts\ScopeSummaryRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionId;
use Tests\TestCase;

class ScopeSummaryBuilderTest extends TestCase
{
    public function test_it_builds_scope_for_single_shift_cash_session(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [10],
            'session_official_shift_id' => 10,
            'current_official_shift_id' => 10,
        ]);

        $this->assertSame('OFFICIAL_SHIFT', $summary->scope_type);
        $this->assertFalse($summary->crosses_multiple_shifts);
        $this->assertSame([10], $summary->official_shift_ids_included);
    }

    public function test_it_detects_cash_session_crossing_multiple_shifts(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [10, 11],
            'session_official_shift_id' => 10,
            'current_official_shift_id' => 11,
        ]);

        $this->assertSame('HYBRID', $summary->scope_type);
        $this->assertTrue($summary->crosses_multiple_shifts);
        $this->assertContains('La caja incluye actividad de multiples official_shift_id.', $summary->warnings);
    }

    public function test_it_supports_cash_session_without_sales_activity(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [],
            'session_official_shift_id' => 10,
        ]);

        $this->assertSame('OFFICIAL_SHIFT', $summary->scope_type);
        $this->assertSame([], $summary->official_shift_ids_included);
        $this->assertContains('No se detectaron official_shift_id en sales/cash_movements/staff_settlements para esta caja.', $summary->warnings);
    }

    public function test_it_formats_scope_label_for_open_cash_session(): void
    {
        $summary = $this->buildWithContext([
            'cash_session_status' => 'OPEN',
            'official_shift_ids_included' => [10],
            'session_official_shift_id' => 10,
        ]);

        $this->assertStringContainsString('(abierta)', $summary->scope_label);
    }

    public function test_it_formats_scope_label_for_closed_cash_session(): void
    {
        $summary = $this->buildWithContext([
            'cash_session_status' => 'CLOSED',
            'cash_session_closed_at' => '2026-07-12T10:00:00+00:00',
            'official_shift_ids_included' => [10],
            'session_official_shift_id' => 10,
        ]);

        $this->assertStringContainsString('(cerrada)', $summary->scope_label);
        $this->assertSame('2026-07-12T10:00:00+00:00', $summary->cash_session_closed_at);
    }

    public function test_it_detects_multiple_open_shift_conflict(): void
    {
        $summary = $this->buildWithContext([
            'open_shift_ids' => [20, 21],
        ]);

        $this->assertTrue($summary->has_open_shift_conflict);
        $this->assertSame([20, 21], $summary->open_shift_ids);
        $this->assertContains('Existen multiples turnos OPEN en la sucursal.', $summary->warnings);
    }

    public function test_it_reports_open_cash_sessions_linked_to_historical_shifts(): void
    {
        $summary = $this->buildWithContext([
            'open_cash_sessions_on_historical_shifts' => [
                [
                    'cash_session_id' => 100,
                    'official_shift_id' => 70,
                    'cash_session_status' => 'OPEN',
                    'official_shift_status' => 'CLOSED',
                    'cash_session_opened_at' => '2026-07-12T03:00:00+00:00',
                ],
            ],
        ]);

        $this->assertCount(1, $summary->open_cash_sessions_on_historical_shifts);
        $this->assertContains('Se detectaron cajas OPEN vinculadas a turnos historicos/cerrados.', $summary->warnings);
    }

    public function test_it_preserves_tenant_isolation_in_scope_summary(): void
    {
        $summary = $this->buildWithContext([
            'tenant_id' => 77,
            'branch_id' => 11,
        ]);

        $this->assertSame(77, $summary->tenant_id);
    }

    public function test_it_preserves_branch_isolation_in_scope_summary(): void
    {
        $summary = $this->buildWithContext([
            'tenant_id' => 5,
            'branch_id' => 99,
        ]);

        $this->assertSame(99, $summary->branch_id);
    }

    public function test_it_does_not_duplicate_shift_ids(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [3, 2, 3, 2],
        ]);

        $this->assertSame([2, 3], $summary->official_shift_ids_included);
    }

    public function test_it_keeps_stable_order_for_official_shift_ids_included(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [7, 5, 6],
        ]);

        $this->assertSame([5, 6, 7], $summary->official_shift_ids_included);
    }

    public function test_it_returns_expected_warning_set_for_combined_conflicts(): void
    {
        $summary = $this->buildWithContext([
            'official_shift_ids_included' => [10, 11],
            'open_shift_ids' => [10, 11],
            'open_cash_sessions_on_historical_shifts' => [
                [
                    'cash_session_id' => 200,
                    'official_shift_id' => 80,
                    'cash_session_status' => 'OPEN',
                    'official_shift_status' => 'CLOSED',
                    'cash_session_opened_at' => '2026-07-12T02:00:00+00:00',
                ],
            ],
        ]);

        $this->assertSame([
            'La caja incluye actividad de multiples official_shift_id.',
            'Existen multiples turnos OPEN en la sucursal.',
            'Se detectaron cajas OPEN vinculadas a turnos historicos/cerrados.',
        ], $summary->warnings);
    }

    public function test_it_builds_readable_scope_label_for_frontend(): void
    {
        $summary = $this->buildWithContext([
            'cash_session_id' => 99,
            'cash_session_status' => 'OPEN',
            'official_shift_ids_included' => [44],
            'session_official_shift_id' => 44,
        ]);

        $this->assertSame('Turno #44 · Caja #99 (abierta)', $summary->scope_label);
    }

    public function test_it_does_not_depend_on_description_to_build_scope(): void
    {
        $summary = $this->buildWithContext([
            'description' => 'Cobro comanda 123',
            'official_shift_ids_included' => [15],
            'session_official_shift_id' => 15,
        ]);

        $this->assertSame('OFFICIAL_SHIFT', $summary->scope_type);
        $this->assertSame([15], $summary->official_shift_ids_included);
    }

    public function test_it_does_not_modify_repository_data(): void
    {
        $source = $this->baseContext();
        $source['official_shift_ids_included'] = [11, 10, 11];
        $source['open_shift_ids'] = [22, 21, 22];

        $repository = new class($source) implements ScopeSummaryRepositoryInterface {
            public function __construct(public array $context)
            {
            }

            public function getScopeContext(CashSessionId $cashSessionId): array
            {
                return $this->context;
            }
        };

        $snapshot = $repository->context;

        $builder = new EloquentScopeSummaryBuilder($repository);
        $summary = $builder->build(new CashSessionId(1));

        $this->assertInstanceOf(ScopeSummaryDTO::class, $summary);
        $this->assertSame($snapshot, $repository->context);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function buildWithContext(array $overrides): ScopeSummaryDTO
    {
        $context = array_merge($this->baseContext(), $overrides);

        $repository = new class($context) implements ScopeSummaryRepositoryInterface {
            /** @param  array<string, mixed>  $context */
            public function __construct(private array $context)
            {
            }

            public function getScopeContext(CashSessionId $cashSessionId): array
            {
                return $this->context;
            }
        };

        $builder = new EloquentScopeSummaryBuilder($repository);

        return $builder->build(new CashSessionId(1));
    }

    /**
     * @return array<string, mixed>
     */
    private function baseContext(): array
    {
        return [
            'tenant_id' => 1,
            'branch_id' => 1,
            'cash_session_id' => 1,
            'cash_session_status' => 'OPEN',
            'cash_session_opened_at' => '2026-07-12T00:00:00+00:00',
            'cash_session_closed_at' => null,
            'session_official_shift_id' => 10,
            'current_official_shift_id' => 10,
            'official_shift_ids_included' => [10],
            'open_shift_ids' => [10],
            'open_cash_sessions_on_historical_shifts' => [],
        ];
    }
}
