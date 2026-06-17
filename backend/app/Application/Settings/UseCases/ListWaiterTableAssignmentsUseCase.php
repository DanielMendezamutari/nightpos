<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Repositories\WaiterTableAssignmentRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListWaiterTableAssignmentsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly WaiterTableAssignmentRepositoryInterface $assignments,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $waiterUserId = request()->filled('waiter_user_id')
            ? (int) request()->input('waiter_user_id')
            : null;
        $serviceAreaId = request()->filled('service_area_id')
            ? (int) request()->input('service_area_id')
            : null;

        $items = $this->assignments->listForBranch(
            $tenant->id,
            $branch->id,
            $waiterUserId,
            $serviceAreaId,
        );

        return OperationResult::ok('Asignaciones listadas.', ['waiter_table_assignments' => $items]);
    }
}
