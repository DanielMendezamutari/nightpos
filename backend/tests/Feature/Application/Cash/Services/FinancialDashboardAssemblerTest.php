<?php

declare(strict_types=1);

namespace Tests\Feature\Application\Cash\Services;

use App\Application\Cash\DTOs\CashSummaryDTO;
use App\Application\Cash\DTOs\MovementSummaryDTO;
use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Application\Cash\DTOs\ScopeSummaryDTO;
use App\Application\Cash\DTOs\SettlementSummaryDTO;
use App\Application\Cash\Services\EloquentFinancialDashboardAssembler;
use App\Application\Cash\Support\FinancialSummarySerializer;
use App\Application\Cash\Support\LegacyFinancialSummaryMapper;
use App\Domain\Cash\Contracts\SummaryBuilders\CashSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\MovementSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\ScopeSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\SalesSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\SettlementSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Shared\Domain\ValueObjects\Money;
use Tests\TestCase;

class FinancialDashboardAssemblerTest extends TestCase
{
    public function test_1_assembles_all_summary_blocks(): void
    {
        $assembler = $this->makeAssembler();

        $dashboard = $assembler->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertArrayHasKey('total_sales', $dashboard->sales_summary);
        $this->assertArrayHasKey('expected_cash', $dashboard->cash_summary);
        $this->assertArrayHasKey('total_movements', $dashboard->movement_summary);
        $this->assertArrayHasKey('totals', $dashboard->settlement_summary);
        $this->assertArrayHasKey('scope_type', $dashboard->scope_summary);
        $this->assertArrayHasKey('expected_cash', $dashboard->financial_summary);
    }

    public function test_2_financial_summary_uses_sales_totals(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame('150.00', $dashboard->financial_summary['total_sales']);
        $this->assertSame('100.00', $dashboard->financial_summary['total_cash']);
        $this->assertSame('30.00', $dashboard->financial_summary['total_qr']);
        $this->assertSame('20.00', $dashboard->financial_summary['total_card']);
    }

    public function test_3_closed_session_prefers_stored_expected_cash(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', '199.99', null, null, 'CLOSED', 7);

        $this->assertSame('199.99', $dashboard->financial_summary['expected_cash']);
    }

    public function test_4_open_session_uses_cash_summary_expected_cash(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', '199.99', null, null, 'OPEN', 7);

        $this->assertSame('130.00', $dashboard->financial_summary['expected_cash']);
    }

    public function test_5_expected_by_method_is_derived_from_movement_summary(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame('130.00', $dashboard->financial_summary['expected_by_method']['cash']);
        $this->assertSame('20.00', $dashboard->financial_summary['expected_by_method']['qr']);
        $this->assertSame('7.00', $dashboard->financial_summary['expected_by_method']['card']);
    }

    public function test_6_counted_cash_and_difference_are_passthrough(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, '128.00', '-2.00', 'OPEN', 7);

