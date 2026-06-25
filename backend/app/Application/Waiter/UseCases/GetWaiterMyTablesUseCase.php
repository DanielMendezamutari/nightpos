<?php

declare(strict_types=1);

namespace App\Application\Waiter\UseCases;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Settings\Repositories\WaiterTableAssignmentRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetWaiterMyTablesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly WaiterTableAssignmentRepositoryInterface $assignments,
        private readonly OrderRepositoryInterface $orders,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw OrderDomainException::branchRequired();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        $tables = $this->assignments->listTablesForWaiter(
            $tenant->id,
            $branch->id,
            $userId,
            $shift->id,
        );

        $payload = array_map(function (array $table) use ($tenant, $branch, $shift) {
            $activeOrder = $this->orders->findActiveByServiceTable(
                $tenant->id,
                $branch->id,
                (int) $table['id'],
                $shift->id,
            );

            $row = [
                'id' => (int) $table['id'],
                'label' => $table['label'],
                'area' => $table['area'],
                'status' => $activeOrder !== null ? 'OCCUPIED' : 'FREE',
                'order_id' => $activeOrder?->id,
            ];

            if ($activeOrder !== null) {
                $row['total'] = $activeOrder->total;
            }

            return $row;
        }, $tables);

        return OperationResult::ok('Mesas listadas.', ['tables' => $payload]);
    }
}
