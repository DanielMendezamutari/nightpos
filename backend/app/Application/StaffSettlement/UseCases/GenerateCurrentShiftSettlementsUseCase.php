<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GenerateCurrentShiftSettlementsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw StaffSettlementDomainException::shiftRequired();
        }

        if (! $this->staffContext->hasPermission('settlements.generate')) {
            throw PermissionDeniedException::forPermission('settlements.generate');
        }

        $userId = $this->staffContext->userId();

        if ($userId === null) {
            throw StaffSettlementDomainException::shiftRequired();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        $result = $this->settlements->generateForShift($tenant->id, $branch->id, $shift->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'settlement.generated',
            [
                'entity'  => ['type' => 'shift', 'id' => $shift->id],
                'summary' => 'Liquidaciones generadas para el turno',
                'refresh' => ['settlements'],
            ]
        );

        return OperationResult::ok('Liquidaciones generadas para el turno actual.', $result);
    }
}
