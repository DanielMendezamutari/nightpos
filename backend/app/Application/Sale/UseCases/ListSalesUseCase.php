<?php

declare(strict_types=1);

namespace App\Application\Sale\UseCases;

use App\Application\Sale\Support\SaleMapper;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListSalesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly SaleRepositoryInterface $sales,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $cashSessionId = null;
        $officialShiftId = null;

        if (is_object($input) && property_exists($input, 'currentShiftOnly') && $input->currentShiftOnly) {
            $officialShiftId = $this->shifts->findOpenForBranch($tenant->id, $branch->id)?->id;
        }

        if (is_object($input) && property_exists($input, 'currentSessionOnly') && $input->currentSessionOnly) {
            $userId = $this->staffContext->userId();

            if ($userId !== null) {
                $session = $this->cashSessions->findOpenForUser($tenant->id, $branch->id, $userId);
                $cashSessionId = $session?->id;
            }
        }

        $items = $this->sales->listForBranch($tenant->id, $branch->id, $cashSessionId, $officialShiftId);

        $data = array_map(static fn ($sale) => SaleMapper::saleSummary($sale), $items);

        return OperationResult::ok('Listado de ventas.', ['sales' => $data]);
    }
}
