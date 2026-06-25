<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementManualDiscountService;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CancelManualDiscountUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementManualDiscountService $discounts,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.fines.manage')) {
            throw PermissionDeniedException::forPermission('settlements.fines.manage');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $settlementId = (int) ($input->settlementId ?? 0);

        if ($tenant === null || $branch === null || $settlementId <= 0) {
            throw new StaffSettlementNotFoundException();
        }

        $model = StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->first();

        if ($model === null) {
            throw new StaffSettlementNotFoundException();
        }

        $cancelled = $this->discounts->cancel($model);

        if (! $cancelled) {
            throw StaffSettlementDomainException::manualDiscountNotFound();
        }

        $this->audit->record(
            'MANUAL_DISCOUNT_CANCELLED',
            'staff_settlement',
            $settlementId,
        );

        $detail = $this->settlements->findById($settlementId, $tenant->id, $branch->id, null);

        return OperationResult::ok('Descuento manual eliminado.', [
            'settlement' => SettlementMapper::settlement($detail['settlement'] ?? []),
        ]);
    }
}
