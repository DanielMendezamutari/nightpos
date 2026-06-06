<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\Cash\Support\CashMapper;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCurrentCashSessionUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly CashSessionFinancialSummaryBuilder $financialSummaryBuilder,
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::ok('Sin sesión de caja abierta.', ['session' => null]);
        }

        $session   = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $userId);
        $openShift = $this->shifts->findOpenForBranch($tenant->id, $branch->id);

        $payload = [
            'session' => null,
            'shift'   => $openShift ? ShiftMapper::shift($openShift) : null,
        ];

        if ($session !== null) {
            $sessionData = CashMapper::session($session);

            $financial = $this->financialSummaryBuilder->build(
                sessionId: $session->id,
                openingAmount: (string) $session->openingAmount,
                storedExpectedAmount: $session->expectedAmount !== null ? (string) $session->expectedAmount : null,
                declaredClosingAmount: null,
                differenceAmount: null,
                status: $session->status ?? 'OPEN',
            );

            $sessionData['financial_summary'] = $financial;
            $sessionData['sales_by_method']   = $this->financialSummaryBuilder->salesByMethod($session->id);

            $payload['session'] = $sessionData;
        }

        return OperationResult::ok(
            $session ? 'Sesión de caja obtenida.' : 'Sin sesión de caja abierta.',
            $payload,
        );
    }
}
