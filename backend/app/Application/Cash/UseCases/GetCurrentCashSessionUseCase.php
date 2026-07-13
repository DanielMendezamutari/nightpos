<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\Cash\Support\CashMapper;
use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Domain\Cash\Contracts\FinancialDashboardAssembler;
use App\Domain\Cash\ValueObjects\CashSessionId;
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
        private readonly FinancialDashboardAssembler $financialDashboardAssembler,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly ComboBraceletReportingService $comboReporting,
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

            $dashboard = $this->financialDashboardAssembler->assemble(
                tenantId: $tenant->id,
                branchId: $branch->id,
                cashSessionId: new CashSessionId($session->id),
                openingAmount: (string) $session->openingAmount,
                storedExpectedAmount: $session->expectedAmount !== null ? (string) $session->expectedAmount : null,
                declaredClosingAmount: null,
                differenceAmount: null,
                status: $session->status ?? 'OPEN',
                officialShiftId: $session->officialShiftId,
            );

            $sessionData['financial_dashboard'] = $dashboard->toArray();
            $sessionData['financial_summary'] = $dashboard->financial_summary;
            $sessionData['sales_by_method'] = $dashboard->financial_summary['sales_by_method'] ?? ['cash' => '0.00', 'qr' => '0.00', 'card' => '0.00'];
            $sessionData['combo_bracelets']   = $this->comboReporting->buildScopeSummary(
                $tenant->id,
                $branch->id,
                ['cash_session_id' => $session->id],
            );

            $payload['session'] = $sessionData;
        }

        return OperationResult::ok(
            $session ? 'Sesión de caja obtenida.' : 'Sin sesión de caja abierta.',
            $payload,
        );
    }
}
