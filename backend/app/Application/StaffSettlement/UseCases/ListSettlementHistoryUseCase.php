<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\DTOs\ListSettlementHistoryInput;
use App\Application\StaffSettlement\Services\SettlementAccessPolicy;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListSettlementHistoryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementAccessPolicy $accessPolicy,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.history')) {
            throw PermissionDeniedException::forPermission('settlements.history');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::ok('Historial de liquidaciones.', ['settlements' => []]);
        }

        $dto = $input instanceof ListSettlementHistoryInput
            ? $input
            : new ListSettlementHistoryInput(limit: (int) ($input->limit ?? 50));

        $limit = min(100, max(1, $dto->limit));

        $filters = array_filter([
            'official_shift_id' => $dto->officialShiftId,
            'staff_user_id' => $dto->staffUserId,
            'settlement_type' => $dto->settlementType,
            'status' => $dto->status,
            'date_from' => $dto->dateFrom,
            'date_to' => $dto->dateTo,
        ], fn ($v) => $v !== null && $v !== '');

        $rows = $this->settlements->listHistory(
            $tenant->id,
            $branch->id,
            $this->accessPolicy->scopedStaffUserId(),
            $filters,
            $limit,
        );

        return OperationResult::ok('Historial de liquidaciones.', [
            'settlements' => array_map(SettlementMapper::settlement(...), $rows),
        ]);
    }
}
