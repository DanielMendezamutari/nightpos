<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Reports\Services\CashCloseReportSectionsBuilder;
use App\Application\Cash\Support\CashMapper;
use App\Domain\Cash\Contracts\FinancialDashboardAssembler;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashSessionUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $sessions,
        private readonly FinancialDashboardAssembler $financialDashboardAssembler,
        private readonly CashCloseReportSectionsBuilder $closeSections,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        $sessionId = (int) ($input->sessionId ?? 0);
        $session = $this->sessions->findById($sessionId, $tenant->id);

        if ($session === null || $session->branchId !== $branch->id) {
            throw new CashSessionNotFoundException();
        }

        if ($session->openedByUserId !== $userId && $session->closedByUserId !== $userId && ! $this->staffContext->hasPermission('admin.cash_sessions.view')) {
            throw new CashSessionNotFoundException();
        }

        $dashboard = $this->financialDashboardAssembler->assemble(
            tenantId: $tenant->id,
            branchId: $branch->id,
            cashSessionId: new CashSessionId($session->id),
            openingAmount: $session->openingAmount,
            storedExpectedAmount: $session->expectedAmount,
            declaredClosingAmount: $session->declaredClosingAmount,
            differenceAmount: $session->differenceAmount,
            status: $session->status,
            officialShiftId: $session->officialShiftId,
        );

        return OperationResult::ok('Sesión encontrada.', [
            'session' => CashMapper::session($session),
            'summary' => $dashboard->financial_summary,
            'financial_dashboard' => $dashboard->toArray(),
            'operational' => $this->closeSections->forSession(
                $tenant->id,
                $branch->id,
                $session->id,
                $session->officialShiftId,
                (string) ($dashboard->financial_summary['total_sales'] ?? '0.00'),
            ),
        ]);
    }
}
