<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Application\Cash\DTOs\FinancialDashboardDTO;
use App\Application\Cash\Support\FinancialSummarySerializer;
use App\Application\Cash\Support\LegacyFinancialSummaryMapper;
use App\Domain\Cash\Contracts\FinancialDashboardAssembler;
use App\Domain\Cash\Contracts\SummaryBuilders\CashSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\MovementSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\ScopeSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\SalesSummaryBuilder;
use App\Domain\Cash\Contracts\SummaryBuilders\SettlementSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;

final readonly class EloquentFinancialDashboardAssembler implements FinancialDashboardAssembler
{
    public function __construct(
        private SalesSummaryBuilder $salesSummaryBuilder,
        private CashSummaryBuilder $cashSummaryBuilder,
        private MovementSummaryBuilder $movementSummaryBuilder,
        private SettlementSummaryBuilder $settlementSummaryBuilder,
        private ScopeSummaryBuilder $scopeSummaryBuilder,
        private FinancialSummarySerializer $serializer,
        private LegacyFinancialSummaryMapper $legacyMapper,
    ) {
    }

    public function assemble(
        int $tenantId,
        int $branchId,
        CashSessionId $cashSessionId,
        string $openingAmount,
        ?string $storedExpectedAmount,
        ?string $declaredClosingAmount,
        ?string $differenceAmount,
        string $status,
        ?int $officialShiftId = null,
    ): FinancialDashboardDTO {
        $salesSummary = $this->serializer->salesSummary(
            $this->salesSummaryBuilder->build($cashSessionId)
        );

        $cashSummary = $this->serializer->cashSummary(
            $this->cashSummaryBuilder->build($cashSessionId)
        );

        $movementSummary = $this->serializer->movementSummary(
            $this->movementSummaryBuilder->build($cashSessionId)
        );

        $settlementSummary = $this->serializer->settlementSummary(
            $this->settlementSummaryBuilder->build(
                tenantId: $tenantId,
                branchId: $branchId,
                cashSessionId: $cashSessionId->value,
                officialShiftId: null,
            )
        );

        $scopeSummary = $this->serializer->scopeSummary(
            $this->scopeSummaryBuilder->build($cashSessionId)
        );

        $financialSummary = $this->legacyMapper->map(
            salesSummary: $salesSummary,
            cashSummary: $cashSummary,
            movementSummary: $movementSummary,
            storedExpectedAmount: $storedExpectedAmount,
            declaredClosingAmount: $declaredClosingAmount,
            differenceAmount: $differenceAmount,
            status: $status,
        );

        return new FinancialDashboardDTO(
            sales_summary: $salesSummary,
            cash_summary: $cashSummary,
            movement_summary: $movementSummary,
            settlement_summary: $settlementSummary,
            scope_summary: $scopeSummary,
            financial_summary: $financialSummary,
        );
    }
}
