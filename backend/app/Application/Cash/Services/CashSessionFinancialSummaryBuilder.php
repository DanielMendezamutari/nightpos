<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Domain\Cash\Contracts\FinancialDashboardAssembler;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;

final class CashSessionFinancialSummaryBuilder
{
    public function __construct(
        private readonly FinancialDashboardAssembler $dashboardAssembler,
        private readonly SaleRepositoryInterface $sales,
    ) {
    }

    /**
     * @return array<string, string|null|array<string, string>>
     */
    public function build(
        int $sessionId,
        string $openingAmount,
        ?string $storedExpectedAmount,
        ?string $declaredClosingAmount,
        ?string $differenceAmount,
        string $status,
    ): array {
        $session = CashSessionModel::query()
            ->select(['id', 'tenant_id', 'branch_id', 'official_shift_id'])
            ->find($sessionId);

        if ($session === null) {
            throw new \InvalidArgumentException('Cash session not found.');
        }

        $dashboard = $this->dashboardAssembler->assemble(
            tenantId: (int) $session->tenant_id,
            branchId: (int) $session->branch_id,
            cashSessionId: new CashSessionId($sessionId),
            openingAmount: $openingAmount,
            storedExpectedAmount: $storedExpectedAmount,
            declaredClosingAmount: $declaredClosingAmount,
            differenceAmount: $differenceAmount,
            status: $status,
            officialShiftId: $session->official_shift_id !== null ? (int) $session->official_shift_id : null,
        );

        return $dashboard->financial_summary;
    }

    /**
     * @return array<string, string>
     */
    public function salesByMethod(int $sessionId): array
    {
        return $this->sales->sumPaymentsByMethodForSession($sessionId);
    }
}
