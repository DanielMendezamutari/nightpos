<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementManualDiscountService;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PreviewManualDiscountUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly SettlementManualDiscountService $discounts,
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
        $discountMode = strtoupper(trim((string) ($input->discountMode ?? '')));
        $discountValue = (float) ($input->discountValue ?? 0);

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

        return OperationResult::ok('Vista previa de descuento.', [
            'preview' => $this->discounts->preview($model, $discountMode, $discountValue),
        ]);
    }
}