        $this->assertSame('128.00', $dashboard->financial_summary['counted_cash']);
        $this->assertSame('-2.00', $dashboard->financial_summary['cash_difference']);
    }

    public function test_7_legacy_total_manual_expense_matches_total_expense(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame('23.00', $dashboard->financial_summary['total_expense']);
        $this->assertSame('23.00', $dashboard->financial_summary['total_manual_expense']);
    }

    public function test_8_scope_summary_is_serialized_with_required_fields(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame(1, $dashboard->scope_summary['tenant_id']);
        $this->assertSame(1, $dashboard->scope_summary['branch_id']);
        $this->assertSame('OFFICIAL_SHIFT', $dashboard->scope_summary['scope_type']);
    }

    public function test_9_settlement_summary_is_serialized_with_totals_block(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertArrayHasKey('pending_total_count', $dashboard->settlement_summary['totals']);
        $this->assertArrayHasKey('paid_total_net', $dashboard->settlement_summary['totals']);
    }

    public function test_10_sales_by_method_in_legacy_matches_sales_summary(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame($dashboard->sales_summary['sales_by_method'], $dashboard->financial_summary['sales_by_method']);
    }

    public function test_11_income_and_expense_by_method_are_legacy_compatible(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame('40.00', $dashboard->financial_summary['income_by_method']['cash']);
        $this->assertSame('20.00', $dashboard->financial_summary['income_by_method']['qr']);
        $this->assertSame('10.00', $dashboard->financial_summary['income_by_method']['card']);
        $this->assertSame('10.00', $dashboard->financial_summary['expense_by_method']['cash']);
    }

    public function test_12_dashboard_to_array_contains_all_sections(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);
        $asArray = $dashboard->toArray();

        $this->assertArrayHasKey('sales_summary', $asArray);
        $this->assertArrayHasKey('cash_summary', $asArray);
        $this->assertArrayHasKey('movement_summary', $asArray);
        $this->assertArrayHasKey('settlement_summary', $asArray);
        $this->assertArrayHasKey('scope_summary', $asArray);
        $this->assertArrayHasKey('financial_summary', $asArray);
    }

    public function test_13_settlement_builder_receives_session_scope_parameters(): void
    {
        $capture = ['tenant' => 0, 'branch' => 0, 'session' => null, 'shift' => null];
        $assembler = $this->makeAssembler($capture);

        $assembler->assemble(22, 33, new CashSessionId(44), '100.00', null, null, null, 'OPEN', 55);

        $this->assertSame(22, $capture['tenant']);
        $this->assertSame(33, $capture['branch']);
        $this->assertSame(44, $capture['session']);
        $this->assertNull($capture['shift']);
    }

    public function test_14_scope_builder_receives_same_cash_session_id(): void
    {
        $capturedSessionId = 0;

        $salesBuilder = $this->salesBuilder();
        $cashBuilder = $this->cashBuilder();
        $movementBuilder = $this->movementBuilder();
        $settlementBuilder = $this->settlementBuilder();
        $scopeBuilder = new class($capturedSessionId) implements ScopeSummaryBuilder {
            public function __construct(private int &$capturedSessionId)
            {
            }

            public function build(CashSessionId $cashSessionId): ScopeSummaryDTO
            {
                $this->capturedSessionId = $cashSessionId->value;

                return new ScopeSummaryDTO(1, 1, $cashSessionId->value, 'OPEN', null, null, 7, 7, [7], false, [7], false, [], 'OFFICIAL_SHIFT', 'Turno #7', []);
            }
        };

        $assembler = new EloquentFinancialDashboardAssembler(
            $salesBuilder,
            $cashBuilder,
            $movementBuilder,
            $settlementBuilder,
            $scopeBuilder,
            new FinancialSummarySerializer(),
            new LegacyFinancialSummaryMapper(),
        );

        $assembler->assemble(1, 1, new CashSessionId(77), '100.00', null, null, null, 'OPEN', 7);

        $this->assertSame(77, $capturedSessionId);
    }

    public function test_15_money_values_are_normalized_to_two_decimals(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100', null, null, null, 'OPEN', 7);

        $this->assertSame('130.00', $dashboard->financial_summary['expected_cash']);
        $this->assertSame('20.00', $dashboard->financial_summary['expected_qr']);
        $this->assertSame('7.00', $dashboard->financial_summary['expected_card']);
    }

    public function test_16_mapper_handles_null_counted_and_difference(): void
    {
        $dashboard = $this->makeAssembler()->assemble(1, 1, new CashSessionId(10), '100.00', null, null, null, 'OPEN', 7);

        $this->assertNull($dashboard->financial_summary['counted_cash']);
        $this->assertNull($dashboard->financial_summary['cash_difference']);
    }

    public function test_17_mapper_defaults_sales_by_method_when_missing(): void
    {
        $mapper = new LegacyFinancialSummaryMapper();
        $legacy = $mapper->map(
            salesSummary: ['total_sales' => '0.00'],
            cashSummary: ['opening_cash' => '0.00', 'expected_cash' => '0.00'],
            movementSummary: ['income_by_payment_method' => [], 'expense_by_payment_method' => [], 'total_income' => '0.00', 'total_expense' => '0.00'],
            storedExpectedAmount: null,
            declaredClosingAmount: null,
            differenceAmount: null,
            status: 'OPEN',
        );

        $this->assertSame(['cash' => '0.00', 'qr' => '0.00', 'card' => '0.00'], $legacy['sales_by_method']);
    }

    private function makeAssembler(?array &$capture = null): EloquentFinancialDashboardAssembler
    {
        return new EloquentFinancialDashboardAssembler(
            $this->salesBuilder(),
            $this->cashBuilder(),
            $this->movementBuilder(),
            $this->settlementBuilder($capture),
            $this->scopeBuilder(),
            new FinancialSummarySerializer(),
            new LegacyFinancialSummaryMapper(),
        );
    }

    private function salesBuilder(): SalesSummaryBuilder
    {
        return new class implements SalesSummaryBuilder {
            public function build(CashSessionId $cashSessionId): SalesSummaryDTO
            {
                return new SalesSummaryDTO(
                    total_sales: new Money('150.00'),
                    sales_count: 3,
                    average_ticket: new Money('50.00'),
                    sales_cash: new Money('100.00'),
                    sales_qr: new Money('30.00'),
                    sales_card: new Money('20.00'),
                    mixed_sales_count: 1,
                    sales_by_method: ['cash' => '100.00', 'qr' => '30.00', 'card' => '20.00'],
                    products_sold_count: 5,
                    services_sold_count: 2,
                );
            }
        };
    }

    private function cashBuilder(): CashSummaryBuilder
    {
        return new class implements CashSummaryBuilder {
            public function build(CashSessionId $cashSessionId): CashSummaryDTO
            {
                return new CashSummaryDTO(
                    opening_cash: new Money('100.00'),
                    cash_income_sales: new Money('100.00'),
                    cash_income_manual: new Money('10.00'),
                    cash_expense_settlements: new Money('5.00'),
                    cash_expense_operational: new Money('3.00'),
                    cash_expense_purchases: new Money('2.00'),
                    cash_expense_other: new Money('0.00'),
                    cash_income_total: new Money('110.00'),
                    cash_expense_total: new Money('10.00'),
                    expected_cash: new Money('130.00'),
                    counted_cash: null,
                    cash_difference: null,
                    is_closed: false,
                    has_declared_count: false,
                );
            }
        };
    }

    private function movementBuilder(): MovementSummaryBuilder
    {
        return new class implements MovementSummaryBuilder {
            public function build(CashSessionId $cashSessionId): MovementSummaryDTO
            {
                return new MovementSummaryDTO(
                    total_movements: 5,
                    total_income: new Money('70.00'),
                    total_expense: new Money('23.00'),
                    income_by_family: ['SALE' => '40.00', 'MANUAL' => '30.00', 'SETTLEMENT' => '0.00', 'EXPENSE' => '0.00', 'ADJUSTMENT' => '0.00'],
                    expense_by_family: ['SALE' => '0.00', 'MANUAL' => '0.00', 'SETTLEMENT' => '10.00', 'EXPENSE' => '13.00', 'ADJUSTMENT' => '0.00'],
                    income_by_category: [],
                    expense_by_category: [],
                    income_by_payment_method: ['cash' => '40.00', 'qr' => '20.00', 'card' => '10.00'],
                    expense_by_payment_method: ['cash' => '10.00', 'qr' => '0.00', 'card' => '3.00'],
                    timeline_by_hour: [],
                    largest_income_category: null,
                    largest_expense_category: null,
                    movement_count_by_category: [],
                );
            }
        };
    }

    private function settlementBuilder(?array &$capture = null): SettlementSummaryBuilder
    {
        return new class($capture) implements SettlementSummaryBuilder {
            public function __construct(private ?array &$capture)
            {
            }

            public function build(int $tenantId, int $branchId, ?int $cashSessionId = null, ?int $officialShiftId = null): SettlementSummaryDTO
            {
                if ($this->capture !== null) {
                    $this->capture['tenant'] = $tenantId;
                    $this->capture['branch'] = $branchId;
                    $this->capture['session'] = $cashSessionId;
                    $this->capture['shift'] = $officialShiftId;
                }

                return new SettlementSummaryDTO(
                    waiters: ['pending_count' => 1, 'pending_gross_amount' => '10.00', 'pending_adjustments_amount' => '0.00', 'pending_net_amount' => '10.00', 'paid_count' => 1, 'paid_gross_amount' => '20.00', 'paid_adjustments_amount' => '0.00', 'paid_net_amount' => '20.00'],
                    girls: ['pending_count' => 0, 'pending_gross_amount' => '0.00', 'pending_adjustments_amount' => '0.00', 'pending_net_amount' => '0.00', 'paid_count' => 1, 'paid_gross_amount' => '30.00', 'paid_adjustments_amount' => '0.00', 'paid_net_amount' => '30.00'],
                    cleaning: ['pending_count' => 0, 'pending_gross_amount' => '0.00', 'pending_adjustments_amount' => '0.00', 'pending_net_amount' => '0.00', 'paid_count' => 0, 'paid_gross_amount' => '0.00', 'paid_adjustments_amount' => '0.00', 'paid_net_amount' => '0.00'],
                    totals: ['pending_total_count' => 1, 'pending_total_gross' => '10.00', 'pending_total_adjustments' => '0.00', 'pending_total_net' => '10.00', 'paid_total_count' => 2, 'paid_total_gross' => '50.00', 'paid_total_adjustments' => '0.00', 'paid_total_net' => '50.00'],
                    adjustments_breakdown: ['cleaning_deduction' => '0.00', 'manual_fines' => '0.00', 'manual_discounts' => '0.00', 'other_adjustments' => '0.00'],
                    manual_compensation: ['waiter_manual_pending_count' => 0, 'waiter_manual_pending_amount' => '0.00', 'waiter_manual_paid_count' => 0, 'waiter_manual_paid_amount' => '0.00'],
                );
            }
        };
    }

    private function scopeBuilder(): ScopeSummaryBuilder
    {
        return new class implements ScopeSummaryBuilder {
            public function build(CashSessionId $cashSessionId): ScopeSummaryDTO
            {
                return new ScopeSummaryDTO(
                    tenant_id: 1,
                    branch_id: 1,
                    cash_session_id: $cashSessionId->value,
                    cash_session_status: 'OPEN',
                    cash_session_opened_at: '2026-01-01T00:00:00+00:00',
                    cash_session_closed_at: null,
                    session_official_shift_id: 7,
                    current_official_shift_id: 7,
                    official_shift_ids_included: [7],
                    crosses_multiple_shifts: false,
                    open_shift_ids: [7],
                    has_open_shift_conflict: false,
                    open_cash_sessions_on_historical_shifts: [],
                    scope_type: 'OFFICIAL_SHIFT',
                    scope_label: 'Turno #7',
                    warnings: [],
                );
            }
        };
    }
}
