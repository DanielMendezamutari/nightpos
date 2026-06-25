<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Reports\Services\CashCloseReportSectionsBuilder;
use App\Application\Cash\Support\CashMapper;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
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
        private readonly CashSessionFinancialSummaryBuilder $financials,
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

        $summary = $this->financials->build(
            sessionId: $session->id,
            openingAmount: $session->openingAmount,
            storedExpectedAmount: $session->expectedAmount,
            declaredClosingAmount: $session->declaredClosingAmount,
            differenceAmount: $session->differenceAmount,
            status: $session->status,
        );

        return OperationResult::ok('Sesión encontrada.', [
            'session' => CashMapper::session($session),
            'summary' => $summary,
            'operational' => $this->closeSections->forSession(
                $tenant->id,
                $branch->id,
                $session->id,
                $session->officialShiftId,
                (string) ($summary['total_sales'] ?? '0.00'),
            ),
        ]);
    }
}
