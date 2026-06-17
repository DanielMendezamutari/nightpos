<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Application\GirlIncome\Services\GirlStaffValidator;
use App\Domain\Settings\Repositories\WaiterTableAssignmentRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class SyncWaiterTableAssignmentsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly WaiterTableAssignmentRepositoryInterface $assignments,
        private readonly GirlStaffValidator $staffValidator,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! is_object($input)) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();
        $waiterUserId = (int) ($input->waiterUserId ?? 0);

        if ($tenant === null || $branch === null || $userId === null || $waiterUserId <= 0) {
            throw UserDomainException::branchNotInTenant();
        }

        $this->staffValidator->assertWaiter($tenant->id, $waiterUserId);

        $serviceTableIds = is_array($input->serviceTableIds ?? null)
            ? array_map('intval', $input->serviceTableIds)
            : [];

        $this->assignments->syncForWaiter(
            $tenant->id,
            $branch->id,
            $waiterUserId,
            $serviceTableIds,
            $userId,
        );

        $items = $this->assignments->listForBranch($tenant->id, $branch->id, $waiterUserId);

        return OperationResult::ok('Asignación guardada.', ['waiter_table_assignments' => $items]);
    }
}
