<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface WaiterTableAssignmentRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(
        int $tenantId,
        int $branchId,
        ?int $waiterUserId = null,
        ?int $serviceAreaId = null,
    ): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listTablesForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?int $officialShiftId,
    ): array;

    public function isTableAssignedToWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        int $serviceTableId,
        ?int $officialShiftId,
    ): bool;

    /**
     * @param  list<int>  $serviceTableIds
     */
    public function syncForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        array $serviceTableIds,
        int $assignedByUserId,
    ): void;
}
